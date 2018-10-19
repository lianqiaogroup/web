<?php
/**
 * Created by PhpStorm.
 * User: jade
 * Date: 2017/8/23
 * Time: 11:12
 */
#use GuzzleHttp\Client;


namespace admin\helper\api;

class olderpseo extends erpbase {

	private $domain = '';//'http://erp.stosz.com:9090/Domain/Api/get_all';
	private $dev_domain = '' ;// 'http://192.168.109.252:8081/Domain/Api/get_all';

    function __construct($type = ''){
        if($type == 'dev'){$this->domain = $this->dev_domain;}
    }

	function getSeo($params){
        //老ERP接口关闭
        /*
		$url = $this->domain.'?name='.$params['name'];
		$res = $this->sendGet($url,$headers);
		$res = json_decode($res['message'],1);
		if($res['status'] && (is_array($res['data'])) && count($res['data'])>0  ){
			return $res;
		}else{
			return [];
		}*/
        return [];
	}
}