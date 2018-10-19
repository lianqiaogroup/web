<?php
/**
 * Created by PhpStorm.
 * User: jade
 * Date: 2017/8/23
 * Time: 11:12
 */

namespace admin\helper\api;

class olderpproduct extends erpbase {

	private $domain = ''; //'http://erp.stosz.com:9090/Product/Api/get';
    private $dev_domain = '' ; //'http://192.168.109.252:8081/Product/Api/get';

    function __construct($type = ''){
        if($type == 'dev'){$this->domain = $this->dev_domain;}
    }

	function getProduct($params){
        /*
		$headers = ['Content-Type:application/x-www-form-urlencoded'];
		$url = $this->domain.'?id='.$params['id'];
		$res = $this->sendGet($url,$headers);
		$res = json_decode($res['message'],1);
        try {
            if(!empty($res['product']) ){
                return $res;
            }
        } catch (Exception $e) {
            
        }*/
        return ['status'=>false,'product'=>[],'msg'=>''];
	}

}