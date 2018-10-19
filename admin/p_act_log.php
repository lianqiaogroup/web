<?php

require_once 'ini.php';
if(!empty($_SESSION['admin']) && (($_SESSION['admin']['is_admin'] == 1 ) || ($_SESSION['admin']['is_root'] == 1 ))){
	if($_GET['act'] && $_GET['act'] == 'query') {
		// $filter = empty($_GET['filter'])?[]:$_GET['filter'];
		$filter = [];
		// $filter['AND'] = [];
		$t = time();
		$start_time = 0;
		if(!empty($_GET['start_time'])){
			$start_time = strtotime($_GET['start_time']);
			$filter['product_act_logs.act_time[>=]'] = $start_time;	
		}
		if(!empty($_GET['end_time'])){
			$end_time = strtotime($_GET['end_time']);
			if($start_time >= $end_time){
				echo json_encode(['res'=>0,'msg'=>'时间参数错误']);exit();
			}
			$end_time = ($end_time>$t)?$t:$end_time;
			$filter['product_act_logs.act_time[<=]'] = $end_time;
		}
		if(!empty($_GET['product_id'])){
			$filter['product_act_logs.product_id'] = $_GET['product_id'];
		}
		if(!empty($_GET['act_loginid'])){
			$filter['product_act_logs.act_loginid'] = $_GET['act_loginid'];
		}
		$filter['ORDER'] = ['product_act_logs.act_id'=>'DESC'];
		$pActLog = new \admin\helper\pactlog($register);
		//设置分页
        $pageSize = 25;
        $page = isset($_GET['p']) ? (int)$_GET['p']:1;
        $page = ($page<=0)?1:$page;
        $start =   ($page -1)* $pageSize;
        $map = [];
        $map['LIMIT'] = [$start,$pageSize];
        $filters = array_merge($filter,$map);
		$_data = $pActLog->find($filters);
		if(!is_array($_data) || (count($_data)<1) ){
			echo json_encode(['res'=>0,'goodsList'=>[]]);exit();
		}
		$data = [];
		$data['ret'] = 1;
		$data['goodsList'] = $_data;
		$count = $db->count("product_act_logs",'act_id',$filter);
        $data['p'] = $page;
        $data['total_p'] = ceil ( $count / $pageSize );
        $data['count']  = $count;
        $data['pageHtml'] = $db->Pagebarht(['p'=>$page],$pageSize,$page,$count);
		echo json_encode($data);exit();
	}else{
		echo json_encode(['res'=>0,'msg'=>'参数错误']);exit();	
	}
}else{
	echo json_encode(['res'=>0,'msg'=>'你没有权限进行此操作']);exit();
}
