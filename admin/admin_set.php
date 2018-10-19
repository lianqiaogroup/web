<?php

require_once 'ini.php';

if(empty($_SESSION['admin']['is_admin']) && empty($_SESSION['admin']['is_root'])){
	echo json_encode(['res'=>'fail','data'=>['msg'=>'no right to do this']]);exit();
}

if($_GET['act'] && $_GET['act'] == 'admin_list') {
	$data = $filter = [];
	$users = new \admin\helper\oa_users($register);
	if(!empty($_GET['username'])){
		$filter['username[~]'] = $_GET['username'];
	}
	if(!empty($_GET['name_cn'])){
		$filter['name_cn[~]'] = $_GET['name_cn'];
	}
	if(!empty($_GET['department'])){
		$filter['department[~]'] = $_GET['department'];
	}
	if(isset($_GET['is_admin'])){
		$filter['is_admin'] = $_GET['is_admin'];
	}
	$data = $users->getAdminList($filter);
	echo json_encode($data);exit();
}
elseif ($_GET['act'] && $_GET['act'] == 'admin_save') {
	if(empty($_REQUEST['username']) || !isset($_REQUEST['is_admin'])){
		echo json_encode(['res'=>'fail','data'=>['msg'=>'params lost']]);exit();
	}
	$data = [];
	$data['username'] = $_REQUEST['username'];
	$data['is_admin'] = $_REQUEST['is_admin'];
	$users = new \admin\helper\oa_users($register);
	$r = $users->adminSave($data);
	echo json_encode($r);exit();
}
elseif ($_GET['act'] && $_GET['act'] == 'select_admin') {
	$data = $filter = [];
	$data['admin'] = $_SESSION['admin'];
	$users = new \admin\helper\oa_users($register);
	$data = $users->selectAdmin($filter);
	echo json_encode($data);exit();
}
elseif ($_GET['act'] && $_GET['act'] == 'admin_log') {
	$data = $filter = [];
	$t = time();
	$start_time = 0;
	if(!empty($_GET['start_time'])){
		$start_time = strtotime($_GET['start_time']);
		$filter['admin_logs.act_time[>=]'] = $start_time;
	}
	if(!empty($_GET['end_time'])){
		$end_time = strtotime($_GET['end_time']);
		if($start_time >= $end_time){
			echo json_encode(['res'=>0,'msg'=>'时间参数错误']);exit();
		}
		$end_time = ($end_time>$t)?$t:$end_time;
		$filter['admin_logs.act_time[<=]'] = $end_time;
	}
	if(!empty($_GET['loginid'])){
		$filter['admin_logs.loginid'] = $_GET['loginid'];
	}
	if(!empty($_GET['name_cn'])){
		$filter['admin_logs.name_cn'] = $_GET['name_cn'];
	}
	if(!empty($_GET['act_loginid'])){
		$filter['admin_logs.act_loginid'] = $_GET['act_loginid'];
	}
	$users = new \admin\helper\oa_users($register);
	//设置分页
    $pageSize = 25;
    $page = isset($_GET['p']) ? (int)$_GET['p']:1;
    $page = ($page<=0)?1:$page;
    $start =   ($page -1)* $pageSize;
    $map = [];
    $map['LIMIT'] = [$start,$pageSize];
    $filters = array_merge($filter,$map);
	$_data = $users->adminLog($filters);
	if(!is_array($_data) || (count($_data)<1) ){
		echo json_encode(['res'=>0,'goodsList'=>[]]);exit();
	}
	$data = [];
	$data['ret'] = 1;
	$data['goodsList'] = $_data;
	$count = $db->count("admin_logs",'id',$filter);
    $data['p'] = $page;
    $data['total_p'] = ceil ( $count / $pageSize );
    $data['count']  = $count;
    $data['pageHtml'] = $db->Pagebarht(['p'=>$page],$pageSize,$page,$count);
	echo json_encode($data);exit();
}




#$register->get('view')->display('/sms/isp_relate.twig', $data);
