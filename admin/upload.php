<?php

require_once 'ini.php';
/*
 * 上传控制器
 * @params fieldName 表单file name值$_File[xx]
 * @params theme  主题
 * @params isCut  是否需要裁剪 80*80的缩略图
 * 
 */

$return = ['code' => 0, 'msg' => '上传失败'];
try {
    $fieldName = $_GET['fieldName'];
    $theme     = $_GET['theme'];
    if (!isset($_FILES[$fieldName])) {
        throw new Exception('该表单图片不存在');
    }
    $rootPath      = app_path;
    $path          = '/upload/' . $theme . '/thumb/' . date('y-m-d', time()) . '/';
    $uploadHandler = new \Sirius\Upload\Handler($rootPath . $path);
    $result        = $uploadHandler->process($_FILES[$fieldName]);
    if (!$result->isValid()) {
        $return['msg'] = $result->getMessages();
    }
    else {
        //判断是否需要裁剪
        $isCut = $_GET['isCut'];
        if ($isCut) {
            admin\helper\imageCut::cut($rootPath . $path, $result->name);
        }
        $return = ['code' => 1, 'msg' => '上传成功', 'imgUrl' => $path . $result->name];
        $result->confirm();
    }
}
catch (Exception $e) {
    $return['msg'] = $e->getMessage();
}
echo json_encode($return);
