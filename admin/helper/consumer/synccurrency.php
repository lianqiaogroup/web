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

class synccurrency extends consumerbase {

    function exec($msg_info){
      	$v = $msg_info['data'];
    	$data = [];
    	if(!empty($v['currencyCode'])){
    		$data['currency_code'] = $v['currencyCode'];
    	}
    	if(!empty($v['rateCny'])){
    		$data['exchange_rate'] = $v['rateCny'];
    	}
    	if(!empty($v['symbolLeft'])){
    		$data['symbol_left'] = $v['symbolLeft'];
    	}
    	if(!empty($v['symbolRight'])){
    		$data['symbol_right'] = $v['symbolRight'];
    	}
        // $v['name'] = $v['name'].'__test';
    	// if(!empty($v['currency_format'])){
    	// 	$data['currency_format'] = $v['currency_format'];
    	// }
    	if($ret = $this->db->get('currency',['currency_id'],['currency_title'=>$v['name']])){
          $this->db->update('currency',$data,['currency_id'=>$ret['currency_id']]);
          (new \lib\consumerlog())->write('basic',print_r($this->db->last(),1) );
          return true;
        }else{
        	$data['currency_title'] = $v['name'];
            $r = $this->db->insert('currency',$data);
            (new \lib\consumerlog())->write('basic',print_r($this->db->last(),1) );
            if($r){
            	return true;
         	}
          	return false;
        }
    }
}