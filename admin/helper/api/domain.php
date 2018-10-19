<?php

namespace admin\helper\api;

class domain  extends erpbase {

	private $domain;//http://112.95.135.114:8090 正式地址

    function __construct($type = ''){
        $this->domain = \lib\register::getInstance('config')->get('apiUrl.domain');
    }

	
	function getDomain($params)
	{
		$token 		= 'WkUCdKeVKJHPDF8uROisFnNrOHJcFgIs';
		$timestamp 	= time();
		$nonce 	= $this->getRandomStr(8);
		$uri 	= $this->domain."/Home/Api/getDomainDepartment";
		$sign 	= $this->getSign($token,$timestamp,$nonce);

		$data = [];
		$data['domain'] 	= $params['domain'];
		$data['timestamp']  = $timestamp;
		$data['nonce'] 		= $nonce;
		$data['sign'] 		= $sign;

        $startTime = microtime(true);
        $res = $this->sendPost($uri, $data, $headers = []);
        $apiTime = number_format(microtime(true) - $startTime, 8, '.', '') * 1000;
        \lib\register::createInstance('\lib\log')->write('product', $uri . '=>' . PHP_EOL . print_r($headers, 1) . print_r($res, 1) . 'api_time=>' . $apiTime .'ms'. PHP_EOL);

        $res = json_decode($res['message'],1);

        if(empty($res['group_name'])){
        	return '';
        }else{
        	return $res;
        }
	}

	//获取邮箱前缀
    function getRegionDomain($domain)
    {
        $token 		= 'WkUCdKeVKJHPDF8uROisFnNrOHJcFgIs';
        $timestamp 	= time();
        $nonce 	= $this->getRandomStr(8);
        $uri 	= $this->domain."/Home/Api/getDomainRegion";
        $sign 	= $this->getSign($token,$timestamp,$nonce);
        $data = [];
        $data['domain'] 	= $domain;
        $data['timestamp']  = $timestamp;
        $data['nonce'] 		= $nonce;
        $data['sign'] 		= $sign;
        $res = $this->sendPost($uri,$data,$headers = []);
        (new \lib\log())->write('product',$uri.'=>'. print_r($headers,1) . print_r($res,1));
        $res = json_decode($res['message'],1);
        if(empty($res['user_name'])){
            return '';
        }else{
            return $res;
        }
    }


	function getSeoDomain($params)
	{
		$token 		= 'WkUCdKeVKJHPDF8uROisFnNrOHJcFgIs';
		$timestamp 	= time();
		$nonce 	= $this->getRandomStr(8);
		$uri 	= $this->domain."/Home/Api/getSeoDomains";
		$sign 	= $this->getSign($token,$timestamp,$nonce);

		$data = [];
		$data['loginid'] 		= $params['loginid'];
		$data['id_department'] 	= $params['id_department'];
		$data['timestamp'] 		= $timestamp;
		$data['nonce'] 			= $nonce;

        $data['project_id'] = \lib\register::getInstance('config')->get('domainProjectId');

        $data['sign'] 			= $sign;

		$res = $this->sendPost($uri,$data,$headers = []);

		(new \lib\log())->write('product',$uri.'=>'. print_r($headers,1) . print_r($res,1) );
        $res = json_decode($res['message'],1);


        if(isset($res['ret'])){
        	return $res;
        }else{
        	return [];
        }
	}
	
	
	function getSign($token,$timestamp,$nonce)
	{
		return md5($token.$timestamp.$nonce);
	}
}