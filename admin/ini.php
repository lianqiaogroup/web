<?php
if (!session_id()) session_start();
//加载配置文件
#$config = require_once app_path.'config/web.php';
include_once 'base.php';
include_once 'helper/function.php';
$register = new \lib\register();

//加载配置文件
$config = new \lib\config();
$register->set('config',$config);

//缓存加载 redis
$cache = \lib\cache\Cache::createInstance($config->get('cache'));
$register->set("cache", $cache);

//加载前端引擎
$view = new \lib\view('template',app_path);
$register->set('view',$view);

$db = new \lib\db();
$register->set('db',$db);

$log = new \lib\log();
$register->set('log',$log);
$log->write('', $_REQUEST);

$register->set('register',$register);

//增加转移部门判断
if(!empty($_SESSION['admin']['uid'])){
    $id_department = $db->get('oa_users', 'id_department', ['uid'=>$_SESSION['admin']['uid']]);
    if($id_department != $_SESSION['admin']['id_department']) header("Location:/index.php?act=logout");
}

define('environment',$config->get('environment'));
if((!isset($_SESSION['admin']) || !$_SESSION['admin']) && 
	(isset($_GET['act']) && $_GET['act']!='login') && 
	(isset($_GET['act']) && $_GET['act'] !="logout") &&
	!isset($_GET['apiToken']) &&
	!isset($_GET['ticket'])
)
{
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
    	&& strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' 
    	|| $_GET['json']==1) 
    {
        echo  json_encode(['session'=>0]);
    } else {
    	if( environment == "office")
        {
            $erpSso = new \admin\helper\api\erpsso($register, 'dev');
            $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? 
                    $_SERVER['HTTP_X_FORWARDED_HOST'] : 
                    (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
           
            if (empty($host)) {
                die('程序获取测试环境域名失败');
            }

            $erpSso->setExperimentEnvironment("http://" . $host);
        }else{
            $erpSso = new \admin\helper\api\erpsso($register);
            $serverPort = !empty($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80;
            $erpSso->setIDCEnvironmentServerPort($serverPort);
        }
	    $erpUrl = $erpSso->getRequestDomain();
    	header("HTTP/1.1 301 Moved Permanently");
		header("Location:" . $erpUrl);
    }
   	exit;
}
elseif(isset($_SESSION['admin']) && isset($_SESSION['admin']['key']) && $_SESSION['admin']['key'] && $_SESSION['admin']['key'] !=phpKey){
    die("hacking attempt");
}