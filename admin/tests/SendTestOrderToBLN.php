<?php
/**
 * Created by PhpStorm.
 * User: zdb
 * Date: 2018/7/6
 * Time: 19:34
 */

require __DIR__.'/../../vendor/autoload.php';

use lib\register;

#define('app_path',(dirname(__FILE__)).'/' );
#define('environment','office');
#define('environment','idc');
include_once __DIR__.'/../../admin/base.php';


(new \cmd\SendTestOrderToBLN())->execute(null, null);

echo '<pre>今天的全部订单发送完毕<br>';
print_r(
    register::getInstance('config')->get('BLN_EMAIL')
);
echo '</pre>';