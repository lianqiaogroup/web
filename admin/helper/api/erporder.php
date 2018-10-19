<?php
/**
 * Created by PhpStorm.
 * User: jade
 * Date: 2017/8/23
 * Time: 11:12
 */
#use GuzzleHttp\Client;


namespace admin\helper\api;

class erporder extends erpbase {

    private $domain;//http://112.95.135.114:8090 正式地址

    function __construct($type = ''){
        $this->domain = \lib\register::getInstance('config')->get('apiUrl.erp');
    }

	function getOrderStatus($params){
		
	}

	

	function getRandomStr( $length = 8 ) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$str ='';
		for ( $i = 0; $i < $length; $i++ )
		{
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
	
	function getSign($token,$timestamp,$nonce){
		return md5($token.$timestamp.$nonce);
	}
}