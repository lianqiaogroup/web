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

class synccountry extends consumerbase {

    function exec($msg_info){
    	$v = $msg_info['data'];
        $data = [];
        // if(!empty($v['ename'])){
        //     $data['iso_code2'] = $v['ename'];
        // }
        if(!empty($v['countryCode'])){
            $data['iso_code2'] = $v['countryCode'];
            $data['iso_code3'] = $v['countryCode'];
        }
        if(!empty($v['currencyCode'])){
            $data['currency_code'] = $v['currencyCode'];
        }
    	if($ret = $this->db->get('country',['id_country'],['title'=>$v['name']])){
    		$res = $this->db->update('country',$data,['id_country'=>$ret['id_country']]);
            (new \lib\consumerlog())->write('basic',print_r($this->db->last(),1) );
            // var_dump($res);
          	return true;
        }else{
            $data['title'] = $v['name'];
            $res = $this->db->insert('country',$data);
            (new \lib\consumerlog())->write('basic',print_r($this->db->last(),1) );
            if($res){
            	return true;
         	}
          	return false;
        }
    }
}