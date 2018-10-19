<?php
require_once 'ini.php';
$id_zone = I("post.id_zone");
$lang =  I("post.lang");
$id_department =  I("post.id_department",0);
$code = $db->get('zone','code',['id_zone'=>$id_zone]);

if(!$code || !$lang)
{
    ajaxReturn([]);
}
$model = new \admin\helper\theme($register);
$data =  $model->getProductTheme($code,$lang,$id_department);

ajaxReturn($data);
