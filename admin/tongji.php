<?php
require_once 'ini.php';
$model = new admin\helper\tongji($register);


$act=$_GET['act'];
$v_date=$_GET['v_date'];
$v_day_num=$_GET['v_day_num'];
$v_time_type=$_GET['v_time_type'];
$search=htmlentities($_GET['search']);


//查询地区的访问次数
if($_GET['act'] && $_GET['act']=='zone')
{
   $data = $model->getZoneData($v_date,$v_day_num,$v_time_type);
    echo json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
}
elseif($_GET['act'] && $_GET['act']=='device')
{
    $data  = $model->getDevice($v_date,$v_day_num,$v_time_type);
    echo json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
}

//mike
if($_GET['act']&&$_GET['act']=='host'){
	$data=$model->getAllHost($v_date,$v_day_num,$v_time_type,$search);
	echo json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
}
if($_GET['act']&&$_GET['act']=='product'){
	$data=$model->getAllProduct($v_date,$v_day_num,$v_time_type,$search);
	echo json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
}
if($_GET['act'] && $_GET['act']=='leftBox'){
    $visit = $model -> getUsersVisited($v_date, $v_day_num, $v_time_type);
    $order = $model -> getOrderQuantity($v_date, $v_day_num, $v_time_type);
    $avg   = $model -> averageStayTime($v_date, $v_day_num, $v_time_type);
    $usertype  = $model -> getUsersVisitedGroupTimeType($v_date, $v_day_num, $v_time_type);
    $ordertype = $model -> getOrderQuantityGroupTimeType($v_date, $v_day_num, $v_time_type);
    $data = array( visit => $visit, order => $order, average => $avg, usertype => $usertype, ordertype => $ordertype );
    echo json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);   
}

