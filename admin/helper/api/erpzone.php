<?php
/**
 * Created by PhpStorm.
 * User: jade
 * Date: 2017/8/23
 * Time: 11:12
 */
#use GuzzleHttp\Client;


namespace admin\helper\api;

class erpzone  extends erpbase {
    private $domain;//http://112.95.135.114:8090 正式地址

    function __construct($type = ''){
        $this->domain = \lib\register::getInstance('config')->get('apiUrl.erp');
    }

	function getZone(){
        $token = \lib\register::getInstance('config')->get('erpToken');
		$timestamp = $this->getMillisecond();
		$nonce = $this->getRandomStr(8);
		$uri = $this->domain."/product/base/zone/list";
		$sign = $this->getSign($token,$timestamp,$nonce);
		$headers = [
			"X-PROJECT-ID:frontend.website.build",
			"Accept:application/json,text/plain,*/*",
			"x-requested-with:XMLHttpRequest",
			"X-AUTH-TIMESTAMP:$timestamp",
			"X-AUTH-NONCE:$nonce",
			"X-AUTH-SIGNATURE:$sign",
		];
		$res = $this->sendGet($uri,$headers);
		(new \lib\log())->write('product',$uri.'=>'. print_r($headers,1) . print_r($res,1) );
        $res = json_decode($res['message'],1);
        if(($res['code'] == 'OK') && (is_array($res['item'])) && (count($res['item']) > 0) ){
            return $res['item'];
        }
		return null;
	}

	
	
	function getSign($token,$timestamp,$nonce){
		return md5($token.$timestamp.$nonce);
	}

	
}