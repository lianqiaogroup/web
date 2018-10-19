<?php

require_once 'ini.php';

if($_GET['act'] && $_GET['act'] == 'isp_list') {
	$data = $filter = [];
	$data['admin'] = $_SESSION['admin'];
	$sms = new \admin\helper\sms($register);
	if(isset($_GET['status'])){
		$filter['status'] = $_GET['status'];
	}
	if(!empty($_GET['ispname'])){
		$filter['ispname'] = $_GET['ispname'];
	}
	$data['list'] = $sms->getAllIsp($filter);
	if(isset($_GET['page'])){
		$register->get('view')->display('/sms/isp_list.twig', $data);
	}else{
		echo json_encode($data);exit();
	}
	// $register->get('view')->display('/sms/isp_list.twig', $data);
}
elseif ($_GET['act'] && $_GET['act'] == 'isp_edit') {
	$data = [];
	$data['admin'] = $_SESSION['admin'];
	if(!empty($_GET['id']) ){
		$sms       = new \admin\helper\sms($register);
		$data['originInfo'] = $sms->getIsp($_GET['id']);
	}
    $_SESSION['token'] = $data['token']     = md5(uniqid(rand(), true));
    // print_r($data);exit;
	$register->get('view')->display('/sms/isp_edit.twig', $data);
}
elseif ($_GET['act'] && $_GET['act'] == 'save_isp') {
	$data = [];
	$sms       = new \admin\helper\sms($register);
	if(empty($_POST['ispname']) || !isset($_POST['status']) ){
		echo json_encode(['ret'=>0,'msg'=>'params lost']);exit();
	}
	// var_dump($_POST);exit();
	$res =  $sms->saveIsp($_POST);
	echo json_encode($res);
}
elseif ($_GET['act'] && $_GET['act'] == 'isp_rel') {
	$data = [];
	$data['admin'] = $_SESSION['admin'];
	$sms       = new \admin\helper\sms($register);
	if(empty($_GET['id']) ){
		echo json_encode(['ret'=>0,'msg'=>'params lost']);exit();
	}
	$data['ispname'] = $sms->getIspName($_GET['id']);
	$data['list'] = $sms->getIspRel($_GET['id']);
	echo json_encode($data);
	//$register->get('view')->display('/sms/isp_states.twig', $data);
}
elseif($_GET['act'] && $_GET['act'] == 'checkIsp') {
	$zone = $_GET['zone'];
	$data['admin'] = $_SESSION['admin'];
	$sms = new \admin\helper\sms($register);
	$data = $sms->checkIspByZone($zone);

	// 潜规则: 按地区是否强制开启短信(开放地区：台湾、泰国、柬埔寨、巴基斯坦、菲律宾、印尼)
    $data['is_open'] =0;
    $D = new \admin\helper\country($register);
    $countryData =  $D->getOne($zone);
    if (($countryData['is_force_open_sms'] == 'enable') && ($_SESSION['admin']['company_id'] ==1)) {
        $data['is_open'] =1;
    }
	echo json_encode($data);exit;
}
elseif($_GET['act'] && $_GET['act'] == 'isp_state_list') {
	$data = $filter = [];
	$data['admin'] = $_SESSION['admin'];
	$sms = new \admin\helper\sms($register);
	if(!empty($_GET['ispname'])){
		$filter['ispname'] = $_GET['ispname'];
	}
	if(isset($_GET['nation'])){
		$filter['nation'] = $_GET['nation'];
	}
	if(!empty($_GET['styles'])){
		$filter['styles'] = $_GET['styles'];
	}
	if(!empty($_GET['ncode'])){
		$filter['ncode'] = $_GET['ncode'];
	}
	if(!empty($_GET['prefix'])){
		$filter['prefix'] = $_GET['prefix'];
	}
	$data['list'] = $sms->getAllIspState($filter);
	if(isset($_GET['page'])){
		$register->get('view')->display('/sms/isp_state_list.twig', $data);
	}else{
		echo json_encode($data);
	}
	// $register->get('view')->display('/sms/isp_state_list.twig', $data);
}
elseif ($_GET['act'] && $_GET['act'] == 'isp_state_edit') {
	$data = [];
	$data['admin'] = $_SESSION['admin'];
	$sms       = new \admin\helper\sms($register);
	if(!empty($_GET['id']) ){
		$data['originInfo'] = $sms->getIspState($_GET['id']);
		if ($data['originInfo']) {
			$data['originInfo']['title'] = "编辑(ID=" . $data['originInfo']['id'] . ")";
		} else {
			$data['originInfo']['title'] = "编辑";
		}
	}
	$data['ispids'] = $sms->getAllAvailIsp();
	$D                 = new \admin\helper\country($register);
    $id_zone           = $D->getAllZone();
    $data['id_zones']  = $id_zone;
    $_SESSION['token'] = $data['token']     = md5(uniqid(rand(), true));
    $region            = $db->select('region', '*', ['parent_id' => 0]);
    $data['region']    = $region;
    $page_styles = [];
    $data['templs']  = '';
    $data['theme'] = $data['originInfo']['styles'];
    // echo json_encode($data);
	$register->get('view')->display('/sms/isp_state_edit.twig', $data);
}
elseif ($_GET['act'] && $_GET['act'] == 'save_isp_state') {
	$data = [];
	$sms       = new \admin\helper\sms($register);
	if(empty($_POST['ispid']) || empty($_POST['nation']) || empty($_POST['prefix'])){
		echo json_encode(['ret'=>0,'msg'=>'params lost']);exit();
	}
	$res =  $sms->saveIspState($_POST);
	echo json_encode($res);
}
elseif ($_GET['act'] && $_GET['act'] == 'delete_isp_state') {
	$data = [];
	$sms       = new \admin\helper\sms($register);
	if(empty($_POST['id']) ){
		echo json_encode(['ret'=>0,'msg'=>'params lost']);exit();
	}
	$res = $sms->deleteIspStateById($_POST['id']);
	if($res){
		echo json_encode(['ret'=>1]);exit();
	}
	echo json_encode(['ret'=>0,'msg'=>'delete fail']);exit();
}
elseif ($_GET['act'] && $_GET['act'] == 'delete_isp') {
	$data = [];
	$sms       = new \admin\helper\sms($register);
	if(empty($_POST['id']) ){
		echo json_encode(['ret'=>0,'msg'=>'params lost']);exit();
	}
	$res = $sms->deleteIspById($_POST['id']);
	echo json_encode($res);exit();
}




#$register->get('view')->display('/sms/isp_relate.twig', $data);