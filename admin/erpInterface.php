<?php 

// 首先加载相关配置
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


$sourceLink =    "FJAKFJFJJXMDJA&@!#$$";
$_act_get = isset($_GET['act']) ? strtolower(trim($_GET['act'])) : NULL;
// 
if(strcasecmp($_act_get, 'getProductAttr') == 0)
{
    // 查询多个产品ID列表获取属性相关信息
    $res = [];
    $productIDs = isset($_GET['productLists']) ? $_GET['productLists'] : null;
    $unixTime = isset($_GET['time']) ? $_GET['time'] : null;
    $token = isset($_GET['token']) ? $_GET['token'] : null;
    if (empty($productIDs) || empty($unixTime) || empty($token)) {
        $res = ['status' => 'failed', 'info' => 'empty params productLists', results => []];
    } else {
        $ids = explode(',', $productIDs);
        $needle = 0;
        foreach ($ids as $key => $value) {
            if (!is_numeric($value) || $value <= 0) {
                $res = ['status' => 'failed', 'info' => 'params productLists error format', 'results' => []];
                $needle +=1;
               continue;
            }
        }

        // 验证加密
        if (strtoupper($token) != strtoupper(md5($unixTime . $sourceLink))) {
             $res = ['status' => 'failed', 'info' => 'token checkout failed', 'results' => []];
             $needle +=1;
        }

        if (!$needle) {
            $DB = $register->get('db');
            $sql = "select product_attr_id, product_id, attr_group_id, name, number, attr_group_title from product_attr where is_del = 0 and product_id in (" . $productIDs . ") ";
            $list = $DB->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

            // 组装数据
            $echoList = [];
            foreach ($ids as $k1 => $v1) {
                $echoList[$v1]['attrs'] = [];
                $echoList[$v1]['product_id'] = $v1;
                foreach ($list as $k2 => $k3) {
                    if ($v1 == $k3['product_id']) {
                        array_push($echoList[$v1]['attrs'], $k3);
                    }
                }
                $echoList[$v1]['attr_count'] = count($echoList[$v1]['attrs']);
            }
            $res = ['status' => 'success', 'info' => 'success', 'results' => array_values($echoList)];
        }
    }
    echo json_encode($res, true);exit;
    
} elseif (strcasecmp($_act_get, 'getProductStatusByDeptAndErpNumber') == 0) {
    // 接受属性
    $oaIdDepartment = isset($_GET['oa_id_department']) ? $_GET['oa_id_department'] : null;
    $erpNumber = isset($_GET['erp_number']) ? $_GET['erp_number'] : null;
    $unixTime = isset($_GET['time']) ? $_GET['time'] : null;
    $token = isset($_GET['token']) ? $_GET['token'] : null;
    // 检查属性
    if (is_null($oaIdDepartment) || !is_numeric($oaIdDepartment) 
        || is_null($erpNumber) || !is_numeric($erpNumber) 
        || is_null($unixTime) || !is_numeric($unixTime)
        || is_null($token)

    ) {
         $res = ['status' => 'failed', 'info' => 'params  format is wrong' , results => []];
    } else {
        
        // 验证加密
        $needle = 0;
        if (strtoupper($token) != strtoupper(md5($unixTime . $sourceLink))) {
             $res = ['status' => 'failed', 'info' => 'token checkout failed', 'results' => []];
             $needle +=1;
        }

        if (!$needle) {
            $DB = $register->get('db');
            $sql = "select product_id, title, add_time, is_del, domain, identity_tag from product where oa_id_department = " . $oaIdDepartment . " and erp_number = ". $erpNumber;
            $list = $DB->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
            
            if (!empty($list)) {
                foreach ($list as $key => $value) {
                    $list[$key]['link'] = $value['domain'] . '/' . $value['identity_tag'];
                }
            }

            $res = ['status' => 'success', 'info' => 'success', 'results' => $list, 'count' => count($list)];
        }
       

    }
    echo json_encode($res, true);exit;


} else {

}















 ?>