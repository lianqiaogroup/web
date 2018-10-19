<?php
/**
 * Created by PhpStorm.
 * User: jade
 * Date: 2017/8/22
 * Time: 上午9:56
 */


include_once 'base.php';

header('Content-type: application/json');
if(!empty($_GET['act']) && ($_GET['act'] == 'deleteProduct') ){
	if(empty($_REQUEST['erp_id']) || empty($_REQUEST['loginid'])  || empty($_REQUEST['timestamp']) || empty($_REQUEST['sign']) || empty($_REQUEST['oldid_department'])){
		echo json_encode(['res'=>'fail','data'=>['msg'=>'参数缺失']]);exit();
	}
	$data = $_REQUEST;
	unset($_REQUEST['act']);
	$p = new \admin\api\product();
	$r = $p->deleteProduct($data);
	echo json_encode($r);exit();
}
else if(!empty($_GET['act']) && ($_GET['act'] == 'getProductByPath') ){
	if(empty($_REQUEST['path_list']) || empty($_REQUEST['timestamp']) || empty($_REQUEST['sign'])){
		echo json_encode(['res'=>'fail','data'=>['msg'=>'参数缺失']]);exit();
	}
	$data = $_REQUEST;
	unset($_REQUEST['act']);
	$p = new \admin\api\product();
	$r = $p->getProductByPath($data);
	echo json_encode($r);exit();
}
// else if(!empty($_GET['act']) && ($_GET['act'] == 'recoveryProduct') ){
// 	if(empty($_REQUEST['erp_id']) || empty($_REQUEST['timestamp']) || empty($_REQUEST['sign'])  || empty($_REQUEST['oldid_department'])){
// 		echo json_encode(['res'=>'fail','data'=>['msg'=>'参数缺失']]);exit();
// 	}
// 	$data = $_REQUEST;
// 	unset($_REQUEST['act']);
// 	$p = new \admin\api\product();
// 	$r = $p->recoveryProduct($data);
// 	echo json_encode($r);exit();
// }
