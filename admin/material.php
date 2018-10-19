<?php

require_once './ini.php';

use admin\helper\Material;

$act = isset($_GET['act'])?$_GET['act']:"";

$material = new Material();
switch ($act){
    case "getconfig":
        $response =  Material::getConfig();
        if($response){
            echo json_encode(['ret'=>1, 'data'=>$response]);
        }else{
            echo json_encode(['ret'=>0, 'data'=>[]]);
        }
        break;
    case 'tags':
        $response = $material ->fetchTag();
        if($response){
            echo json_encode(['ret'=>1, 'data'=>$response]);
        }else{
            echo json_encode(['ret'=>0, 'data'=>[]]);
        }
        break;
    case 'lists':
        $request = json_decode(file_get_contents("php://input"));

        $filter = [];
        $p = isset($request->p) ? intval($request->p) : 1;
        $pageSize = isset($request->pagesize) ? intval($request->pagesize) : 15;

        if (isset($request->mtype) && $request->mtype!='') {
            $filter['mtype'] = explode(',', $request->mtype);
        }

        if (isset($request->tag)) {
            $filter['mtag'] = intval($request->tag);
        }

        if (isset($request->format)) {
            $upperCode = strtoupper($request->format);
            switch ($upperCode) {
                case 'IMAGE':
                    $filter['format[!]'][] = $material->convertFileType('GIF');
                    $filter['format[!]'][] = $material->convertFileType('MP4');
                    break;
                default:
                    $filter['format'] = $material->convertFileType($upperCode);
                    break;
            }
        }

        $response = $material->getlist($filter, $p, $pageSize);
        if ($response) {
            echo json_encode(['ret'=>1, 'data'=>$response]);
        } else {
            echo json_encode(['ret'=>0, 'data'=>[]]);
        }
        break;
    case 'add':
        $response = file_get_contents('php://input');
        $response = json_decode($response,true);
        if(!isset($response['mtype'])){
            echo json_encode(['ret'=>1,'data'=>[],'ret_message'=>'素材类别不能为空']);
        }
        else if(!isset($response['tag'])){
            echo json_encode(['ret'=>1,'data'=>[],'ret_message'=>'素材标签不能为空']);
        }
        else if(!isset($response['datas'])){
            echo json_encode(['ret'=>1,'data'=>[],'ret_message'=>'素材内容不能为空']);
        }else{
            $mtype = intval($response['mtype']);
            $mtag = intval($response['tag']);
            $datas = $response['datas'];
            $response = $material->insert($mtype,$mtag,$datas);

            if($response){
                echo json_encode(['ret'=>1,'data'=>[],'ret_message'=>'新增成功!']);
            }else{
                echo json_encode(['ret'=>0,'data'=>[],'ret_message'=>'新增失败!']);
            }
        }

        break;
    case 'type':
        try{
            $domian = \lib\register::getInstance('config')->get('apiUrl.erp');
            $url = $domian. '/product/base/category/tree';
            $response = sendGet($url, getHeaders());
            if ($response['status'] == 1) {
                $er_reponse_json = json_decode($response['message']);
                if ($er_reponse_json==null) {
                    echo json_encode(['ret'=>0, 'msg'=>'ERP系统返回空的分类数据。']);
                } else {
                    echo json_encode(['ret'=>1, 'data'=>$er_reponse_json]);
                }
            } else {
                echo json_encode(['ret'=>0, 'data'=>[]]);
            }
        }catch (\Exception $e){
            return [ 'code' => 400, 'msg' => $e->getMessage() ];
        }
        break;
    default:
        echo json_encode(['ret'=>0,'data'=>[]]);
        break;
}

function getHeaders()
{
    $token  = \lib\register::getInstance('config')->get('erpToken');
    $timestamp = getMillisecond();
    $nonce = getRandomStr(8);
    $sign  = \md5($token.$timestamp.$nonce);
    $headers = [
        "X-PROJECT-ID:frontend.website.build",
        "Accept:application/json,text/plain,*/*",
        "x-requested-with:XMLHttpRequest",
        "X-AUTH-TIMESTAMP:$timestamp",
        "X-AUTH-NONCE:$nonce",
        "X-AUTH-SIGNATURE:$sign",
    ];
    return $headers;
}

function getMillisecond(){
    list($s1, $s2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
}

function getRandomStr( $length = 8 ) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $str ='';
    for ( $i = 0; $i < $length; $i++ )
    {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

function sendGet($url,$headers =[]){
    $curl = curl_init();
    curl_setopt($curl,CURLOPT_URL,$url);
    if($headers){
        curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
    }
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, False);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    $result = curl_exec($curl);
    $retdata['status'] = 1;

    if($error = curl_error($curl)){
        $retdata['status'] = 0;
        $retdata['message'] = $error;
    }else{
        $retdata['message'] = $result;
    }
    curl_close($curl);
    return $retdata;
}
