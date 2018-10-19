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

class synczone extends consumerbase {

    function exec($msg_info){
    	$v = $msg_info['data'];
    	$params = [];
    	if($ret = $this->db->get('zone',['id_zone'],['title'=>$v['title']])){
          $this->db->update('zone',['erp_id_zone'=>$v['id'],'erp_country_id'=>$v['countryId'],'erp_parent_id'=>$v['parentId'],'code'=>$v['code'],'currency'=>$v['currency']],['id_zone'=>$ret['id_zone']]);
          (new \lib\consumerlog())->write('basic',print_r($this->db->last(),1) );
          return true;
        }else{
          if($v['title'] == '港澳台新'){
            return true;//不添加 港澳台新  地区
          }else{
            $r = $this->db->insert('zone',['erp_id_zone'=>$v['id'],'erp_country_id'=>$v['countryId'],'erp_parent_id'=>$v['parentId'],'code'=>$v['code'],'currency'=>$v['currency'],'title'=>$v['title']]);
            (new \lib\consumerlog())->write('basic',print_r($this->db->last(),1) );
            if($r){
              return true;
            }
          }
          
          return false;
        }
    }
}