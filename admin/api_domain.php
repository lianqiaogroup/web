<?php
/**
 * Created by PhpStorm.
 * User: jade
 * Date: 2017/8/22
 * Time: 上午9:56
 */
exit('域名下有产品的不再做处理');
// define('app_path',dirname(dirname(__FILE__)).'/');
// define('environment','office');
// #define('environment','idc');//线上
// define('phpKey','8abf7b4dc529a5b9ef624731ee71e9c7');
// require_once app_path.'vendor/autoload.php';
// $config=require_once app_path.'config/base.php';
// // var_export(json_encode(['www.dzpas.com']));exit();
// header('Content-type: application/json');
// if(!empty($_GET['act']) && ($_GET['act'] == 'changeDepartment') ){
// 	if(empty($_REQUEST['domain']) || empty($_REQUEST['timestamp']) || empty($_REQUEST['sign']) || empty($_REQUEST['oldid_department'])){
// 		echo json_encode(['res'=>'fail','data'=>['msg'=>'参数缺失']]);exit();
// 	}
// 	$_REQUEST['domain'] = json_decode($_REQUEST['domain'],1);
// 	if(!is_array($_REQUEST['domain']) || (count($_REQUEST['domain'])<1) ){
// 		echo json_encode(['res'=>'fail','data'=>['msg'=>'域名参数格式错误']]);exit();
// 	}
// 	// $_REQUEST['domain'] = 'www.'.$_REQUEST['domain']; //务必保证 带有www前缀
// 	$data = $_REQUEST;
// 	unset($_REQUEST['act']);
// 	$p = new \admin\api\product();
// 	$r = $p->changeDepartment($data);
// 	echo json_encode($r);exit();
// }
