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

class formatusers extends consumerbase {

    function exec(){
      $res = $this->db->select('oa_users',['uid','path','manager_id'],['manager_id'=>0]);
      (new \lib\consumerlog())->write('basic',print_r($res,1) );
      $this->digui($res,$this->db);
      return true;
    }

    function digui($list,&$db){
      $uid_list = array_column($list,'uid');
      if(empty($list[0]['manager_id'])){
        $res = $db->update('oa_users',['is_base'=>1],['AND'=>['uid'=>$uid_list]]);
        $_list =  $db->select('oa_users',['uid','path','manager_id'],['AND'=>['manager_id'=>$uid_list]]);
        foreach ($_list as $key => $value) {
            $db->update('oa_users',['path'=>','.$value['manager_id']],['uid'=>$value['uid']]);
        }
      }else{
        $uid_str = implode($uid_list, ',');
        $path_arr = array_column($list,'path','uid');
        $_list =  $db->select('oa_users',['uid','path','manager_id'],['AND'=>['manager_id'=>$uid_list]]);
        if(is_array($_list) && count($_list)>0){
          foreach ($_list as $k => $v) {
            $params = ['path'=>$path_arr[$v['manager_id']].','.$v['manager_id']];
            $res = $db->update('oa_users',$params,['uid'=>$v['uid']]);
          }
        }
        $_uid_list = array_column($_list, 'uid');
        $lef_list = array_diff($uid_list, $_uid_list);
        if($lef_list){
          $res = $db->update('oa_users',['is_leaf'=>1],['AND'=>['uid'=>$lef_list]]); 
        }
      }
      if($_list){
        $this->digui($_list,$db);
      }
    }
    
}
