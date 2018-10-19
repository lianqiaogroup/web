<?php

define('app_path',dirname(dirname(__FILE__)).'/');
define('phpKey','8abf7b4dc529a5b9ef624731ee71e9c7');
require_once app_path.'vendor/autoload.php';
#$config=require_once app_path.'config/base.php';
ini_set('date.timezone','Asia/Shanghai');
// include_once 'helper/function.php';
error_reporting(E_ERROR | E_WARNING | E_PARSE);
