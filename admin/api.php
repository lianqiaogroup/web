<?php

 require_once 'ini.php';

if($_GET['act'] && $_GET['act'] == 'erp_list') {
	$data = $filter = [];
	$data['admin'] = $_SESSION['admin'];
	$a = new \admin\helper\api($register);
	$filter = [];
	// if(isset($_GET['company_id'])){
	// 	$filter['company_id'] = $_GET['company_id'];
	// }
	// if(!empty($_GET['name'])){
	// 	$filter['name'] = $_GET['name'];
	// }
	$data['list'] = $a->getApiErplist($filter);
	if(isset($_GET['page'])){
		$register->get('view')->display('/api/erplist.twig', $data);
	}else{
		echo json_encode($data);
	}
}
elseif ($_GET['act'] && $_GET['act'] == 'erp_edit') {
	$data['admin'] = $_SESSION['admin'];
	$data = [];
	if(!empty($_GET['id']) ){
		$a = new \admin\helper\api($register);
		$data = $a->getApiErp($_GET['id']);
	}
	// var_dump($data);exit();
    //$_SESSION['token'] = $data['token']     = md5(uniqid(rand(), true));
	$register->get('view')->display('/api/erp.twig', $data);
}
elseif ($_GET['act'] && $_GET['act'] == 'erp_save') {
	$data = [];
	$a = new \admin\helper\api($register);
	if(empty($_POST['name']) || empty($_POST['tag']) || empty($_POST['domain']) || empty($_POST['classname']) || empty($_POST['token'])){
		echo json_encode(['ret'=>0,'msg'=>'params lost']);exit();
	}
	$res =  $a->saveApiErp($_POST);
	echo json_encode($res);
}
elseif ($_GET['act'] && $_GET['act'] == 'erp_delete') {
	$data = [];
	$a = new \admin\helper\api($register);
	if(empty($_POST['id'])){
		echo json_encode(['ret'=>0,'msg'=>'params lost']);exit();
	}
	$res =  $a->deleteApiErp($_POST);
	echo json_encode($res);
}
