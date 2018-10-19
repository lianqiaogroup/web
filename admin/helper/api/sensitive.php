<?php
/**
 * Created by PhpStorm.
 * User: yangpan
 * Date: 2018/3/28
 * Time: 14:09
 */

namespace admin\helper\api;


class sensitive extends erpbase
{

    function __construct($type = ''){
        $this->domain = \lib\register::getInstance('config')->get('apiUrl.sensitive');
    }

    function getSensitive($data){
        $token = \lib\register::getInstance('config')->get('erpToken');
        $timestamp = $this->getMillisecond();
        $nonce = $this->getRandomStr(8);
        $uri = $this->domain."/product/base/sensitiveWord/findSensitive/list";
        $sign = $this->getSign($token,$timestamp,$nonce);
        $headers = [
            "X-PROJECT-ID:frontend.website.build",
            "Accept:application/json,text/plain,*/*",
            "x-requested-with:XMLHttpRequest",
            "X-AUTH-TIMESTAMP:$timestamp",
            "X-AUTH-NONCE:$nonce",
            "X-AUTH-SIGNATURE:$sign",
        ];
        $res = $this->sendPost($uri,$data,$headers);

        (new \lib\log())->write('product',$uri.'=>'. print_r($headers,1) . print_r($res,1) );
        $res = json_decode($res['message'],1);
        if(($res['code'] == 'OK') && !empty($res['item'])){
            return $res['item'];
        }
        return null;
    }

    function getSign($token,$timestamp,$nonce){
        return md5($token.$timestamp.$nonce);
    }

}