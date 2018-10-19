<?php
/**
 * Created by PhpStorm.
 * User: jade
 * Date: 2017/8/23
 * Time: 11:12
 */
#use GuzzleHttp\Client;


namespace admin\helper\api;

class erpseo extends erpbase {

    private $domain;//http://112.95.135.114:8090 正式地址

    function __construct($type = ''){
        $this->domain = \lib\register::getInstance('config')->get('apiUrl.erp');
    }

	## old seo接口作为过渡 (已关闭)
	function getSeo($params){
        /*
		$this->domain = 'http://erp.stosz.com:9090/Domain/Api/get_all';
		$url = $this->domain.'?name='.$params['name'];
		$res = $this->sendGet($url,[]);
		$res = json_decode($res['message'],1);
		if($res['status'] && (is_array($res['data'])) && count($res['data'])>0  ){
			return $res;
		}else{
			return [];
		}*/
        return [];
	}

	## 目前此接口有些问题，暂时继续用旧的
	function getSeo2($params){
		#测试服务器:http://luckydog-erp-product-dev.stosz.com:8082
		#接口uri：/pc/getAdUserList/loginid/{loginid}?q=徐
        $token = \lib\register::getInstance('config')->get('erpToken');
		$timestamp = ''.(1000 * microtime(true));
		$nonce = $this->getRandomStr(8);
		if(empty($params['q'])){
			$uri = $this->domain."/product/pc/getAdUserList/loginid/".$params['loginid'];
		}else{
			$uri = $this->domain."/product/pc/getAdUserList/loginid/".$params['loginid']."?q=".$params['q'];
		}
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
		$res = json_decode($res['message'],1);
        if(($res['code'] == 'OK') && !$res['item'] ){
        	return $res['item'];
        }
		return [];
	}

	
	
	function getSign($token,$timestamp,$nonce){
		return md5($token.$timestamp.$nonce);
	}

}