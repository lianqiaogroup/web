<?php
/**
 * Created by PhpStorm.
 * User: jade
 * Date: 2017/9/19
 * Time: 11:12
 */

namespace admin\helper\api;

abstract class erpbase {

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

    /**
     * 生成随机字符串
     * @param $length
     * @return string
     */
    protected function getRandomStr( $length = 8 ) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$str ='';
		for ( $i = 0; $i < $length; $i++ )
		{
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}

    protected function getMillisecond(){
        list($s1, $s2) = explode(' ', microtime()); 
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * 通用返回
     * @param $ret 标志
     * @param $message 返回错误信息
     * @param $data 返回数据
     */
    public function ret($message = '',$data = '',$ret = false){

        if(!$ret){
            return ['ret'=>0,'msg'=>$message,'data'=>$data];
        }else{
            return ['ret'=>1,'msg'=>$message,'data'=>$data];
        }

    }

}