<?php
/**
 * Created by PhpStorm.
 * User: zdb
 * Date: 2018/7/13
 * Time: 9:52
 */

require_once __DIR__ . '/ini.php';

$actionName = isset($_GET['act']) ? trim($_GET['act']) : (isset($_POST['act']) ? trim($_POST['act']) : '');
if(empty($actionName)){
    FacebookMarketing::notFound();
}else{
    if( !isset($_SESSION, $_SESSION['admin'], $_SESSION['admin']['uid']) || 0 == intval($_SESSION['admin']['uid'])
        || 0 == intval($_SESSION['admin']['id_department'])
    ){
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');
        exit(\json_encode(['code'=>400, 'msg'=>'用户ID为0 或 部门ID为0, 请重新登陆']));
    }

    echo FacebookMarketing::$actionName();
    exit;
}


class FacebookMarketing
{
    public static function __callStatic($name, $arguments)
    {
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');
        return \json_encode(['code'=>400, 'msg'=>'method not exists']);

        /*header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        header('Refresh: 10; url=/');
        echo '<h1>404 Not Found</h1>';
        exit;*/
    }

    public static function createAdvertising()
    {
        /**
         * @var \Medoo\Medoo $db
         */
        $db = \lib\register::getInstance('db');
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');

        if(\strtoupper($_SERVER['REQUEST_METHOD'] != 'POST')){
            return \json_encode([
                'code' => 400,
                'msg'  => 'request method not allowed'
            ]);
        }

        //企业账号
        if(\mb_strlen( \trim((string) $_POST['account_id']) ) == 0){
            return \json_encode([
                'code' => 400,
                'msg'  => '建站: 请选择企业账号'
            ]);
        }
        //预算
        if(\bccomp($_POST['customer_price'], 0, 2) !== 1) {
            return \json_encode([
                'code' => 400,
                'msg' => '建站: 预算应大于0'
            ]);
        }

        $createType = isset($_POST['$createType']) ? (int) $_POST['$createType'] : 1;
        $createType = \min(2, \max(1, $createType));
        $curlPostParams = $_POST;

        $insertData = [
            'oa_uid'     => $_SESSION['admin']['uid'],
            'oa_dept_id' => (int) $_SESSION['admin']['id_department'],
            '#create_at' => 'NOW()',
            //广告系列
            'campaign_name' => \trim((string) $_POST['campaign_name']),
            //广告分组
            'adset_name'     => \trim((string) $_POST['adset_name']),
            'customer_price' =>  (string) $_POST['customer_price'],
            //受众
            'targeting_age_min' => \max(13, intval( $_POST['targeting']['age_min'] ?: 13) ),
            'targeting_age_max' => \min(65, intval( $_POST['targeting']['age_max'] ?: 65 )),
            'targeting_genders' => \max(0, \min(2, intval($_POST['targeting']['genders'] ?: 0) )),
            'targeting_interests' => \implode(',', \array_filter((array) $_POST['targeting']['interests'])),
            'account_id'  => \trim((string) $_POST['account_id']),
            //api传参模式
            //'create_type' => $createType //暂时不需要保存这个字段
        ];

        //公共参数
        if( !isset($_POST['targeting']) || ! is_array($_POST['targeting']) ){
            return \json_encode([
                'code' => 400,
                'msg'  => '建站: 请填写受众目标'
            ]);
        }

        if($createType == 1){
            $type1data = [
                'post_id'     => \trim((string) $_POST['post_id']),
                'page_id'     => \trim((string) $_POST['page_id'])
            ];
            //$creative_id = $page_id + $post_id
            $insertData = \array_merge($insertData, $type1data);
        }else{
            if( !isset($_POST['creative']) || ! is_array($_POST['creative']) ){
                return \json_encode([
                    'code' => 400,
                    'msg'  => '建站: 请完善广告创意内容'
                ]);
            }
            if( !isset($_POST['ad']) || ! is_array($_POST['ad']) ){
                return \json_encode([
                    'code' => 400,
                    'msg'  => '建站: 请完善广告内容'
                ]);
            }
            if(! isset( $_FILES['file'], $_FILES['file']['tmp_name'] )){
                return \json_encode([
                    'code' => 400,
                    'msg'  => '建站: 请上传图片或视频'
                ]);
            }
            //创意文件
            try{
                $fileInfo = self::handleUploadImageOrVideo($_FILES['file']);
            }catch (\Exception $e){
                return \json_encode([
                    'code' => 400,
                    'msg'  => '建站: '.$e->getMessage()
                ]);
            }
            $type2data = [
                //创意
                'creative_name'     => \trim((string) $_POST['creative']['name']),
                'creative_subtitle' => \trim((string) $_POST['creative']['subtitle']),
                'creative_link_url' => \trim((string) $_POST['creative']['link_url']),
                'file' => $fileInfo['relative_path'],
                //广告
                'ad_name'  => \trim((string) $_POST['ad']['name']),
                'ad_describe' => \trim((string) $_POST['ad']['describe'])
            ];
            $insertData = \array_merge($insertData, $type2data);

            //PHP5.5 使用@将报出致命错误
            $curlPostParams['file'] = new \CURLFile($fileInfo['absolute_path'], $fileInfo['mime_type'], $fileInfo['client_name']);
        }

        try{
            $url = self::getFmpDomain() . '/api/advertising/create';
            $fmpApiResponse = self::curlPost($url, $curlPostParams);
            $res = \json_decode($fmpApiResponse, true);
            if(\json_last_error() !== JSON_ERROR_NONE){
                return \json_encode([
                    'code' => 400,
                    'msg'  => 'FMP接口异常: 无法返回正确的JSON'
                ]);
            }
            if(empty($res) || !isset($res['code'])){
                return \json_encode([
                    'code' => 400,
                    'msg'  => 'FMP接口异常: 无法返回约定的JSON,内容是'.$fmpApiResponse
                ]);
            }
            if($res['code'] == 200){
                $db->insert('fmp_ad', $insertData);
                $errorInfo = $db->error();
                if($errorInfo[0] !== '00000'){
                    return \json_encode([
                        'code' => 400,
                        'msg'  => '建站: API调用成功，插入数据库失败'.$errorInfo[2]
                    ]);
                }
                //TODO 图片上传七牛
            }else{
                if($createType == 2 && isset($fileInfo)){
                    unlink($fileInfo['absolute_path']);
                }
            }
            return $fmpApiResponse;
        }catch (\Exception $e){
            return \json_encode([
                'code' => 400,
                'msg'  => 'FMP接口异常:'.$e->getMessage()
            ]);
        }
    }

    /**
     * @param $file
     * @return array
     * @throws \lib\UploadException
     */
    public static function handleUploadImageOrVideo($file)
    {
        if($file['error'] != \UPLOAD_ERR_OK){
            throw new \lib\UploadException($file['error']);
        }
        if($file['size'] > 10*1024*1024 || $file['size'] < 512 ){
            throw new \Exception('文件大小 不得大于 10MB 或小于 512 bytes');
        }
        if( \stripos($file['type'], 'image') === false && \stripos($file['type'], 'video') === false )
        {
            throw new \Exception('文件类型不是图片或视频'.$file['type']);
        }

        //保存路径
        $savePath = __DIR__ . '/../upload/ad/'. date('y-m-d') .'/';
        clearstatcache();
        if(!file_exists($savePath) || !is_dir($savePath)){
            \mkdir($savePath, 0755, true);
        }
        $savePath = realpath($savePath) . '/';
        //文件名
        $ext = \explode('/', $file['type']);
        $ext = \str_replace('+', '', \end($ext));
        $fileName = \md5($file['tmp_name']) . '.' . $ext;
        //
        $absolutePath = $savePath . $fileName;
        $relativePath = str_replace( \dirname(__dir__), '', $absolutePath);

        if( ! move_uploaded_file($file['tmp_name'], $absolutePath) ){
            throw new \Exception('文件保存失败');
        }

        return [
            'absolute_path' => $absolutePath,
            'relative_path' => $relativePath,
            'mime_type'     => $file['type'],
            'client_name'   => $file['name']
        ];
    }


    /**
     * @param $url
     * @param array $body
     * @param array $headers
     * @return mixed
     * @throws Exception
     */
    public static function curlPost($url, $body = [], $headers = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_HTTPHEADER, self::getFormattedHeaders($headers));
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, \http_build_query($body));
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        if( curl_errno($curl) ){
            throw new \Exception('curl通信错误: '. curl_error($curl));
        }
        if($info['http_code'] != 200){
            throw new \Exception('通信异常,状态码'. $info['http_code'].',url:'.$url.',内容:'.(string)$response);
        }
        curl_close($curl);
        return $response;
    }

    public static $defaultHeaders = [];
    public static function getFormattedHeaders($headers)
    {
        $formattedHeaders = array();

        $combinedHeaders = array_change_key_case(array_merge(self::$defaultHeaders, (array) $headers));

        foreach ($combinedHeaders as $key => $val) {
            $formattedHeaders[] = self::getHeaderString($key, $val);
        }

        if (!array_key_exists('user-agent', $combinedHeaders)) {
            $formattedHeaders[] = 'user-agent: shopAdmin';
        }

        if (!array_key_exists('expect', $combinedHeaders)) {
            $formattedHeaders[] = 'expect:';
        }

        return $formattedHeaders;
    }

    private static function getHeaderString($key, $val)
    {
        $key = trim(strtolower($key));
        return $key . ': ' . $val;
    }

    public static function adList()
    {
        /**
         * @var \Medoo\Medoo $db
         */
        $db = \lib\register::getInstance('db');
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');

        if(\strtoupper($_SERVER['REQUEST_METHOD'] != 'GET')){
            return \json_encode([
                'code' => 400,
                'msg'  => 'request method not allowed'
            ]);
        }

        //分页参数
        $limit  = isset($_GET['limit']) ? \min(1e5, \max(10, (int) $_GET['limit'])) : 20;
        $offset = isset($_GET['offset']) ? \max(0, (int) $_GET['offset']) : 0;
        if(isset($_GET['p'])){
            $page = \max(1, (int) $_GET['p']);
            $offset = $limit * ($page - 1);
        }else{
            $page = \ceil($offset / $limit);
        }
        $where = [
            'AND' => []
        ];
        //不是超级管理员
        if( ! $_SESSION['admin']['is_admin']){
            $where['AND']['oa_uid'] = $_SESSION['admin']['uid'];
        }
        if(empty($where['AND'])){
            unset($where['AND']);
        }

        //联表
        $join = ["[>]oa_users" => ['fmp_ad.oa_uid' => 'uid']];
        //字段
        $fields = [
            'fmp_ad.id','fmp_ad.oa_uid',
            'fmp_ad.campaign_name',
            'fmp_ad.adset_name','fmp_ad.customer_price',
            'fmp_ad.targeting_age_min','fmp_ad.targeting_age_max','fmp_ad.targeting_genders','fmp_ad.targeting_interests',
            'fmp_ad.creative_name', 'fmp_ad.creative_subtitle', 'fmp_ad.creative_link_url', 'file',
            'fmp_ad.ad_name', 'fmp_ad.ad_describe', 'fmp_ad.create_at',
            'oa_users.name_cn', 'oa_users.id_department', 'oa_users.department', 'oa_users.company_id'
        ];
        //总条数
        $count = $db->count('fmp_ad', $join, 'fmp_ad.id', $where);
        $where['ORDER'] = ['fmp_ad.id' => 'DESC'];
        $where['LIMIT'] = [$offset, $limit];
        //数据列表
        $outputAdList = $db->select('fmp_ad', $join, $fields, $where);

        return \json_encode([
            'code' => 200,
            'msg'  => '成功获取广告列表',
            'count' => $count,
            'limit' => $limit,
            'offset'=> $offset,
            'page'  => $page,
            'pageCount'  => \ceil($count / $limit),
            'filter' => $where['AND'] ?: [],
            'adList' => $outputAdList
        ]);
    }

    public static function getFmpDomain()
    {
        if(environment == 'office'){
            return 'https://pokerface.stosz.com'; //测试环境也要发邮件改配置，太慢了
        }

        /**
         * @var \lib\config $config
         */
        $config = \lib\register::getInstance('config');
        return $config->get('fmp_domain');
    }

    //前端上传至FMP服务器的配置文件
    public static function getFmpApi()
    {
        $domain = self::getFmpDomain();

        \headers_sent() OR header('Content-type: application/json; charset=utf-8');
        return \json_encode([
            'domain' => $domain,
            'uploadImage' => $domain . '/api/advertising/upload',
            'uploadVideo' => $domain . '/api/advertising/upload',
            'deleteImage' => $domain . '/api/advertising/delupload',
            'deleteVideo' => $domain . '/api/advertising/delupload',
        ]);
    }
}