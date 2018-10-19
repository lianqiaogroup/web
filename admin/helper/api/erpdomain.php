<?php
/**
 * Created by PhpStorm.
 * User: jade
 * Date: 2017/8/23
 * Time: 11:12
 */
#use GuzzleHttp\Client;


namespace admin\helper\api;

use lib\register;

class erpdomain  extends erpbase {
	private $domain;//http://112.95.135.114:8090 正式地址
    function __construct($type = ''){
       // if($type == 'dev'){$this->domain = $this->dev_domain;}
        $this->domain = register::getInstance('config')->get('apiUrl.erp');
    }

	function sendDomain($params){
		return ['ret'=>'1'];

		#测试服务器:http://luckydog-erp-product-dev.stosz.com:8082
		#接口uri：/pc/archive/product/{productId}/loginid/{loginId}?zoneid={zoneid}&webDirectory=二级目录&domain=建站域名
        $token = \lib\register::getInstance('config')->get('erpToken');
		$timestamp = $this->getMillisecond();
		$nonce = $this->getRandomStr(8);//$params['loginid'] = 'zhoujieying';
		$uri = $this->domain."/product/pc/archive/product/".$params['erp_id']."/loginid/".$params['loginid']."?zoneId=".$params['zone_id']."&webDirectory=".$params['tags']."&domain=".$params['domain'].'&siteProductId='.$params['product_id'].'&seoLoginId='.$params['seo_loginid'];
		
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
		// error_log($uri,3,'domain_url.txt');
		// error_log($res['message'],3,'domain.txt');
		(new \lib\log())->write('product',$uri.'=>'. print_r($headers,1) . print_r($res,1) );
        $res = json_decode($res['message'],1);
        
        if(($res['code'] == 'OK') && (is_array($res['item'])) && (count($res['item']) > 0) ){
            return ['ret'=>'1'];//success
        }else{
        	$msg = empty($res['desc'])?'未知错误':$res['desc'];
        	return ['ret'=>'0','desc'=>$msg];
        }
		
	}

	
	
	function getSign($token,$timestamp,$nonce){
		return md5($token.$timestamp.$nonce);
	}

	
}