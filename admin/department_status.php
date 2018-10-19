<?php
require_once 'ini.php';

if(isset($_GET['act']) && $_GET['act'] == 'save'){

    $JSON  = $_POST['department_status'];
    if(!$JSON){

        $ret = ['ret'=>0,'msg'=>'配置项不能为空'];
        echo json_encode($ret);
        exit;
    }
    //检测是否是正确的json

    if(!is_json($JSON)){
        $ret = ['ret'=>0,'msg'=>'配置项有误，检查后重试'];
        echo json_encode($ret);
        exit;
    }

    //插入
    $data['department_status'] = $JSON;
    $data['add_time'] = date("Y-m-d H:i:s",time());
    $ret = $db->insert('department_status',$data);
    if(!$ret){
        $ret = ['ret'=>0,'msg'=>'保存失败咯'];
        echo json_encode($ret);
        exit;
    }
    $ret = ['ret'=>1,'msg'=>'保存配置成功','id'=>$ret];

    echo json_encode($ret);

}elseif(isset($_GET['id'])){
    $id  =   $_GET['id'];
    if($id =='last'){
        $id= $db->get('department_status','id',['ORDER'=>['id'=>"DESC"]]);
    }
    $data = $db->get('department_status','*',['id'=>$id]);
    if(!$data){
        exit('NOT FOUND');
    }

    $data['admin'] = $_SESSION['admin'];
    $data['department_status'] = jsonFormat($data['department_status']);
    $register->get('view')->display('/department_status/edit.twig', $data);
}else{

    $data['admin'] = $_SESSION['admin'];
    $register->get('view')->display('/department_status/edit.twig', $data);
}

function is_json($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

function jsonFormat($data, $indent=null){

    // 对数组中每个元素递归进行urlencode操作，保护中文字符
    //array_walk_recursive($data, 'jsonFormatProtect');

    // json encode
    //$data = json_encode($data);

    // 将urlencode的内容进行urldecode
    $data = urldecode($data);

    // 缩进处理
    $ret = '';
    $pos = 0;
    $length = strlen($data);
    $indent = isset($indent)? $indent : '    ';
    $newline = "\n";
    $prevchar = '';
    $outofquotes = true;

    for($i=0; $i<=$length; $i++){

        $char = substr($data, $i, 1);

        if($char=='"' && $prevchar!='\\'){
            $outofquotes = !$outofquotes;
        }elseif(($char=='}' || $char==']') && $outofquotes){
            $ret .= $newline;
            $pos --;
            for($j=0; $j<$pos; $j++){
                $ret .= $indent;
            }
        }

        $ret .= $char;

        if(($char==',' || $char=='{' || $char=='[') && $outofquotes){
            $ret .= $newline;
            if($char=='{' || $char=='['){
                $pos ++;
            }

            for($j=0; $j<$pos; $j++){
                $ret .= $indent;
            }
        }

        $prevchar = $char;
    }

    return $ret;
}