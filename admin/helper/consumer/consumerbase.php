<?php
/**
 * Created by PhpStorm.
 * User: jade
 * Date: 2017/8/23
 * Time: 11:12
 */
#use GuzzleHttp\Client;


namespace admin\helper\consumer;
use admin\helper\common;

class consumerbase {

	protected $db;
	
	function __construct(){
		if(!$this->db){
			//加载配置文件
			//require_once app_path.'config/base.php';
            $register = new \lib\register();
            //加载配置文件
            $config = new \lib\config();
            $register->set('config',$config);
			ini_set('date.timezone','Asia/Shanghai');
			// include_once 'helper/function.php';
			// $config = require_once app_path.'config/web.php';
			$this->db = new \lib\db();
		}
		
	}

	protected function sendPost($url,$data,$headers=[]){
        #'Content-Type:application/x-www-form-urlencoded'
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
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

    /**
     * GET方法
     * @param $url
     * @return mixed
     */
    protected function sendGet($url,$headers =[]){
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        if($headers){
            curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);    
        }
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, False);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
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