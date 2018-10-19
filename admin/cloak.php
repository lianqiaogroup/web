<?php
/**
 * Created by PhpStorm.
 * User: yangpan
 * Date: 2018/3/14
 * Time: 14:22
 */

require  'ini.php';
$cloak = new admin\cloakModel\cloak_model($register,$db);

$data = $_POST;
if(!empty($data['sensitive']))$sensitive = trim($data['sensitive']);
if(!empty($data['safety']))$safety = trim($data['safety']);
if(!empty($data['id']))$id = trim($data['id']);
if(!empty($data['is_close']))$is_close = trim($data['is_close']);
$uid = $_SESSION['admin']['uid'];
$id_department = $_SESSION['admin']['id_department'];

$filter = [];
switch ($_GET['act']){

    case 'add':
        if (!empty($sensitive)) {
            $filters['sensitive'] = $sensitive;

            if($sensitive == $safety){
                echo $cloak->encode(['ret'=>0,'message' => '敏感站点与安全站点重复，请修改后重新关联！']);
                exit;
            }
            $domain =   strstr($sensitive,'/',true);
            $domain2 =   strstr($safety,'/',true);
            if($domain != $domain2){
                echo $cloak->encode(['ret'=>0,'message' => '敏感站点与安全站点必须是相同一级域名，请修改后重新关联！']);
                exit;
            }
            $sensitive_id = $cloak->web_verification($sensitive);
            if(!$sensitive_id){
                echo $cloak->encode(['ret'=>0,'message' => '敏感站点输入有误']);
                exit;
            }

            if($cloak->cloak(['sensitive_id'=> $sensitive_id])){
                echo $cloak->encode(['ret'=>0,'message' => '敏感站点已经被关联，请勿重复关联！']);
                exit;
            }

            if($cloak->cloak(['safety_id'=>$sensitive_id])){
                echo $cloak->encode(['ret'=>0,'message' => '敏感站点已被添加成安全站点']);
                exit;
            }

            $safety_id = $cloak->web_verification($safety);
            if(!$safety_id){
                echo $cloak->encode(['ret'=>0,'message' => '安全站点输入有误']);
                exit;
            }

            if($cloak->cloak(['sensitive_id'=>$safety_id])){
                echo $cloak->encode(['ret'=>0,'message' => '安全站点已被添加成敏感站点']);
                exit;
            }

            $data['uid']            =   $uid;
            $data['id_department']  =   $id_department;
            $data['sensitive_id']   =   $sensitive_id;
            $data['safety_id']      =   $safety_id;
            $data['username']       =   $_SESSION['admin']['username'];;
            $data['create_at']      =   date('Y-m-d H:i:s',time());

            $result = $cloak->insert_cloak($data);
            if($result) {
                echo $cloak->encode(['ret'=>1,'message'=>'关联成功']);
                exit;
            }else{
                echo $cloak->encode(['ret'=>0,'message'=>'关联失败']);
                exit;
            }
        }else{
            echo $cloak->encode(['ret'=>0,'message'=>'请输入站点']);
            exit;
        }
        break;

    case 'update':
        if(!empty($safety)){
            $safety_id = $cloak->web_verification($safety);
            if(!$safety_id){
                echo $cloak->encode(['ret'=>0,'message' => '安全站点输入有误']);
                exit;
            }

            if($cloak->cloak(['sensitive_id'=>$safety_id])){
                echo $cloak->encode(['ret'=>0,'message' => '该站点是敏感站点,不能添加成安全站点']);
                exit;
            }

            $data['safety_id']  = $safety_id;
        }

        $filter['id'] = $id;
        $result =$cloak->get_cloak($filter);
        $domain =   strstr($result['sensitive'],'/',true);
        $domain2 =   strstr($result['safety'],'/',true);
        if($domain != $domain2){
            echo $cloak->encode(['ret'=>0,'message' => '敏感站点与安全站点必须是相同一级域名，请修改后重新关联！']);
            exit;
        }
        if($cloak->update_cloak($data,$filter)){
            echo $cloak->encode(['ret'=>1,'message' => '更新关联成功！']);
            exit;
        }else{
            echo $cloak->encode(['ret'=>0,'message' => '更新关联失败！']);
            exit;
        }
        break;

    case 'select':
        $comment = new admin\helper\common($register);
        $user = $comment->getUids();
        if(!empty($_POST['uid']))       $filter['uid'] = $_POST['uid'];
        if(!empty($sensitive))          $filter['sensitive[~]'] = ["%" . $sensitive . '%'];
        if(!empty($is_close))           $filter['is_close'] = $is_close;
        if(!empty($user['is_leader'])) {
            $filter['id_department'] =  $user['id_department'];
        } else{
            if(!empty($user['uid']))    $filter['uid'] = $user['uid'][0];
        }

        $result =$cloak->select_cloak($filter);
        foreach ($result['goodsList'] as $k =>$v){
            $result['goodsList'][$k]['region'] = $cloak->get_zone(['product_id'=>$v['sensitive_id']]);
        }

        if($result['goodsList']){
            echo $cloak->encode($result);
        }else{
            echo $cloak->encode(['ret'=>0,'message' => '未查到相关数据']);
        }
        break;
}





