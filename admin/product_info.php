<?php
/**
 * Created by PhpStorm.
 * User: jimmy
 * Date: 2017/8/22
 * Time: 上午9:56
 */
require 'ini.php';
$erp_id = $_GET['erp_id'];
$timestamp = $_GET['timestamp'];
$apiToken= $_GET['apiToken'];

$token = md5($erp_id.$timestamp);
$status = 1;
if( $token != $apiToken)
{
     $status = 0;
     Json(['ret'=>$status,'msg'=>'验证失败']);
}

$api = new \admin\api\apiProduct();
$data = $api->getProductPhotos($erp_id);
Json($data);



function Json($array =[]){

    echo  json_encode($array);
    exit;

}
