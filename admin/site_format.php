<?php

//fixme:待确认需要删除的文件
require_once 'ini.php';
$db = $register->get('db');
$res = $db->select('site','domain',[]);
$res = array_unique($res);
// var_dump($res);
// exit();
format_oa_id_department($res,$db);
// $a =0;global $a ;
function format_oa_id_department($list,&$db){
	if(environment == 'office'){
		$url = 'http://192.168.105.136/Home/Api/getDomainDepartment?domain=';
	}else{
		$url = 'http://domain.stosz.com/Home/Api/getDomainDepartment?domain=';
	}
	if($list){
		foreach ($list as $key => $value) {
			$r = sendGet($url . $value);
			if($r['status'] && $r['message'] && ($r = json_decode($r['message'],1)) ){
				$db->update('site',['oa_id_department'=>$r['erp_department_id']],['domain'=>$value]);
			}else{
				echo $value."
";
			}
		}
	}
	
	// $uid_list = array_column($list,'uid');
	//   if(empty($list[0]['manager_id'])){
	//     $res = $db->update('oa_users',['is_base'=>1],['AND'=>['uid'=>$uid_list]]);
	//     $_list =  $db->select('oa_users',['uid','path','manager_id'],['AND'=>['manager_id'=>$uid_list]]);
	//     foreach ($_list as $key => $value) {
	//         $db->update('oa_users',['path'=>','.$value['manager_id']],['uid'=>$value['uid']]);
	//     }
	//   }else{
	//     $uid_str = implode($uid_list, ',');
	//     $path_arr = array_column($list,'path','uid');
	//     $_list =  $db->select('oa_users',['uid','path','manager_id'],['AND'=>['manager_id'=>$uid_list]]);
	//     if(is_array($_list) && count($_list)>0){
	//       foreach ($_list as $k => $v) {
	//         $params = ['path'=>$path_arr[$v['manager_id']].','.$v['manager_id']];
	//         $res = $db->update('oa_users',$params,['uid'=>$v['uid']]);
	//       }
	//     }
	//     $_uid_list = array_column($_list, 'uid');
	//     $lef_list = array_diff($uid_list, $_uid_list);
	//     if($lef_list){
	//       $res = $db->update('oa_users',['is_leaf'=>1],['AND'=>['uid'=>$lef_list]]); 
	//     }
	//   }
	  // if($_list){
	  //   digui($_list,$db);
	  // }
}

function sendGet($url,$headers =[]){
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
exit('OK');