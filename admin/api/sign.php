<?php
/**
 * Created by PhpStorm.
 * User: jade
 * Date: 2017/11/11
 * Time: 下午1:52
 */
namespace admin\api;

class sign{

	function check_sign($data){
		$sign = $data['sign'];
		unset($data['sign']);
		$local_sign = $this->gen_sign($data);
		if($sign != $local_sign){
            echo json_encode(['res'=>'fail','data'=>['msg'=>'签名错误']]);exit();
			
		}
	}


	/**
     *
     * 生成签名算法函数
     * @param array $params
     */
    private function gen_sign($params){
        $token = 'JKuis89JNks98h*9(jks@L%s23';//一般从服务器配置文件获取
        if(!$token){
            return false;
        }
        return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
    }

	/**
     *
     * 签名参数组合函数
     * @param array $params
     */
    private function assemble($params)
    {
        if(!is_array($params))  return null;
        ksort($params, SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            if(is_null($val))   continue;
            if(is_bool($val))   $val = ($val) ? 1 : 0;
            $sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
        }
        return $sign;
    }
	
}