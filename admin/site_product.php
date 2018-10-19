<?php

/**
 * chenhk 20170622 15:40
 */

require_once 'ini.php'; //加载配置

//类型判断
$actGET  = isset($_GET['act'])?$_GET['act']:null;
$actPOST = isset($_POST['act'])?$_POST['act']:null;

//初始化注册类
$siteProduct = new \admin\helper\site_product($register);

if ($actGET && $actGET == 'edit') {
    //编辑获取产品信息
    $sid     = $_GET['sid'];
    $data = [];
    if ($sid) {
        $data = $siteProduct->getOneProduct($sid);
        if (!$data) {
            echo json_encode(['ret'=>0, 'msg'=>'不存在该产品', 'data'=>[]]);
            exit;
        }
        echo json_encode(['ret'=>1, 'msg'=>'成功', 'data'=>$data]);
    }else {
        echo json_encode(['ret'=>0, 'msg'=>'获取产品信息失败', 'data'=>[]]);
    }
}
else if ($actPOST && $actPOST == 'save') {
    //编辑更新产品
    $dataPost = $_POST;

    $sid        = $dataPost['sid'];
    $domain     = $dataPost['domain'];
    if(substr($domain, 0,4) != 'www.'){
        $domain = 'www.'.$domain;
    }
    $product_id = $dataPost['product_id'];
    $sort       = $dataPost['sort'];
    $is_del     = $dataPost['is_del'];
    $aid        = $_SESSION['admin']['uid'];
    $thumb      = \admin\helper\qiniu::changImgDomain($dataPost['thumb']);
    $add_time   = date('Y-m-d H:i:s', time());

    //封装保存数据
    $data['domain']     = $domain;
    $data['product_id'] = $product_id;
    $data['sort']       = $sort;
    $data['is_del']     = $is_del;
    $data['thumb']      = $thumb;
    $data['add_time']   = $add_time;

    //判断是否存在该产品(不管是否删除)
    if ($sid) {
        $title = $siteProduct->existsProduct($sid);
        if (!$title) {
            echo json_encode(['ret'=>0, 'msg'=>'产品重复提交', 'data'=>[]]);
        }
    }

    //判断是否设置了主页
    $site = new \admin\helper\site($register);
    $filter['domain'] = $domain;
    $siteRes = $site->getSearchSite($filter);
    if (empty($siteRes)) {
        echo json_encode(['ret'=>0, 'msg'=>'未设置主页 请先设置主页后再添加产品', 'date'=>[]]);
        exit;
    }

    //判断保存或者更新是否成功
    $lastID = $siteProduct->saveSiteProduct($sid, $data);
    if ($lastID) {
        echo json_encode(['ret'=>1, 'msg'=>'成功', 'data'=>$data]);
    } else {
        echo json_encode(['ret'=>0, 'msg'=>'更新失败(可能存在该产品)', 'data'=>[]]);
    }
}
else if($actGET && $actGET == 'delete') {
    //get删除产品
    $sid            = $_GET['sid'];
    $data['is_del'] = $_GET['is_del'];
    $ret            = $siteProduct->deleteSiteProduct($sid, $data);
    echo json_encode($ret);
}
else {
    //查询产品列表
    $domain = isset($_GET['domain']) ? trim($_GET['domain']) : null;
    $data = [];
    if (!empty($domain)) {
         // 检查域名权限
        $company = new \admin\helper\company($register);
        $id_departments = $company->get_id_departments();
        $site = new \admin\helper\site($register);
        $domainPrivileges = $site->isDoaminPrivate($domain,$id_departments);
        
        if(!$domainPrivileges['code']){
            echo json_encode([]);
            exit;
        }
        $data    = $siteProduct->getAllSiteProduct($domain, $_GET['p']);
    }
    $data['ret'] = 1;
    $data['msg'] = '成功';
    echo json_encode($data);
}
 