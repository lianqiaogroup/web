<?php
//订单量/订单信息 查询

require __DIR__. '/ini.php';


$actionName = isset($_GET['act']) ? trim($_GET['act']) : (isset($_POST['act']) ? trim($_POST['act']) : '');
if(empty($actionName)){
    OrderQuantity::notFound();
}else{
    if( !isset($_SESSION, $_SESSION['admin'], $_SESSION['admin']['uid']) || 0 == intval($_SESSION['admin']['uid'])
        || 0 == intval($_SESSION['admin']['id_department'])
    ){
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');
        exit(\json_encode(['code'=>400, 'msg'=>'用户ID为0 或 部门ID为0, 请重新登陆']));
    }

    echo OrderQuantity::$actionName();
    exit;
}

class OrderQuantity
{
    public static function __callStatic($name, $arguments)
    {
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');
        return \json_encode(['code'=>400, 'msg'=>'method not exists']);

        /*header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        header('Refresh: 10; url=/');
        echo '<h1>404 Not Found</h1>';
        exit;*/
    }

    public static function orderQuantityQuery()
    {
        global $register;

        if (!isset($_GET['type']) || ! in_array((string) $_GET['type'], ['search_domain', 'search_member']) ) {
            return json_encode(['ret' => 0, 'message' => '请选择搜索方式']);
        }
        if (empty($_GET['search_value'])) {
            return json_encode(['ret' => 0, 'message' => '请输入搜索条件']);
        }

        // 处理时间
        if (!empty($_GET['start_time']) && !empty($_GET['end_time'])) {
            $startTime      = \date('Y-m-d H:00:00', \strtotime(\trim($_GET['start_time'])));//开始时间
            $endTimeString2 = \date('Y-m-d H:00:00', \strtotime(\trim($_GET['end_time'])));  //结束时间
            $endTimeClose   = \strtotime($endTimeString2) - 1;
            $endTime        = \date('Y-m-d H:i:s', $endTimeClose);//结束时间减1秒

            if ($endTimeClose - strtotime($startTime) > 7*24*3600) {
                return json_encode(['ret' => 0, 'message' => '只能查询七天内的数据！']);
            }
        } else {
            $startTime = date('Y-m-d 00:00:00');
            $endTime = date('Y-m-d H:i:s');
        }

        $filter = [];

        //进行用户权限判断
        $common = new \admin\helper\common($register);
        $privInfo = $common->getUids();
        if( isset($privInfo['company_id']) && ! empty($privInfo['company_id']) ){
            $filter[] = 'product.company_id = ' . (int) $privInfo['company_id'];//公司ID
        }
        if( isset($privInfo['uid']) && $privInfo['uid'] ){
            $filter[] = 'product.oa_id_department in (' . implode(',', $privInfo['id_department']) .')';//部门列表也代表员工列表!
            if( ! isset($privInfo['is_leader']) || ! $privInfo['is_leader'] ){
                $memberList = \array_map(function($str){
                    return "'".$str."'";
                }, (array) $privInfo['ad_member']);
                $filter[] = 'product.ad_member in (' . implode(',', $memberList) .')';//部门列表也代表员工列表!
            }
        }

        switch ((string) $_GET['type']) {
            case 'search_domain':
                // 解析域名或URL
                $url = trim($_GET['search_value']);
                $url = (strpos($url, 'http://') === false) ? ('http://'.$url) : $url;
                $urlInfo = parse_url($url);
                //主机名
                if( ! isset($urlInfo['host'])){
                    return json_encode(['ret' => 0, 'message' => '主机名/域名错误']);
                }
                $filter[] = 'product.domain = \'' . htmlspecialchars($urlInfo['host']) . '\'';
                //path
                if (isset($urlInfo['path'])) {
                    $path = str_replace('/', '', $urlInfo['path']);
                    $filter[] = 'product.identity_tag = \'' . htmlspecialchars($path) .'\'';
                }
                break;
            case 'search_member':
                if($_GET['search_value'] != -1){ // -1 全部
                    $filter[] = 'product.ad_member_id = '.intval($_GET['search_value']);
                }
                break;
            default:
                exit;
        }

        $limit = 15;
        $currentPage = (isset($_GET['p']) ? \max(1, (int) $_GET['p']) : 1);
        $offset = ($currentPage - 1) * $limit;

        // 查询商品列表,联表查订单量(日期约束)
        $filter = implode(' AND ', $filter);
        $sql =<<<PRODUCT_LIST
select 
product.ad_member_id,
product.ad_member as user_name,
product.domain,
product.identity_tag,
product.product_id,
IF(product.is_del = 0, '上架', '下架') `is_del`,
cnt.count 
from product 
inner join 
(
  select `order`.`product_id`, count(1) as `count` from `order` 
  where `order`.add_time 
  BETWEEN '$startTime' and '{$endTime}'
  group by `order`.`product_id`
) cnt on cnt.product_id = product.product_id
where {$filter}
order by 
cnt.count desc,
product.product_id desc,
product.ad_member_id desc;
PRODUCT_LIST;

        $pdoStament = \lib\register::getInstance('db')->query($sql);
        $productList = $pdoStament ? $pdoStament->fetchAll(\PDO::FETCH_ASSOC) : [];
        if ( 0 == count($productList) ) {
            return json_encode(['ret' => 0, 'message' => '搜索不到订单数据，换一个搜索条件试试？','filter' => $filter,'sql'=>$sql]);
        }

        //总订单量
        $totalOrder = 0;//配合前端做变量的命名
        foreach($productList as &$productInfo){
            $totalOrder += (int) $productInfo['count'];
        }

        return json_encode([
            //'sql' => $sql,
            //'filter' => $filter,
            'limit' => $limit, //输出多少行
            'offset'=> $offset,//第0~n行 开始输出
            'currentPage'=> $currentPage,//当前分页 第几页
            'pageCount'  => ceil(count($productList) / 1.0 / $limit),//总页数
            'count'      => count($productList),//总行数
            'goodsList'  => \array_slice($productList, $offset, $limit), //只输出必要的数据
            'totalOrder'   => $totalOrder //总订单量
        ]);
    }


    //同部门的优化师列表
    public static function getDepartmentTargetUser()
    {
        global $register;
        $c = new \admin\helper\company($register);
        $common = new \admin\helper\common($register);
        $uid = $common->getUids();

        $isLeader = false;
        if (isset($uid['is_leader']) && $uid['is_leader']) {
            $isLeader = true;
        }
        if (empty($uid['uid']) || $isLeader) {
            $ret = $c->getAdUser($_SESSION['admin']['uid']);
            if (is_null($ret)) {
                $ret = $c->getAUser($_SESSION['admin']['uid']);
            }
        } else {
            $ret[] = \lib\register::getInstance('db')->get('oa_users',
                ['uid(ad_member_id)','username','name_cn(name)','id_department','department','manager_id'],
                ['uid'=>$_SESSION['admin']['uid']]);

        }

        return json_encode(['ret'=>$ret]);
    }



    /**
     * 统计product浏览量pv
     * @param  array  $product [description]
     * @return int PV值
     * @deprecated 写完后又不调用,等待废弃
     */
    private static function getProductPageView($product=[], $startTime, $endTime)
    {
        $api = new \admin\helper\api\pageView();
        $api->setProductId($product['product_id']);
        $api->setStartTimer($startTime);
        $api->setEndTimer($endTime);
        $pageView = $api->getPageView();
        return  $pageView['data']['pv'] ?:0;
    }

}




