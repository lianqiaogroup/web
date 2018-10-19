<?php
/**
 * Created by PhpStorm.
 * User: jade
 * Date: 2017/8/23
 * Time: 11:12
 */

namespace admin\helper\api;

/**
 * Class pageView
 * @package admin\helper\api
 * @deprecated order_quantity订单量统计 不调用这个接口了吗?
 */
class pageView extends erpbase
{
    private $token;
    private $productId;
    private $startTime;
    public $endTime;
    public $key ='dev@cuckoo';
    public $uri = 'http://www.ouoho.com/log/findpv';

    public function getPageView(){

        $token =$this->createToken();

        $param['token'] = $token;
        $param['productid'] = $this->productId;
        $param['start'] = $this->startTime;
        $param['end'] =$this->endTime;
        $ret = $this->sendPosts($this->uri,$param);
        \lib\register::getInstance('log')->write('pageView',print_r($ret,1));

        if(!$ret || $ret['status']){
            $this->ret('数据返回超时');
        }

        $ret = json_decode($ret['message'],true);
        if($ret['code'] !=200){
            $this->ret($ret['message']);
        }
        $data =  $ret['data'];
        return  $this->ret('OK',$data,true);

    }

    public function setProductId($value){
        $this->productId = $value;
    }
    public function setStartTimer($value){
        $this->startTime = $value;
    }

    public function setEndTimer($value){
        $this->endTime = $value;
    }

    public function createToken(){
        if(!$this->productId){
            $this->ret('product_id is Required');
        }
        $this->token = md5($this->key.$this->productId);

        return $this->token;
    }

    public function sendPosts($url,$data,$headers=[]){
        #'Content-Type:application/x-www-form-urlencoded'
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
        curl_setopt($curl, CURLOPT_TIMEOUT,20);
        $result = curl_exec($curl);
        $retdata['status'] = 1;

        if($error = curl_error($curl)){
            $retdata['status'] = 0;
            $retdata['message'] = $error;
        }else{
            $retdata['message'] = $result;
        }
        curl_close($curl);
        return $retdata;
    }

}