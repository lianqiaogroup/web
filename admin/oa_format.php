<?php
require_once 'ini.php';
$db = $register->get('db');
$res = $db->select('oa_users',['uid','path','manager_id'],['manager_id'=>0]);
digui($res,$db);
// $a =0;global $a ;
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
	    digui($_list,$db);
	  }
}
exit('OK');