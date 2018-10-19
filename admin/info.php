<?php
/**
 * Created by PhpStorm.
 * User: jimmy
 * Date: 2017/8/3
 * Time: 上午10:30
 */
require  'ini.php';
/**
 * @var \lib\config $config
 * @var \lib\cache\driver\Redis $cache
 * @var \lib\view $view
 * @var \lib\db $db
 * @var \lib\log $log
 */

use admin\helper\qiniu;
$productId= isset($_GET['pid'])      ? $_GET['pid'] : 0;
$username = isset($_GET['username']) ? $_GET['username'] : '';
$uid      = isset($_GET['uid']) ? ((int) $_GET['uid']) : 0;
if( isset($_SESSION['admin'], $_SESSION['admin']['username']) ) {
    $log->write('VIEWINFO', '用户'.$_SESSION['admin']['username'].'查看了erp_id为 '.$productId.' 的产品');
}
$qiniu = new qiniu();
$fields = ['content', 'thumb', 'title', 'add_time', 'del_time', 'product_id', 'domain', 'identity_tag', 'ad_member_id', 'ad_member'];

function outputNotFound($msg){
    echo "<html><body><head></head><body>{$msg} </body></html>";
    exit;
}

//两种查询方式,
//1. 按产品ID查询 $productId               -- 新ERP-采购管理-商品详情-单品站
//2. 按产品ID $productId + 用户ID $uid 查询 -- 用于超管?

//预览产品详情图片
if( $uid ){
    $map = [
        'AND' => [
            'product_id' => $productId,
            'is_del' => [1, 10]
        ]
    ];
    //判断是否管理员
    if( ! $_SESSION['admin']['is_root'] ){
        $c = new \admin\helper\company($register);
        $data = $c->getUIdAndMemberList($uid,$contain='1');
        $oaUserInfo = [
            'uid'       => \array_column($data['data'], 'uid'),
            'ad_member' => \array_column($data['data'], 'ad_member'),
            'id_department' => \array_unique(\array_column($data['data'], 'id_department'))
        ];
        if ($oaUserInfo['uid']) {
            $map['AND']['oa_id_department'] = $oaUserInfo['id_department'];
            if ( ! $data['is_leader']) {
                $map['AND']['OR'] = [
                    'aid' => $uid,
                    'ad_member' => $oaUserInfo['ad_member'],
                ];
            }
        }
    }

    $product = $db->get('product', $fields, $map);
    if($product){
        $product['thumb']   = $qiniu->get_video_path($product['thumb']);
        $product['content'] = $qiniu->get_content_path($product['content']);
        $product['photos']  = $db->select('product_thumb','*', [ 'product_id' => $product['product_id'] ]);
        foreach ( $product['photos'] as $k=>$v) {
            $product['photos'][$k]['thumb'] = $qiniu->get_video_path($v['thumb']);
        }
        $view->display('/product/simpleContent.twig', $product);
    }else{
        //TODO 需求跳转到LAB站
        outputNotFound("没有找到该产品");
    }
    exit;
}

if($productId)
{
    if($username){
        // aid
        $aid = $db->get('oa_users',['uid','name_cn'],['username'=>$username]);
        if(!$aid){
            outputNotFound("没有找到".$username."优化师");
        }
    }

    $where['and']['erp_number'] = $productId;
    if($aid){
        $or['aid'] = $aid['uid'];
        $or['ad_member'] = $aid['name_cn'];
        $where['and']['OR'] = $or;
    }

    //查询productId
    $id_product = $db->select('product', ['product_id'], $where);
    if(!$id_product)
    {
        $productId = '0'.$productId; //TODO 为什么要加前缀 0 ?
        $where['and']['erp_number'] = $productId;
        $id_product = $db->select('product', ['product_id'], $where);
        if(!$id_product) outputNotFound("没有找到该产品");
    }

    $id_product = \array_column($id_product,'product_id');
    \rsort($id_product);
    $id_product = \array_slice($id_product, 0, 100);
    $id_product = \implode(',', $id_product);

    //$yesMon = date('Y-m-d',strtotime('-1 month'));
    //查询order最多的产品
//    $sql ="select `product_id`,count(*) as `count` from `order` where product_id in ($id_product) and add_time > '$yesMon'  group by product_id order by `count` desc limit 1";
    //查询最新订单的产品
    $sql =<<<GET_NEW_ORDER
select product_id from `order` where 
`order`.product_id in ($id_product) and `erp_status` IN ('SUCCESS', 'FINISH') 
order by `order`.add_time desc limit 1
GET_NEW_ORDER;
    /**
     * @var \PDOStatement|bool $pdoStatement
     */
    $pdoStatement = $db->query($sql);
    if($pdoStatement && ($orderInfo = $pdoStatement->fetch(\PDO::FETCH_ASSOC) )){
        $map['product_id'] = $orderInfo['product_id'];
    }else{
        $map['erp_number'] = $productId;
        $map['is_del'] = 0;
        $map['ORDER'] = ['product_id'=>"DESC"];
    }

    //获取 商品信息
    $productInfo  =  $db->get('product',$fields, $map);
    if(!$productInfo){
        //TODO 新需求 跳转到LAB站查询
         outputNotFound("ERPID为".$productId."的产品都已删除或没有建站");
    }

    $productInfo['content'] = $qiniu->get_content_path($productInfo['content']);
    $productInfo['thumb']   = $qiniu->get_video_path($productInfo['thumb']);
    $productInfo['photos']  = (array) $db->select('product_thumb','*', ['product_id'=>$productInfo['product_id']]);
    foreach ( $productInfo['photos'] as $k => $v) {
        $productInfo['photos'][$k]['thumb'] = $qiniu->get_video_path($v['thumb']);
    }
    $view->display('/product/simpleContent.twig', $productInfo);
}