<?php
require_once 'ini.php';

if ($_GET['act'] && $_GET['act'] == 'edit') {
    $product = new \admin\helper\oa_users($register);
    $uid = $_GET['uid'];
    $data = $product->getOneUser($uid);

    $data['error'] = $error;
    $data['admin'] = $_SESSION['admin'];
    $register->get('view')->display('/user/user.twig', $data);
} elseif ($_GET['act'] && $_GET['act'] == 'save') {
    $error = [];
    $uid = $_POST['uid'];
    if ($_POST['password']) {
        $_POST['password'] = strtoupper(md5($_POST['password']));
    } else {
        unset($_POST['password']);
    }
    if (!$uid) {
        $_POST['create_at'] = date('Y-m-d H:i:s', time());
        $_POST['company_id'] =$_SESSION['admin']['company_id'];
        if (!$_POST['password']) {
           echo json_encode(['ret'=>0,'msg'=>'密码不能为空']);
            exit;
        }
    }

    if (!$_POST['username']) {
            echo json_encode(['ret'=>0,'msg'=>'用户名不能为空']);
            exit;
    }
    if (!$_POST['name_cn']) {
        echo json_encode(['ret'=>0,'msg'=>'姓名不能为空']);
        exit;
    }
    
    $product = new \admin\helper\oa_users($register);
    $_POST['update_at'] = date('Y-m-d H:i:s', time());
    $ret = $product->saveUser($uid, $_POST);
    if (!$ret['ret']) {
        echo json_encode(['ret'=>0,'msg'=>$ret['msg']]);
        exit;
    }
    echo json_encode(['ret'=>1,'msg'=>'OK']);

} elseif ($_POST['act'] && $_POST['act'] == 'delete') {
    $uid = $_POST['uid'];
    $data['is_del'] = $_POST['is_del'];
    $product = new \admin\helper\oa_users($register);
    $ret = $product->deleteUser($uid, $data);

    echo json_encode($ret);

}elseif($_POST['act'] && $_POST['act'] == 'set_admin'){
    $uid = $_POST['uid'];
    $is_root = $_POST['is_root'];
    $user = new \admin\helper\oa_users($register);
    $ret =  $user->set_admin($uid,$is_root);
    echo json_encode($ret);
} else {
    $product = new \admin\helper\oa_users($register);
    $filter =[];
    if($_GET['is_root']){
        $filter['is_root'] = 1;
    }
    if($_GET['username']){
        $filter['name_cn'] = $_GET['username'];
    }

    $data = $product->getAlluser($filter);
   // $data['admin'] = $_SESSION['admin'];
    if(isset($_GET['json']) && $_GET['json']=='1'){
        echo json_encode($data);
    }else{
        $register->get('view')->display('/user/user_list.twig', $data);
    }
}