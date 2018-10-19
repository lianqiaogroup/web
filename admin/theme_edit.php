<?php
require_once 'ini.php';

if($_GET['act'] && $_GET['act']=="saveModule"){
    $post = $_POST;
    $model = new \admin\helper\themeDiy($register);
    $ret = $model->save($post);
    echo json_encode($ret);

}elseif ($_GET['act'] && $_GET['act']=="saveSort"){
    $post = $_POST;
    $model = new \admin\helper\themeDiy($register);
    $ret = $model->saveSort($post);
    echo json_encode($ret);

}elseif($_GET['act'] && $_GET['act']=="loadModule"){
    $product_id = $_POST['product_id'];
    $module_id = $_POST['module_id'];
    $model = new \admin\helper\themeDiy($register);
    $ret = $model->getModule($product_id,$module_id);
    echo json_encode($ret);
}elseif($_GET['act'] && $_GET['act']=="reset"){
    if( isset($_POST['product_id']) && $_POST['product_id'] !="" ){
        $product_id = $_POST['product_id'];
        $model = new \admin\helper\themeDiy($register);
        $ret = $model->reset($product_id);
        echo json_encode($ret);
    }else{
        $ret['ret'] = 0;
        echo json_encode($ret);
    }
}else{
    $data = [];
    //找到产品
    $product_id =$_GET['product_id'];
    $model = new \admin\helper\product($register);
    $data = $model->getOneProduct($product_id);

    $data['admin']  = $_SESSION['admin'];
    $user = $register->get('db')->select('user',['uid','username']);
    $data['user'] = $user;
    if( isset($_GET['json']) && $_GET['json']=='1' ){
        echo json_encode($data);
        die();
    }
    $register->get("view")->display('theme_edit.twig',$data);
}