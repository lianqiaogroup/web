<?php
require_once 'ini.php';


if( !$_SESSION['admin']['uid'] ){
    require_once 'build/login.html';
}else{
    $data =  $_SESSION;
    $arr = dayHost();
    echo json_encode($data);
}


