<?php
/**
 * Created by PhpStorm.
 * User: zdb
 * Date: 2018/8/18
 * Time: 15:33
 */

namespace cmd;

class ErpDebug extends \Symfony\Component\Console\Command\Command
{
    public $msg;
    public function __construct($msg = '')
    {
        $this->msg = $msg;
        parent::__construct();

    }

    public function configure()
    {
        $this->setName('ErpDebug');
    }


    public function execute(\Symfony\Component\Console\Input\InputInterface $input = null, \Symfony\Component\Console\Output\OutputInterface $output = null)
    {
        $this->init();

        $ret = $this->getProduct('178524', 'wujiashengD');
        var_dump($ret);
        return 0;
    }

    private function getProduct($erpProductId, $loginId)
    {

        try{
            $domain = $this->config->get('apiUrl.erp');
            $url = $domain.'/product/pc/product/'.$erpProductId.'/seoLoginid/'.$loginId;
            echo $url;
            $response = $this->sendGet($url, $this->getHeaders());
        }catch (\Exception $e){
            echo $e->getMessage();exit;
            return [ 'code' => 400, 'msg' => $e->getMessage() ];
        }
        var_dump($response);exit;
        $response = json_decode($response,true);
        if( \json_last_error() != \JSON_ERROR_NONE ){
            return [ 'code' => 400, 'msg' => \json_last_error_msg() ];
        }
//        if( $response['code'] !== 'OK' || ! is_array($response['item']) ){
//            return [ 'code' => 400, 'msg'  => $response['desc'] ];
//        }

//        {
//            "code" : "OK",
//          "desc" : "批量查询属性值成功!",
//          "item" : [ {
//                "creatorId" : null,
//                "id" : 3875,
//                "title" : "藍色",
//                "createAt" : "2017-10-26 19:58:24",
//                "updateAt" : "2017-10-26 19:58:24",
//                "attributeId" : 919,
//                "version" : -3875,
//                "relId" : 3875,
//                "bindIs" : null,
//                "attributeTitle" : null,
//                "attributeValueLangs" : null,
//                "productId" : null,
//                "productAttributeValueRel" : null,
//                "table" : "attribute_value"
//              } ]
//        }


        return [ 'code' => 200, 'data' => $response ];
    }

    //获取ERP的属性值名称(采购人员输入的中文,不是外文)
    protected function getErpAttrValueList($erpAttrValueIdList)
    {
        if(empty($erpAttrValueIdList)){
            return [ 'code' => 200, 'data' => [] ];
        }
        try{
            $domian = $this->config->get('apiUrl.erp');
            $url = $domian. '/product/manage/findAttrValByIds';
            $response = $this->sendPost($url, ['ids' => \implode(',', $erpAttrValueIdList) ], $this->getHeaders());
        }catch (\Exception $e){
            return [ 'code' => 400, 'msg' => $e->getMessage() ];
        }
        $response = json_decode($response,true);
        if( \json_last_error() != \JSON_ERROR_NONE ){
            return [ 'code' => 400, 'msg' => \json_last_error_msg() ];
        }
        if( $response['code'] !== 'OK' || ! is_array($response['item']) ){
            return [ 'code' => 400, 'msg'  => $response['desc'] ];
        }

//        {
//            "code" : "OK",
//          "desc" : "批量查询属性值成功!",
//          "item" : [ {
//                "creatorId" : null,
//                "id" : 3875,
//                "title" : "藍色",
//                "createAt" : "2017-10-26 19:58:24",
//                "updateAt" : "2017-10-26 19:58:24",
//                "attributeId" : 919,
//                "version" : -3875,
//                "relId" : 3875,
//                "bindIs" : null,
//                "attributeTitle" : null,
//                "attributeValueLangs" : null,
//                "productId" : null,
//                "productAttributeValueRel" : null,
//                "table" : "attribute_value"
//              } ]
//        }

        //筛选必要字段
        $attrValueList = [];
        foreach($response['item'] as &$attrValueInfo){
            array_push($attrValueList, [
                'id'    => $attrValueInfo['relId'],
                'title' => $attrValueInfo['title']
            ]);
        }
        return [ 'code' => 200, 'data' => $attrValueList ];
    }

    private function getHeaders()
    {
        $token  = $this->config->get('erpToken');
        $timestamp = (int) (microtime(true) * 1000);
        $nonce = $this->getRandomStr(8);
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

    protected function getMillisecond(){
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    protected function getRandomStr( $length = 8 ) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str ='';
        for ( $i = 0; $i < $length; $i++ )
        {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    protected function sendPost($url,$data,$headers=[]){

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

    protected function sendGet($url,$headers=[]){

        //'Content-Type:application/x-www-form-urlencoded'
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl,CURLOPT_POST,false);
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



    /**
     * @var \lib\config @config
     * @var \lib\log @$logger
     * @var \Medoo\Medoo $db
     */
    protected $config;
    //protected $logger;
    protected $db;
    protected $register;
    protected function init()
    {
        $this->config = new \lib\config();
        //$this->logger = new \lib\log();
        //fuck register , 不是正规的DI
        $this->register = new \lib\register();
        $this->register->set('config', $this->config);
        set_time_limit(0);
        ini_set('memory_limit', '-1');
    }

}