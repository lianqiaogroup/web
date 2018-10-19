<?php

require_once __DIR__ .'/ini.php';

use admin\helper\ResourceHelper;

$actionName = isset($_GET['act']) ? trim($_GET['act']) : (isset($_POST['act']) ? trim($_POST['act']) : '');
if(empty($actionName)){
    Resource::notFound();
}else{
    if( !isset($_SESSION, $_SESSION['admin'], $_SESSION['admin']['uid']) || 0 == intval($_SESSION['admin']['uid'])
        || 0 == intval($_SESSION['admin']['id_department'])
    ){
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');
        exit(\json_encode(['code'=>400, 'msg'=>'用户ID为0 或 部门ID为0, 请重新登陆']));
    }

    echo Resource::$actionName();
    exit;
}



class Resource
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

    public static function getConfig()
    {
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');
        $config = \lib\register::getInstance('config')->get('Aliyun');
        if(! $config || ! array_key_exists('OSS',$config)) {
            return json_encode(['code'=>400, 'msg' => '获取OSS签名失败', 'data'=>[]]);
        }
        $oss = $config['OSS'];

        $now = time();
        $end = $now + $oss['Expire'];
        $expiration = \date('Y-m-d\TH:i:s', $end).'Z';

        list($min,$max) = $oss['SizeRange'];
        $conditions = [
            ['content-length-range', $min, $max],
            ['starts-with', '$key', $oss['Dir']]
        ];
        $policy = json_encode(['expiration'=>$expiration,'conditions'=>$conditions]);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1',$string_to_sign,$oss['AccessKeySecret'],true));

        $response = [
            'accessid'=>$oss['AccessKeyID'],
            'host' => $oss['EndPoint'],
            'policy' => $base64_policy,
            'signature' => $signature,
            'bucket' => $oss['Bucket'],
            'expire' => $end,
            'dir' => $oss['Dir']
        ];
        return json_encode(['code'=>200, 'msg' => '成功获取OSS签名', 'data'=>$response]);
    }

    /**
     * 获取资源类型
     *
     * @return string
     */
    public static function types()
    {
        /**
         * @var \Medoo\Medoo $db
         */
        $db = \lib\register::getInstance('db');
        $response = $db->select('resource_type',['id','type_name'], ['ORDER' => 'id']);
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');
        $errorInfo = $db->error();
        if($errorInfo[0] === '00000'){
            return json_encode(['code'=>200, 'msg' => '成功获取资源类型', 'typeList'=>(array) $response]);
        }else{
            return json_encode(['code'=>400, 'msg' => '获取资源类型失败'.$errorInfo[2]]);
        }
    }

    /**
     * 获取上传创作者列表
     *
     * @return string
     */
    public static function authorList()
    {
        /**
         * @var \Medoo\Medoo $db
         */
        $db = \lib\register::getInstance('db');
        $pdoStatement = $db->query('select DISTINCT(ad_member) from material;');
        $result = $pdoStatement ? $pdoStatement->fetchAll(\PDO::FETCH_ASSOC) : [];
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');
        $errorInfo = $db->error();
        if($errorInfo[0] === '00000'){
            $authorList = \array_column((array)$result, 'ad_member');
            return json_encode(['code'=>200, 'msg' => '成功获取创作者列表', 'authorList'=> $authorList]);
        }else{
            return json_encode(['code'=>400, 'msg' => '获取创作者列表失败'.$errorInfo[2]]);
        }
    }

    public static function query()
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

        $input = file_get_contents("php://input");
        if($input){
            $request = json_decode($input);
            if(\json_last_error() !== \JSON_ERROR_NONE){
                return \json_encode([
                    'code' => 400,
                    'msg'  => \json_last_error_msg()
                ]);
            }
        }else{
            $request = new stdClass();
        }

        //分页参数
        $limit  = isset($request->limit) ? \min(1e5, \max(1, (int) $request->limit)) : 15;
        $offset = isset($request->offset) ? \max(0, (int) $request->offset) : 0;
        if(isset($request->p)){
            $page = \max(1, (int) $request->p);
            $offset = $limit * ($page - 1);
        }else{
            $page = \ceil($offset / $limit);
        }
        $where = [
            'AND' => []
        ];

        if (isset($request->product_category_id) && $request->product_category_id != '') {//产品类别
            $categoryIdList = \explode(',', $request->product_category_id);
            /*if( count($categoryIdList) == 1) {
                $categoryIdList = self::getChildCategoryIdList($categoryIdList[0]);
            }*/
            $where['AND']['product_category_id'] = $categoryIdList;
        }
        if (isset($request->resource_type_id) && $request->resource_type_id > 0) {//素材类型
            $where['AND']['resource_type_id'] = intval($request->resource_type_id);
        }
        if (isset($request->beginDate) && $request->beginDate) {//开始时间
            $where['AND']['add_time[>]'] = (string) ($request->beginDate);
        }
        if (isset($request->endDate) && $request->endDate) {//结束时间
            $where['AND']['add_time[<]'] = (string) ($request->endDate);
        }

        if (isset($request->format)) {//静态图IMAGE, 视频VIDEO, 动图GIF, PSD, 其他 FILE
            switch (strtoupper($request->format)) {
                case 'IMAGE':
                    $where['AND']['format'] = [
                        ResourceHelper::getMimeType('JPG'),
                        ResourceHelper::getMimeType('PNG'),
                        ResourceHelper::getMimeType('BMP')
                    ];
                    break;
                case 'VIDEO':
                    $where['AND']['format'] = ResourceHelper::getMimeType('MP4');
                    break;
                case 'GIF':
                    $where['AND']['format'] = ResourceHelper::getMimeType('GIF');
                    break;
                case 'PSD':
                    $where['AND']['format'] = [
                        ResourceHelper::getMimeType('PSD'),
                        ResourceHelper::getMimeType('FILE')
                    ];
                    break;
                default:
                    $where['AND']['format'] = ResourceHelper::getMimeType('FILE');
                    break;
            }
        }
        if (isset($request->mimeType) && $request->mimeType) {//mimeType
            $where['AND']['format'] = (string) $request->mimeType;
        }
        if (isset($request->ad_member) && $request->ad_member) {//创作者
            $where['AND']['ad_member'] = (string) $request->ad_member;
        }

        if (isset($request->tag_ids) && $request->tag_ids) {
            /*if(! isset($where['AND']['format']) ){
                return \json_encode([
                    'code' => 400,
                    'msg'  => '搜索范围太广, 请选择其他搜索条件'
                ]);
            }*/
            $tag_ids = \explode(',', $request->tag_ids);
            $resourceIdList = (array) $db->select('resource_tags_assoc', ['resource_id'], [
                'tag_id'=> $tag_ids,
                'ORDER' => ['resource_id' => 'DESC'],
                'LIMIT' => 500 //建议开启Sphinx或ES
            ]);
            $errorInfo = $db->error();
            if($errorInfo[0] !== '00000'){
                return json_encode([
                    'code' => 400,
                    'msg'  => '获取素材文件列表失败:'.$errorInfo[2],
                    'count' => 0,
                    'limit' => $limit,
                    'offset'=> $offset,
                    'page'  => $page,
                    'pageCount' => \ceil(0 / $limit),
                    'filter' => $where['AND'] ?: [],
                    'resourceList' => []
                ]);
            }
            if($resourceIdList){
                $where['AND']['id'] = \array_column($resourceIdList, 'resource_id');
            }else{
                return json_encode([
                    'code' => 200,
                    'msg'  => '获取素材文件列表失败, 该标签没有素材',
                    'count' => 0,
                    'limit' => $limit,
                    'offset'=> $offset,
                    'page'  => $page,
                    'pageCount' => \ceil(0 / $limit),
                    'filter' => $where['AND'] ?: [],
                    'resourceList' => []
                ]);
            }
        }

        if(empty($where['AND'])){
            unset($where['AND']);
        }

        $count = $db->count('material',$where);
        $errorInfo = $db->error();
        if($errorInfo[0] !== '00000'){
            return json_encode([
                'code' => 400,
                'msg'  => '获取素材文件列表失败:'.$errorInfo[2],
                'count' => 0,
                'limit' => $limit,
                'offset'=> $offset,
                'page'  => $page,
                'pageCount' => \ceil(0 / $limit),
                'filter' => $where['AND'] ?: [],
                'resourceList' => []
            ]);
        }

        $where['ORDER'] = ['id' => 'DESC'];
        $where['LIMIT'] = [$offset, $limit];

        $fields = ['id','mtype','mtag','thumb', 'format(mimeType)','product_category_id','resource_type_id', 'size','ad_member','add_time'];
        $resourceList = $db->select('material', $fields, $where);

        $aliyunConfig = \lib\register::getInstance('config')->get('Aliyun');
        $resourceList = array_map(function($rs) use ($aliyunConfig) {
            $rs['size']   = ResourceHelper::formatSize($rs['size']);
            $rs['format'] = ResourceHelper::getFormat($rs['mimeType']);
            $rs['thumb']  = $aliyunConfig['OSS']['EndPoint'].'/'. $rs['thumb'] ;
            return $rs;
        }, $resourceList);
        //TODO 显示素材标签

        return json_encode([
            'code' => 200,
            'msg'  => '成功获取素材文件列表',
            'count' => $count,
            'limit' => $limit,
            'offset'=> $offset,
            'page'  => $page,
            'pageCount' => \ceil($count / $limit),
            'filter' => $where['AND'] ?: [],
            'resourceList' => $resourceList
        ]);
    }

    private static function getChildCategoryIdList($categoryId)
    {
        $categoryIdList = [$categoryId];
        $categoryList = json_decode(self::categoryList(), true);
        if($categoryList['code'] != 200){
            return $categoryIdList;
        }
        $categoryList = $categoryList['categoryList'][0]['children'];

        foreach($categoryList as $categoryInfo)
        {
            if($categoryInfo['id'] == $categoryId){
                //一级结点,找出二级
                foreach($categoryInfo['children'] as $categoryInfo2){
                    $categoryIdList[] = $categoryInfo2['id'];
                    //三级结点
                    foreach($categoryInfo2['children'] as $categoryInfo3){
                        $categoryIdList[] = $categoryInfo3['id'];
                    }
                }
                break;
            }
            //可能是二级结点
            foreach($categoryInfo['children'] as $categoryInfo2){
                if($categoryInfo2['id'] == $categoryId){
                    $categoryIdList[] = $categoryInfo2['id'];
                    //三级结点
                    foreach($categoryInfo2['children'] as $categoryInfo3){
                        $categoryIdList[] = $categoryInfo3['id'];
                    }
                    break 2;
                }
            }
        }

        return $categoryIdList;
    }

    public static function create()
    {
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');

        $request = file_get_contents('php://input');
        $request = json_decode($request,true);
        if( \json_last_error() !== \JSON_ERROR_NONE ) {
            return \json_encode(['code'=>400, 'msg'=>'表单JSON解析失败:'. \json_last_error_msg()]);
        }
        if(!isset($request['product_category_id']) || intval($request['product_category_id']) < 1){
            return json_encode(['code'=>400, 'msg'=>'请选择 产品分类']);
        }
        if(!isset($request['resource_type_id']) || intval($request['resource_type_id']) < 1){
            return json_encode(['code'=>400, 'msg'=>'请选择 素材类型']);
        }
        if(!isset($request['datas']) || empty($request['datas'])){
            return json_encode(['code'=>400, 'msg'=>'请先上传素材']);
        }
        /**
         * @var \Medoo\Medoo $db
         */
        $db = \lib\register::getInstance('db');
        $product_category_id = intval($request['product_category_id']);
        $resource_type_id    = intval($request['resource_type_id']);
        $unixDate = \date('Y-m-d H:i:s');
        $adMember = $_SESSION['admin']['username'];

        $list = $request['datas'];
        foreach ($list as $row){
            $insertInfo = [
                'mtype' => (int) $product_category_id,
                'mtag'  => (int) $resource_type_id,
                'ad_member' => (string) $adMember,
                'add_time'  => $unixDate,
                'thumb' => (string) $row['thumb'],
                'format'=> (string) $row['mimeType'],
                'size'  => $row['size'],
                'product_category_id' => (int) $product_category_id,
                'resource_type_id'  =>  (int) $resource_type_id
            ];
            $success = $db->insert('material',$insertInfo);
            if(!$success){
                continue;
            }
            $resourceId = $db->id();

            //标签
            if(isset($request['tag_ids']) && $request['tag_ids']){
                $tagIds = \array_values(\array_filter(\explode(',', $request['tag_ids']), 'ctype_digit'));
                $assocList = [];
                foreach($tagIds as $tagId){
                    $assocList[] = [
                        'tag_id' => $tagId,
                        'resource_id' => $resourceId
                    ];
                }
                $db->insert('resource_tags_assoc', $assocList);
                unset($assocList, $tagIds);
                $errorInfo = $db->error();
                if($errorInfo[0] !== '00000'){
                    return json_encode(['code'=>400, 'msg'=>'标签失败!'.$errorInfo[2]]);
                }
            }
        }

        return json_encode(['code'=>200, 'msg'=>'新增成功!']);
    }

    public static function delete()
    {
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');

        if(!isset($_POST['resource_id']) || intval($_POST['resource_id']) < 1 ){
            return json_encode(['code'=>400, 'msg'=>'ID为空, 请选择要删除的素材资源']);
        }
        $resourceId = intval($_POST['resource_id']);

        /**
         * @var \Medoo\Medoo $db
         */
        $db = \lib\register::getInstance('db');
        $where = ['id' => $resourceId];
        $exists = $db->get('material', 'id', $where);
        if(! $exists ){
            return json_encode(['code'=>400, 'msg'=>'素材资源不存在, 删除失败!']);
        }
        $success = $db->delete('material', $where);
        if($success){
            //标签
            $db->delete('resource_tags_assoc', ['resource_id' => $resourceId]);
            return json_encode(['code'=>200, 'msg'=>'删除成功!']);
        }else{
            $errorInfo = $db->error();
            return json_encode(['code'=>400, 'msg'=>'删除失败!'.$errorInfo[2]]);
        }
    }

    //品类树
    public static function categoryList()
    {
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');

        $domian = \lib\register::getInstance('config')->get('apiUrl.erp');
        $url = $domian. '/product/base/category/tree';
        try{
            $str = self::sendGet($url, self::getHeaders());
            $res = json_decode($str, true);
            if ( \json_last_error() !== \JSON_ERROR_NONE ) {
                return json_encode(['code'=>400, 'msg'=>'ERP返回的分类数据异常: '. \json_last_error_msg(), 'erp_debug' => $str]);
            } else {
                if(isset($res['code']) && $res['code'] == 'OK') {
                    return json_encode(['code'=>200, 'msg' => '成功获取ERP产品分类', 'categoryList'=> $res['item']]);
                }else{
                    return json_encode(['code'=>400, 'msg' => $res['desc']]);
                }
            }
        }catch (\Exception $e){
            return json_encode([ 'code' => 400, 'msg' => $e->getMessage() ]);
        }
    }

    private static function getHeaders()
    {
        $token  = \lib\register::getInstance('config')->get('erpToken');
        $timestamp = self::getMillisecond();
        $nonce = self::getRandomStr(8);
        $sign  = \md5($token.$timestamp.$nonce);
        $headers = [
            "X-PROJECT-ID:frontend.website.build",
            "Accept:application/json,text/plain,*/*",
            "x-requested-with:XMLHttpRequest",
            "X-AUTH-TIMESTAMP:$timestamp",
            "X-AUTH-NONCE:$nonce",
            "X-AUTH-SIGNATURE:$sign",
        ];
        return $headers;
    }

    private static function getMillisecond(){
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    private static function getRandomStr( $length = 8 ) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str ='';
        for ( $i = 0; $i < $length; $i++ )
        {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private static function sendGet($url,$headers =[]){
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, False);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30000);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30000);
        $str = curl_exec($curl);

        if(curl_errno($curl)){
            throw new \Exception(curl_error($curl));
        }
        $info = curl_getinfo($curl);
        if($info['http_code'] != 200){
            throw new \Exception($str);
        }
        if(empty($str)){
            throw new \Exception('ERP没有返回内容');
        }
        curl_close($curl);
        return $str;
    }


    private static function sendPost($url,$data,$headers=[]){

        //'Content-Type:application/x-www-form-urlencoded'
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl,CURLOPT_POST,true);
        curl_setopt($curl,CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30000);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30000);
        $str = curl_exec($curl);

        if(curl_errno($curl)){
            throw new \Exception(curl_error($curl));
        }
        $info = curl_getinfo($curl);
        if($info['http_code'] != 200){
            throw new \Exception($str);
        }
        if(empty($str)){
            throw new \Exception('ERP没有返回内容');
        }
        curl_close($curl);
        return $str;
    }

}




