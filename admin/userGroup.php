<?php
require_once 'ini.php';

if($_GET['act'] && $_GET['act'] =='add'){
    $product = new \admin\helper\userGroup($register);
    $data['admin'] =  $_SESSION['admin'];
    $register->get('view')->display('/userGroup/add.twig',$data);
}elseif($_GET['act'] && $_GET['act'] =='edit') {
    $product = new \admin\helper\userGroup($register);
    $id =  I('get.id');
    if($id)
    {
        $data =  $product->getOne($id);
    }
    $data['admin'] =  $_SESSION['admin'];
    $register->get('view')->display('/userGroup/edit.twig',$data);
} elseif($_GET['act'] && $_GET['act'] =='save'){
    $id = I('post.id');
    $product = new \admin\helper\userGroup($register);
    if($id)
    {
        //update
        $ret = $product->saveUserGroup($_POST);
        echo json_encode($ret);

    }else{
        //add
        $title = I('post.title');
        $remark = I('post.remark');
        $check = [];
        foreach ($title as $key=>$row)
        {
            if($check[$row])
            {
                $ret =['ret'=>0,'msg'=>'名称不能重复'];
                break;
            }
            if(empty($row))
            {
                $ret =['ret'=>0,'msg'=>'名称不能为空'];
                break;
            }
            $check[$row] = $row ;
            $rows[$key]['title'] = $row;
            $rows[$key]['remark'] = $remark[$key];
        }
        if($ret)
        {

            echo json_encode($ret);
            exit;
        }
        $ret = $product->saveUserGroup($rows);
        echo json_encode($ret);

    }
}else
{
    $product = new \admin\helper\userGroup($register);
    $data =  $product->getAllUserGroup();
    $data['admin'] =  $_SESSION['admin'];
    if($_GET['json']){
        echo json_encode($data);
    }else{
        $register->get('view')->display('/userGroup/list.twig',$data);
    }
}