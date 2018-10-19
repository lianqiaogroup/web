<?php

if($_SERVER['REQUEST_METHOD'] == 'GET'){
    $url = isset($_GET['url'])? $_GET['url']: "";

    if(!$url){
        echo json_encode(['ret'=>0,"ret_message"=>'资源地址不能为空!']);
        exit(0);
    }

    $files = parse_url($url);

    if(strpos($files['path'],'/') >= 0){
        $filename = end(explode('/',$files['path']));
    }else{
        $filename = $files['path'];
    }
    header('Content-type: application/jpeg');
    header('Content-disposition: attachment; filename='.$filename.';');
    header('Content-Length: '.remote_filesize($url));
    readfile($url);
    exit;
}

function remote_filesize($url, $user = "", $pw = "")
{
    ob_start();
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 1);

    if(!empty($user) && !empty($pw))
    {
        $headers = array('Authorization: Basic ' .  base64_encode("$user:$pw"));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $ok = curl_exec($ch);
    curl_close($ch);
    $head = ob_get_contents();
    ob_end_clean();

    $regex = '/Content-Length:\s([0-9].+?)\s/';
    $count = preg_match($regex, $head, $matches);

    return isset($matches[1]) ? intval($matches[1]) : 0;
}
