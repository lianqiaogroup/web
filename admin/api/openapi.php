<?php
/**
 * Created by PhpStorm.
 * User: jade
 * Date: 2017/11/11
 * Time: 下午1:52
 */
namespace admin\api;

class openapi{
	protected $db;
	
	function __construct(){
		if(!$this->db){
			//加载配置文件
			ini_set('date.timezone','Asia/Shanghai');
			$this->db = new \lib\db();
			(new sign())->check_sign($_REQUEST);
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
	