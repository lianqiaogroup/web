<?php
require_once 'ini.php';
if($_SESSION['admin']['uid']){
    ajaxReturn($_SESSION);
}
require_once 'build/login.html';

//if( !$_SESSION['admin']['uid'] ){
//    require_once 'build/login.html';
//}else{
//    $data =  $_SESSION;
//    if(!$_SESSION['admin']['is_admin'])
//    {
//        $map['aid'] = $_SESSION['admin']['uid'];
//    }
//    echo json_encode($data);
//}


