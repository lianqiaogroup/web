<?php

require_once 'ini.php';

if($_GET['act'] && $_GET['act'] == 'list') {
	$data = $filter = [];
	$data['admin'] = $_SESSION['admin'];
	$c = new \admin\helper\company($register);
	$filter = [];
	// if(isset($_GET['company_id'])){
	// 	$filter['company_id'] = $_GET['company_id'];
	// }
	// if(!empty($_GET['name'])){
	// 	$filter['name'] = $_GET['name'];
	// }
	$data['list'] = $c->getCompanylist($filter);
	$a = new \admin\helper\api($register);
	$data['erpList'] = $a->getApiErpList();
	if(isset($_GET['page'])){
		$register->get('view')->display('/company/list.twig', $data);
	}else{
		echo json_encode($data);
	}
	// $register->get('view')->display('/sms/isp_list.twig', $data);
}
elseif ($_GET['act'] && $_GET['act'] == 'edit') {
	$data['admin'] = $_SESSION['admin'];
	$data = [];
	if(!empty($_GET['company_id']) ){
		$c = new \admin\helper\company($register);
		$data = $c->getCompany($_GET['company_id']);
	}
	$a = new \admin\helper\api($register);
	$erpList = $a->getApiErpList();
	$data['product_erp'] = $data['domain_erp'] = $data['order_erp'] = $data['seo_erp'] = [];
	foreach ($erpList as $k => $v) {
		if($v['tag'] == 'product_erp'){
			$data['product_erp'][] = $v;
		}
		if($v['tag'] == 'domain_erp'){
			$data['domain_erp'][] = $v;
		}
		if($v['tag'] == 'order_erp'){
			$data['order_erp'][] = $v;
		}
		if($v['tag'] == 'seo_erp'){
			$data['seo_erp'][] = $v;
		}
	}
    //$_SESSION['token'] = $data['token']     = md5(uniqid(rand(), true));
    // echo json_encode($data);
	$register->get('view')->display('/company/edit.twig', $data);
}
elseif ($_GET['act'] && $_GET['act'] == 'save') {
	$data = [];
	$c = new \admin\helper\company($register);
	// if(empty($_POST['name']) || empty($_POST['classname']) || empty($_POST['token'])){
	// 	echo json_encode(['ret'=>0,'msg'=>'params lost']);exit();
	// }
	if(empty($_POST['name']) || !isset($_POST['product_erp_api']) || !isset($_POST['order_erp_api']) ){
		echo json_encode(['ret'=>0,'msg'=>'params lost']);exit();
	}
	$res =  $c->saveCompany($_POST);
	echo json_encode($res);
}
elseif ($_GET['act'] && $_GET['act'] == 'delete') {
	$data = [];
	$c = new \admin\helper\company($register);
	if(empty($_POST['company_id'])){
		echo json_encode(['ret'=>0,'msg'=>'params lost']);exit();
	}
	$res =  $c->deleteCompany($_POST['company_id']);
	echo json_encode($res);
}
elseif ($_GET['act'] && $_GET['act'] == 'getDepartmentUsers') {
	$data = [];
	$c = new \admin\helper\company($register);
	if(empty($_REQUEST['id_department'])){
		echo json_encode(['ret'=>0,'msg'=>'params lost']);exit();
	}
	$res =  $c->getDepartmentUsers($_REQUEST['id_department']);
	echo json_encode(['ret'=>1,'users'=>$res]);exit();
}
