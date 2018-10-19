<?php

require_once 'ini.php';

if ($_GET['act'] && $_GET['act'] == 'edit') {
    $product = new \admin\helper\index_focus($register);
    $mid     = $_GET['id'];
    $data    = [];
    if ($mid) {
        $data = $product->getOneModule($mid);
        if (!$data) {
            exit("没有找到该信息");
        }
    }

    $data['admin']  = $_SESSION['admin'];
    $data['option'] = $module;
    $register->get('view')->display('index_focus.twig', $data);
}
elseif ($_GET['act'] && $_GET['act'] == 'save') {
    $model = new \admin\helper\index_focus($register);
    $error = [];
    $id    = $_POST['id'];
    $data  = I("post.");
    
    if (!$_POST['product_id']) {
        array_push($error, ['title' => '请选择产品']);
    }
    if (empty($data['thumb'])) {
        array_push($error, ['title' => '请上传图片']);
    }
    $thumb = \admin\helper\qiniu::changImgDomain($data['thumb'] ) ;
    $thumb = str_replace('shop80','shop',$thumb); //前台取的是缩略图，把80后缀去掉 又给你们挖了个坑
    $data['thumb'] = $thumb;
    if(substr($data['domain'], 0,4) != 'www.'){
        $data['domain'] = 'www.'.$data['domain'];
    }
    if (!empty($error)) {
        $data['admin'] = $_SESSION['admin'];
        $data['error'] = $error;
        if($_GET['json']){
            $data['ret'] = 0;
            $data['msg'] = $error[0]['title'];
            echo json_encode($data);
            exit;
        }
        $register->get('view')->display('index_focus.twig', $data);
        exit;
    }
    if (!$id) {
        $data['add_time'] = date('Y-m-d H:i:s', time());
    }
    $data['aid'] = $_SESSION['admin']['uid'];
    $ret         = $model->saveModule($id, $data);

    if (is_array($ret) && !$ret['ret']) {
        array_push($error, ['title' => $ret['msg']]);
        $data['error'] = $error;
        $data['admin'] = $_SESSION['admin'];
        if($_GET['json']){
            $data['ret'] = 0;
            $data['msg'] = $error[0]['title'];
            echo json_encode($data);
            exit;
        }
        $register->get('view')->display('index_focus.twig', $data);
        exit;
    }
    if($_GET['json']){
            $data['ret'] = 1;
            $data['msg'] = '设置成功';
            echo json_encode($data);
            exit;
        }
    header("location:index_focus.php?domain=".$data['domain']);
}
elseif ($_POST['act'] && $_POST['act'] == 'delete') {
    $mid            = $_POST['mid'];
    $data['is_del'] = $_POST['is_del'];
    $product        = new \admin\helper\index_focus($register);
    $ret            = $product->deleteModule($mid, $data);

    echo json_encode($ret);
}
elseif ($_GET['act'] && $_GET['act'] == 'delete') {
    $mid            = $_GET['mid'];
    $data['is_del'] = $_GET['is_del'];
    $product        = new \admin\helper\index_focus($register);
    $ret            = $product->deleteModule($mid, $data);

    echo json_encode($ret);
}
else {
    $domain = $_GET['domain'];
    $data   = $_SESSION;
    $product = new \admin\helper\index_focus($register);
    
    // 检查域名权限
    $company = new \admin\helper\company($register);
    $id_departments = $company->get_id_departments();
    $site = new \admin\helper\site($register);
    $domainPrivileges = $site->isDoaminPrivate($domain,$id_departments);
    
    if(!$domainPrivileges['code']){
        echo json_encode([]);
        exit;
    }
    if (!empty($domain)) $data    = $product->getAllModule($domain);
    foreach ($data['goodsList'] as $k=>$v){
        $data['goodsList'][$k]['thumb'] = \admin\helper\qiniu::get_image_path($v['thumb']);
    }
    $data['domain'] = $domain ;
    if( isset($_GET['json']) && $_GET['json']=='1' ){
        echo json_encode($data);
    }else{
        $register->get('view')->display('index_focus_list.twig', $data);
    }
}