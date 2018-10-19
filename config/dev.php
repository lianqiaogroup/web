<?php

//DB配置
$config['DB'] = [
    'database_type' => 'mysql',
    'database_name' => 'stoshop',
    'server' => '192.168.105.252',
    'username' => 'stodb',
    'password' => 'sto@123',
    'charset' => 'utf8',
    // [optional]
    'port' => 3306,
    // [optional] Table prefix
    'prefix' => '',
    'option' => [
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    ],
];

//redis 配置
$config['cache'] = [
    "driver"    => "redis",         //缓存引擎
    "host"      => "192.168.105.144", //主机地址
    #"password"  => "redismaster",   //认证密码
    #"host"      => "127.0.0.1", //主机地址
    "password"  => "",   //认证密码
    "port"      => 6379,            //端口号
    "select"    => 1,               //数据库
    "timeout"   => 0,               //有效期
    "prefix"    => "",              //前缀
    "persistence" => false          //是否持久化长连接
];

//邮件配置
$config['mail_attention'] = [
    'fromEmail' =>'glennvip@163.com',
    'fromPsw'=>'L1p92nlf',
    'fromName'=>'system warming',
    'sendMail'=>['liujun@stosz.com','pengxu@stosz.com','1307823586@qq.com'],
    'host'=>'smtp.163.com',
];

$config['mail_ispWarm'] = [
    'fromEmail' =>'report@stosz.com',
    'fromPsw'=>'bgn123',
    'fromName'=>'isp report',
    'sendMail'=>['liujun@stosz.com','zhanghui@stosz.com','liumanling@stosz.com','pengxu@stosz.com','wujianeng@stosz.com','lixianfei@stosz.com'],
    'host'=>'smtp.stosz.com',
];

// 应用名称(项目)
$config['Application'] = 'cuckoo';
//环境配置
$config['environment'] = 'office';
//域名项目id
$config['domainProjectId'] =1;

// 短信号码
$config['sms_regex'] = [               //正则表达式匹配
        '2'=>'/0[0-9]{9}$/i',                               //台湾
        '3'=>'/0[0-9]{8}|\d{8}$/i',                         //香港
        '11'=>'/0[8|9|6][0-9]{8}$/i',                       //泰国
        '64'=>'/0[8|5|6|3|1][0-9]{9}|3[0-9]{9}$/i',         //巴基斯坦
        '9'=> '/08[689]{1}\d{7}|09[0134678]{1}\d{7}|012[0-9]{1}\d{7}|016[0-9]{1}\d{7}$/i',        //越南
        '51'=> '/05[0-9]{8}$/i',                             //沙特
        '45'=> '/09[0-9]{9}$/i',                             //菲律宾
        '29'=> '/08[0-9]{8}|08[0-9]{9}|08[0-9]{10}$/i',      //印度尼西亚
        '17'=> '/011\d{8}|010\d{7}|01[2-9]{1}\d{7}$/i',      //马来西亚
        '37'=> '/\d{7}|\d{8}|\d{9}|\d{10}|\d{11}|\d{12}/i',  //柬埔寨
        '66'=> '/9[0-9]{9}$/i',                              //印度
        '72'=> '/5[0-9]{9}|05[0-9]{9}$/i',                   //土耳其
];

// 交互url链接地址
$config['apiUrl'] = [
    'erp'=>'http://luckydog-erp-front-test.stosz.com', //正式环境 http://luckydog.stosz.com
    'domain'=>'192.168.105.136', //正式环境 http://domain.stosz.com
    'oldErp'=>'http://192.168.105.252:8081', //正式环境 http://domain.stosz.com
    'sensitive' =>'http://luckydog-erp-front-test.stosz.com',//正式环境 http://luckydog-product.erp.stosz.com:8080
    'sso'=> 'http://luckydog-erp-front-test.stosz.com',//正式  http://luckydog.stosz.com
    // 单品站回调地址
    'ssoBackDomain' => "http://admin.stosz.com" 
];

define('IMGURLCN', 'http://imgcn.stosz.com'); //国内图片地址
define('IMGURL', 'http://img.stosz.com'); //国外图片地址
define('VIDEOURL', 'http://cdn.bgnht.com'); //vedio加速域名
$config['erpToken'] =  'WkUCdKeVKJHPDF8uROisFnNrOHJcFgIs';

$config['BLN_EMAIL'] = [
    'wujianeng@stosz.com', 'liujiafa@stosz.com',
    '531274884@qq.com',  'zhuangdebiao@stosz.com',
    'liqianyuan@qq.com', 'gongliwen@stosz.com'
];

$config['Aliyun']['OSS'] = [
        'AccessKeyID' => 'LTAIqVMagJFho8iL',
        'AccessKeySecret'=>'LzJFR3XuypPAz1WdFRecZs6o21hh8w',
        'EndPoint' => 'http://front-material.oss-cn-shenzhen.aliyuncs.com',
        'Bucket' => 'front-material',
        'Dir' => 'origin/',
        'Expire' => 30,
        'SizeRange' => [0,52428800]
];

$config['fmp_domain'] = 'https://pokerface.stosz.com';
//正式环境 'https://pokerface02.stosz.com';

return $config;