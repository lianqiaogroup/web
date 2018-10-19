<?php

define('app_path',dirname(dirname(__FILE__)).'/');
require_once '../vendor/autoload.php';
require_once './helper/function.php';
// 引入鉴权类
use admin\helper\qiniu;
new \lib\config();
$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
$action   = isset($_GET['action']) ? $_GET['action'] : '';
if ($action === 'config') { //百度编辑器配置
    $result = preg_replace("/\/\*[\s\S]+?\*\//", "",
        file_get_contents(__DIR__ .'/template/plugins/ue/php/config.json'));
    echo $result;
    exit;
}

    $type   = isset($_GET['type']) ? $_GET['type'] : '';
    $txt = isset($_POST['txt'])? $_POST['txt']:'';
    if (empty($_FILES) || !isset($_FILES['upfile']) || $_FILES['upfile']['error'] !== \UPLOAD_ERR_OK ) {
        echo json_encode(['state'=>'FAIL']);
        exit;
    }

    /**
     * 判断文件类型是否为png、jpg格式
     */
    if ($txt && strpos($_FILES['upfile']['type'], 'image/') !== false && strpos($_FILES['upfile']['type'], 'gif') === false)
    {
        //如果上传水印文字text则加水印 jimmy
        $upload = new admin\helper\upload();
        $file = $upload->uploadImg();
        $original_name ='';
        //图片压缩类(只对原图压缩)
        if (!empty($file['fileName'])) {
            $imageCop = new \admin\helper\ImageCompression();
            if ($type == 'logo' || $type == 'thumb' || $type == 'photos' || $type == 'cover') {
                $ret = $imageCop->geoCompression(app_path.$file['fileName'], 800, 800, 1);
            } elseif ($action == 'uploadimage') {
                $ret = $imageCop->geoCompression(app_path.$file['fileName'], 790, 100000, 1);
            }
        }

        if(!$file || $file['state'] =='FAIL')
        {
            echo json_encode($file);
            exit;
        }

        //加水印
        $files = $upload->imgTxt($file['fileName'],$txt);
        if (!$files) {
            echo json_encode(['state'=>'FAIL']);
            exit;
        }
        //jimmy 如果是缩略图或者属性图，则上传原图一份到7牛
        if($type=='thumb' || $type =='attr')
        {
            $upFileRoute =  substr(app_path, 0, -1) . $file['fileName'];
            //上传7牛
            $ret = qiniu::uploadImgTxt($type,$upFileRoute,false);
            if($ret['state'] =='FAIL')
            {
                echo json_encode(['state'=>'FAIL']);
                exit;
            }
            $original_name = $ret['url'];
        }

        //上传加完水印的图片到7牛
        $result = qiniu::uploadImgTxt($type,$files);
        //原图片本地相对地址
        $original_name = !empty($original_name)? $original_name.','.$file['fileName']:$file['fileName'];
        $result['original_name'] = $original_name;
    } else {
        if ($type == "video") {
            // 视频大小限制
            $file     = $_FILES['upfile'];
            $limit = 10; // 单位:M
            // 视频格式限制
            $bool = checkSupportVideoFormat($_FILES['upfile']['type']);
            if($file['size'] > $limit *1024*1024){ 
                $result['msg'] = "上传视频超过" . $limit . "M";
                $result['original_name'] ='';
            } elseif (!$bool) {
                $result['msg'] = "不支持的视频格式";
                $result['original_name'] ='';
            } else {
                $result = qiniu::upload($type);
                $result['original_name'] ='';
            }
        } else {
            $result = qiniu::upload($type);
            $result['original_name'] ='';
        }
    }


$result = json_encode($result);
/* 输出结果 */
if ($callback) {
    if (preg_match("/^[\w_]+$/", $callback)) {
        echo htmlspecialchars($callback) . '(' . $result . ')';
    } else {
        echo json_encode(array(
            'state' => 'callback参数不合法'
        ));
    }
}
else {
    echo $result;
}