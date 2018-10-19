<?php
/**
 * Created by PhpStorm.
 * User: yangpan
 * Date: 2018/4/17
 * Time: 16:56
 */
namespace admin;
require_once 'ini.php';
switch ($_POST['act']){
    case 'getErpProduct':
        $product_id = $_POST['product_id'];
        $erp_number = $_POST['number'];
        $users      = new \admin\helper\oa_users($register);
        $loginid    = $users->getUsernameByNameCn(trim($_POST['ad_member']));//使用优化师的loginid
        $params     = ['company_id'=>$_SESSION['admin']['company_id'],'loginid'=>$loginid,'id'=>$erp_number];
        $objname    = 'admin\helper\api\erpproduct';
        $obj = new $objname();
        $ret = $obj->getProduct($params);
        $product_att =  $ret['product']['product_attr'];
        //获取属性组1
        $attr_grop1 = array_column($product_att,'title','id');

        //获取属性组2
        $product    = new \admin\helper\product($register);
        $att = $product->getProduct_attr($product_id);
        $attr_grop2 = array_column($att,'attr_group_title','attr_group_id');


        //进行比较返回差集
        $attr_grop = array_diff($attr_grop1,$attr_grop2);

        if (!$ret['status'] || !$ret['product']) {
            $msg = empty($ret['message'])?'':$ret['message'];
            echo json_encode(['ret' => 0, 'msg' => "没找到该产品信息:".$msg]);
            exit;
        }
        echo json_encode(['ret' => 1, 'data' => $ret['product']['product_attr']]);
        break;
}

