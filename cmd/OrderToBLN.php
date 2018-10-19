<?php

namespace cmd;

use lib\register;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


abstract class OrderToBLN extends Command
{
    public $msg;
    public function __construct($msg = '')
    {
        $this->msg = $msg;
        parent::__construct();

    }

    public function configure()
    {
        $this->setName('OrderToBLN');
    }

    /**
     * @var \lib\config @config
     * @var \lib\log @$logger
     * @var \Medoo\Medoo $db
     */
    protected $config;
    //protected $logger;
    protected $db;
    protected $register;
    protected function init()
    {
        $this->config = new \lib\config();
        //$this->logger = new \lib\log();
        //fuck register , 不是正规的DI
        $this->register = new register();
        $this->register->set('config', $this->config);
        set_time_limit(0);
        ini_set('memory_limit', '-1');
    }

    /**
     * @return \Medoo\Medoo
     * @throws \exception
     */
    protected function getDb()
    {
        if(isset($this->db, $this->db->db, $this->db->db->pdo) && $this->db->db->pdo){
        }else{
            $this->db = new \lib\db(/*$this->config->get('DB')*/);
            return $this->db->db;
        }
        return $this->db->db;
    }

    protected function getOrderList()
    {
        $this->init();

        if($this->config->get('environment') == 'office'){
            $dept = '`oa_users`.`company_id` != 9 and ';
        }else{
            $dept = '`oa_users`.`company_id` = 9 and ';
        }

        $sql =<<<GET_ORDER_LIST
select 
`order`.`order_no` `订单号ID`,
`order`.`erp_no` `ERP单号`,
`order`.`add_time` `下单时间`,
concat( `product`.`domain`, IF( LENGTH(`product`.`identity_tag`) > 0 , concat('/', `product`.`identity_tag`), '' ) ) `站点域名`,
`oa_users`.`department` `部门`,
`oa_users`.`name_cn` `优化师`,
IF(0=`product`.`is_open_sms`,'未开启短信',IF(exists(
	select 1 from t_sms_order where 
	t_sms_order.order_id = `order`.`order_id`
	and `t_sms_order`.`status` = 1
), '验证成功', '验证失败') ) `验证码状态`,
concat(`order`.`name`, IF( LENGTH(`order`.`last_name`)> 0, concat(' ', `order`.`last_name`), '')) `收货姓名`,
/*'country' `国家`, 'province' `省份`, 'city' `市`, 'area' `区`,'address' `街道`,'zipcode' `邮编`,'remark' `备注`,*/
`order`.`address` `地址`,
`order`.`mobile` `电话`,
`order`.`email` `邮箱`,
`zone`.`title` `投放地区`,
`product`.`currency` `货币`,
`order`.`num` `sku数量`,
cast((`order`.`payment_amount`/100) as decimal(14,2)) `订单总额`,
`order`.`order_id`,
`order`.`product_id`,
`order`.`combo_id`,
`order`.`order_status`,
`order`.`post_erp_data`
from 
 `order`,`oa_users`,`product`,`zone`
where 
{$dept}
`order`.`order_status` in ('SUCCESS','NOT_PAID') and
`order`.`add_time` >= concat(CURRENT_DATE, ' 00:00:00') and
/*`order`.`aid` = `oa_users`.`uid` and */
`order`.product_id = `product`.`product_id` and
`product`.`ad_member_id` = `oa_users`.`uid` and
`product`.`id_zone` = `zone`.id_zone;
GET_ORDER_LIST;
        $pdoStatement = $this->getDb()->query($sql);
        if($pdoStatement){
            $orderList = $pdoStatement->fetchAll(\PDO::FETCH_ASSOC);
            foreach($orderList as $i => $orderInfo){
                $data = \json_decode($orderInfo['post_erp_data'], true);
                $orderList[$i]['国家'] = $data['country'];
                $orderList[$i]['省份'] = $data['province'];
                $orderList[$i]['市'] = $data['city'];
                $orderList[$i]['区'] = $data['area'];
                $orderList[$i]['街道'] = $data['address'];
                $orderList[$i]['邮编'] = $data['zipcode'];
                $orderList[$i]['备注'] = $data['remark'];
            }
            return $orderList;
        }
        unset($pdoStatement);
        return [];
    }

    protected $maxProductLength = 1;//一个订单, 最大的产品个数
    protected $maxAttrLength = 0;//一个商品, 最大的属性分组个数
    protected function getOrderProductInfo($orderId)
    {
        if($orderId < 1) return [];
//        产品1-erpID
//        产品1-SKU
//        产品1-内部名
//        产品1-外文名
//        产品1-面单名
//        产品1单价
//        产品1数量
//        产品1属性1
//        产品1属性1 id
//        产品1属性1属性值
//        产品1属性1属性值 id

        //一个order_id,固定一个order_goods_id,进而关联到一个product
        $sql =<<<GET_PRODUCT_INFO
select 
order_goods.erp_id `erpID`, /*erpID*/
product.title `内部名`,      /*内部名*/
product.sales_title `外文名`,/*外文名*/
product.waybill_title `面单名`, /*面单名*/
cast((order_goods.price/100) as decimal(16,2)) `单价`,
order_goods.num `产品数量`
from order_goods left join product 
on order_goods.product_id=product.product_id
where order_goods.order_id={$orderId};
GET_PRODUCT_INFO;
        $pdoStatement = $this->getDb()->query($sql);
        $productInfo = $pdoStatement ? $pdoStatement->fetch(\PDO::FETCH_ASSOC) : [];
        unset($pdoStatement);
        if(!$productInfo){
            return [];
        }

        //一个order_id,固定一个order_goods_id,可能有0到多个product_attr_id,多条product_attr记录
        $sql =<<<GET_PRODUCT_ATTR
select 
`product_attr`.`attr_group_id` `属性分组ID`,
`product_attr`.`attr_group_title` `属性分组名称`,
`product_attr`.`number` `属性值id`,
`product_attr`.`name` `属性值名称`,
'' AS `属性分组内部名`,
'' AS `属性值内部名`
from product_attr where product_attr.product_attr_id in(
  select product_attr_id from order_goods_attr where order_goods_id in (
    select order_goods.order_goods_id from order_goods where order_goods.order_id = {$orderId}
  )
) order by product_attr.sort asc, product_attr.product_attr_id asc;
GET_PRODUCT_ATTR;
        $pdoStatement = $this->getDb()->query($sql);
        $attrList = $pdoStatement ? $pdoStatement->fetchAll(\PDO::FETCH_ASSOC) : [];
        unset($pdoStatement);
        $this->maxAttrLength = \max($this->maxAttrLength, count($attrList));

        //调用ERP接口,获取SKU
        $erpSkuList = $this->getErpSkuList([[
            'productId'       => $productInfo['erpID'],
            'attrValueIdList' => \array_column($attrList, '属性值id')
        ]]);
        if($erpSkuList['code'] == 200){
            $productInfo['SKU'] = $erpSkuList['data'][0]['sku'];
        }else{
            $productInfo['SKU'] = $erpSkuList['msg'];
        }

        //调用ERP接口, 获取属性的内部名
        $erpAttrList = $this->getErpAttrList(\array_values(\array_unique(\array_column($attrList, '属性分组ID'))));
        if($erpAttrList['code'] == 200){
            foreach($attrList as $index => &$attrInfo){
                foreach($erpAttrList['data'] as &$erpAttrInfo){
                    if($erpAttrInfo['id'] == $attrInfo['属性分组ID']){
                        $attrList[$index]['属性分组内部名'] = $erpAttrInfo['title'];
                        break 1;
                    }
                }
            }
        }

        unset($erpAttrList);

        //调用ERP接口, 获取属性值的内部名
        $erpAttrValueList = $this->getErpAttrValueList(\array_values(\array_unique(\array_column($attrList, '属性值id'))));
        if($erpAttrValueList['code'] == 200){
            foreach($attrList as $index => &$attrInfo){
                foreach($erpAttrValueList['data'] as &$erpAttrValueInfo){
                    if($erpAttrValueInfo['id'] == $attrInfo['属性值id']){
                        $attrList[$index]['属性值内部名'] = $erpAttrValueInfo['title'];
                        break 1;
                    }
                }
            }
        }
        unset($erpAttrValueList);

        return [$productInfo, $attrList];
    }

    protected function getComboProductList($order_id, $order_sku_number, $combo_id)
    {

        //TODO 有问题,如果相同商品,为什么要合并combo_product2条记录至 order_goods一条记录

        //合并后只有1条记录
        $sql =<<<GET_COMBO_GOODS_IF_MERGED
select 
order_goods.order_goods_id,
order_goods.product_id,
order_goods.erp_id `erpID`, /*erpID*/
order_goods.num,
order_goods.total,
-- combo_goods_tmp.sale_title `外文名`,
-- combo_goods_tmp.num `产品数量`,
-- cast((order_goods.total/order_goods.price) as int) `产品数量`,
-- combo_goods_tmp.num `产品数量2`,
-- cast((IF(order_goods.`price` > 0, `order_goods`.`price`, combo_goods_tmp.`promotion_price`)/100) as decimal(16,2)) `单价`,
product.title `内部名`,
product.sales_title `外文名`,
product.waybill_title `面单名`
from
`order_goods` left join product on product.product_id = order_goods.product_id
where 
`order_goods`.`order_id` = {$order_id} 
order by `order_goods`.`product_id` ASC;
GET_COMBO_GOODS_IF_MERGED;
        $pdoStatement = $this->getDb()->query($sql);
        $comboGoodsList = $pdoStatement ? $pdoStatement->fetchAll(\PDO::FETCH_ASSOC) : [];
        unset($pdoStatement);

        foreach($comboGoodsList as $i => $comboGoodsInfo){

            $comboGoodsList[$i]['产品数量'] = intval($comboGoodsInfo['num'] / $order_sku_number);
            $pdoStatement = $this->getDb()->query("select sale_title, num from combo_goods where combo_id={$combo_id} and product_id={$comboGoodsInfo['product_id']} and is_del=0 limit 1; ");
            $info = $pdoStatement ? $pdoStatement->fetch(\PDO::FETCH_ASSOC) : [];
            unset($pdoStatement);

            if($info){
                if($info['sale_title']){
                    $comboGoodsList[$i]['外文名'] = $info['sale_title'];
                }

                $comboGoodsList[$i]['单价'] = $comboGoodsInfo['total'] / $order_sku_number/ $comboGoodsList[$i]['产品数量'] / 100;
            }else{
                $comboGoodsList[$i]['单价'] = '';//???商品被删除了
            }

        }

        //$comboGoodsCnt = intval($this->getDb()->count('combo_goods', 'combo_goods_id', ['combo_id' => $combo_id]));
        //$merged = $comboGoodsCnt > count($comboGoodsList);
        $merged = false;
        if($merged){
            //合并之前至少有2条记录, 不该合并!
            $sql =<<<GET_COMBO_GOODS
select 
combo_goods.product_id,
combo_goods.num `产品数量`,
cast((IF(combo_goods.promotion_price > 0, combo_goods.promotion_price, product.price)/100) as decimal(16,2))`单价`,
product.erp_number `erpID`,
product.title `内部名`,
product.sales_title `外文名`,
product.waybill_title `面单名`
from combo_goods
left join product on product.product_id = combo_goods.product_id
where combo_goods.combo_id in (select `order`.combo_id from `order` where `order`.order_id = {$order_id});
GET_COMBO_GOODS;
            $pdoStatement = $this->getDb()->query($sql);
            $comboGoodsList = $pdoStatement ? $pdoStatement->fetchAll(\PDO::FETCH_ASSOC) : [];
        }

        $sql =<<<GET_COMBO_GOODS_ATTR
select 
`product_attr`.`attr_group_id` `属性分组ID`,
`product_attr`.`attr_group_title` `属性分组名称`,
`product_attr`.`number` `属性值id`,
`product_attr`.`name` `属性值名称`,
'' AS `属性分组内部名`,
'' AS `属性值内部名`,
`product_attr`.`product_id`,
`order_goods`.`order_goods_id`
from order_goods 
inner join order_goods_attr on order_goods_attr.order_goods_id = order_goods.order_goods_id
left join product_attr on product_attr.product_attr_id = order_goods_attr.product_attr_id
where order_goods.order_id = {$order_id} 
order by product_attr.product_id asc limit 500 offset 0;
GET_COMBO_GOODS_ATTR;
        $pdoStatement = $this->getDb()->query($sql);
        $comboGoodsAttrList = $pdoStatement ? $pdoStatement->fetchAll(\PDO::FETCH_ASSOC) : [];
        unset($pdoStatement);

        $productList = [];
        foreach($comboGoodsList as $i => &$comboGoodsInfo)
        {
            $attrList = [];
            foreach($comboGoodsAttrList as $ii => &$attrInfo){
                if($merged){
                    if($attrInfo['product_id'] == $comboGoodsInfo['product_id']){
                        $attrList[] = $attrInfo;
                    }
                }else{
                    if($attrInfo['order_goods_id'] == $comboGoodsInfo['order_goods_id']){
                        $attrList[] = $attrInfo;
                    }
                }
            }
            $this->maxAttrLength = \max($this->maxAttrLength, count($attrList));

            //调用ERP接口,获取SKU
            $erpSkuList = $this->getErpSkuList([[
                'productId'       => $comboGoodsInfo['erpID'],
                'attrValueIdList' => \array_column($attrList, '属性值id')
            ]]);
            if($erpSkuList['code'] == 200){
                $comboGoodsInfo['SKU'] = $erpSkuList['data'][0]['sku'];
            }else{
                $comboGoodsInfo['SKU'] = $erpSkuList['msg'];
            }

            //调用ERP接口, 获取属性的内部名
            $erpAttrList = $this->getErpAttrList(\array_values(\array_unique(\array_column($attrList, '属性分组ID'))));
            if($erpAttrList['code'] == 200){
                foreach($attrList as $index => &$attrInfo){
                    foreach($erpAttrList['data'] as &$erpAttrInfo){
                        if($erpAttrInfo['id'] == $attrInfo['属性分组ID']){
                            $attrList[$index]['属性分组内部名'] = $erpAttrInfo['title'];
                            break 1;
                        }
                    }
                }
            }
            unset($erpAttrList);

            //调用ERP接口, 获取属性值的内部名
            $erpAttrValueList = $this->getErpAttrValueList(\array_values(\array_unique(\array_column($attrList, '属性值id'))));
            if($erpAttrValueList['code'] == 200){
                foreach($attrList as $index => &$attrInfo){
                    foreach($erpAttrValueList['data'] as &$erpAttrValueInfo){

                        if($erpAttrValueInfo['id'] == $attrInfo['属性值id']){
                            $attrList[$index]['属性值内部名'] = $erpAttrValueInfo['title'];
                            break 1;
                        }
                    }
                }
            }
            unset($erpAttrValueList);

            $productList[] = [$comboGoodsInfo, $attrList];
        }
        $this->maxProductLength = \max($this->maxProductLength, count($comboGoodsList));

        return $productList;
    }

    protected function getExportList()
    {
        //订单列表
        $orderList = $this->getOrderList();

        $exportList = [];
        foreach($orderList as $index => $orderInfo)
        {
            //订单详情
            if($orderInfo['combo_id']){
                $productList = $this->getComboProductList($orderInfo['order_id'], $orderInfo['sku数量'], $orderInfo['combo_id']);
                $exportList[] = [$orderInfo, $productList ];
            }else{
                $productList = [ $this->getOrderProductInfo($orderInfo['order_id']) ];
                $exportList[] = [$orderInfo, $productList ];
            }
        }

        return $exportList;
    }

    protected function initExcel()
    {
        //写入excel
        $excel = new \PHPExcel();
        $defaultStyle = $excel->getDefaultStyle();
        $defaultStyle->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $defaultStyle->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $excel->removeSheetByIndex($excel->getActiveSheetIndex());
        $sheet = $excel->createSheet(0)->setTitle(date('Y年m月d日'));


        //表头
        $col = 0;
        $row = 1;

        $orderHeaderLength = count($this->orderHeaderTemplate);
        $productHeadLength = count($this->productHeadTemplate);
        $attrHeadLength = count($this->attrHeadTemplate);

        foreach($this->orderHeaderTemplate as $i => $value){
            $sheet->setCellValueExplicitByColumnAndRow($i, $row = 1, $value, \PHPExcel_Cell_DataType::TYPE_STRING2);
        }
        $sheet->getColumnDimensionByColumn(0)->setWidth(20);//订单号ID
        $sheet->getColumnDimensionByColumn(1)->setWidth(20);//ERP单号
        $sheet->getColumnDimensionByColumn(2)->setWidth(20);//下单时间
        $sheet->getColumnDimensionByColumn(3)->setWidth(18);//部门
        $sheet->getColumnDimensionByColumn(4)->setWidth(10);//优化师
        $sheet->getColumnDimensionByColumn(5)->setWidth(18);//站点域名
        $sheet->getColumnDimensionByColumn(6)->setWidth(12);//验证码状态
        $sheet->getColumnDimensionByColumn(7)->setWidth(18);//收货姓名
        $sheet->getColumnDimensionByColumn(8)->setWidth(22);//国家
        $sheet->getColumnDimensionByColumn(9)->setWidth(14);//省份
        $sheet->getColumnDimensionByColumn(10)->setWidth(14);//市
        $sheet->getColumnDimensionByColumn(11)->setWidth(10);//区
        $sheet->getColumnDimensionByColumn(12)->setWidth(40);//街道
        $sheet->getColumnDimensionByColumn(13)->setWidth(10);//邮编
        $sheet->getColumnDimensionByColumn(14)->setWidth(60);//地址
        $sheet->getColumnDimensionByColumn(15)->setWidth(12);//电话
        $sheet->getColumnDimensionByColumn(16)->setWidth(30);//email
        $sheet->getColumnDimensionByColumn(17)->setWidth(14);//备注
        $sheet->getColumnDimensionByColumn(18)->setWidth(10);//投放地区
        $sheet->getColumnDimensionByColumn(19)->setWidth(8);//货币
        $sheet->getColumnDimensionByColumn(20)->setWidth(12);//金额
        $sheet->getColumnDimensionByColumn(21)->setWidth(8);//sku数量

        for($productIndex = 1; $productIndex <= $this->maxProductLength; $productIndex++) {

            //产品头部
            foreach($this->productHeadTemplate as $i => $value){
                $thisCol = $i + $orderHeaderLength + //订单
                    ($productIndex-1)* ($productHeadLength + $this->maxAttrLength * $attrHeadLength);//产品
                $value = sprintf($value, $productIndex);
                $sheet->setCellValueExplicitByColumnAndRow($thisCol, $row = 1, $value, \PHPExcel_Cell_DataType::TYPE_STRING2);
                if($i == 0){
                    $sheet->getColumnDimensionByColumn($thisCol)->setWidth(18);//erpID
                }else if($i == 1){
                    $sheet->getColumnDimensionByColumn($thisCol)->setWidth(22);//内部名
                }else if($i == 2){
                    $sheet->getColumnDimensionByColumn($thisCol)->setWidth(26);//外文名
                }else if($i == 3){
                    $sheet->getColumnDimensionByColumn($thisCol)->setWidth(20);//面单名
                }else if($i == 4){
                    $sheet->getColumnDimensionByColumn($thisCol)->setWidth(12);//单价
                }else if($i == 5){
                    $sheet->getColumnDimensionByColumn($thisCol)->setWidth(12);//数量
                }

            }

            //属性分组
            for($attrIndex = 1; $attrIndex <= $this->maxAttrLength; $attrIndex++){
                foreach($this->attrHeadTemplate as $i => $value){
                    $thisCol = $i + $orderHeaderLength + //订单
                        ($productIndex-1)* ($productHeadLength + $this->maxAttrLength * $attrHeadLength) + //产品
                        $productHeadLength + //上方的产品头部
                        ($attrIndex-1) * $attrHeadLength; //前几次循环的偏移量
                    $value = sprintf($value, $productIndex, $attrIndex);
                    $sheet->setCellValueExplicitByColumnAndRow($thisCol, $row = 1, $value, \PHPExcel_Cell_DataType::TYPE_STRING2);
                }
            }
        }

        $this->excel = $excel;
        $this->sheet = $sheet;
        return $row;
    }

    /**
     * @var \PHPExcel $excel
     * @var \PHPExcel_Worksheet $sheet
     */
    protected $excel;
    protected $sheet;
    protected $orderHeaderTemplate = [
        '订单号ID', 'ERP单号', '下单时间', '部门', '优化师', '站点域名', '验证码状态', '收货姓名',
        '国家', '省份', '市', '区', '街道', '邮编', '地址', '电话', '邮箱', '备注',
        '投放地区', '货币', '订单总额', 'sku数量'
    ];
    protected $productHeadTemplate = [
        '产品%d-erpID',
        '产品%d-SKU',
        '产品%d-内部名',
        '产品%d-外文名',
        '产品%d-面单名',
        '产品%d-单价',
        '产品%d-数量',
    ];
    protected $attrHeadTemplate = [
        '产品%d-属性%d外文名',          //属性分组名称-外文
        '产品%d-属性%d内文名',          //属性分组名称-内文
        '产品%d-属性%d id',             //属性分组ID
        '产品%d-属性%d属性值外文名',    //属性值-外文
        '产品%d-属性%d属性值内文名',    //属性值-内文
        '产品%d-属性%d属性值 id',       //属性值ID
    ];

    public function execute(InputInterface $input = null, OutputInterface $output = null)
    {
        //获取订单数据
        $exportList = $this->getExportList();

        $row = $this->initExcel();
        $excel = & $this->excel;
        $sheet = & $this->sheet;

        $orderHeaderLength = count($this->orderHeaderTemplate);
        $productHeadLength = count($this->productHeadTemplate);
        $attrHeadLength    = count($this->attrHeadTemplate);
        $productField = ['erpID', 'SKU', '内部名', '外文名', '面单名', '单价', '产品数量'];
        $attrField = ['属性分组名称', '属性分组内部名', '属性分组ID', '属性值名称', '属性值内部名', '属性值id' ];


        foreach($exportList as $tmp)
        {
            $row+=1;
            list($orderInfo, $productList) = $tmp;

            foreach($this->orderHeaderTemplate as $i => $value){
                $thisCol = $i;
                $value = $orderInfo[$value];
                $sheet->setCellValueExplicitByColumnAndRow($thisCol, $row, $value, \PHPExcel_Cell_DataType::TYPE_STRING2, false);
            }

            foreach($productList as $productIndex => $tmp2) {
                list($productInfo, $attrList) = $tmp2;

                //产品头部
                foreach($this->productHeadTemplate as $i => $value){
                    $thisCol = $i + $orderHeaderLength + //订单
                        $productIndex * ($productHeadLength + $this->maxAttrLength * $attrHeadLength);//产品
                    $value = $productInfo[$productField[$i]];
                    $sheet->setCellValueExplicitByColumnAndRow($thisCol, $row, $value, \PHPExcel_Cell_DataType::TYPE_STRING2);

                }

                //属性分组
                for($attrIndex = 1; $attrIndex <= $this->maxAttrLength; $attrIndex++){
                    foreach($this->attrHeadTemplate as $i => $value){
                        $thisCol = $i + $orderHeaderLength + //订单
                            $productIndex * ($productHeadLength + $this->maxAttrLength * $attrHeadLength) + //产品
                            $productHeadLength + //上方的产品头部
                            ($attrIndex-1) * $attrHeadLength; //前几次循环的偏移量
                        $attrInfo = $attrList[$attrIndex-1];
                        $value = $attrInfo[$attrField[$i]];
                        $sheet->setCellValueExplicitByColumnAndRow($thisCol, $row, $value, \PHPExcel_Cell_DataType::TYPE_STRING2);

                        if($i == 0){
                            $sheet->getColumnDimensionByColumn($thisCol)->setWidth(24);//属性分组 名称
                        }else if($i == 1){
                            $sheet->getColumnDimensionByColumn($thisCol)->setWidth(24);//属性分组 内部名
                        }else if($i == 2){
                            $sheet->getColumnDimensionByColumn($thisCol)->setWidth(24);//属性分组 ID
                        }else if($i == 3){
                            $sheet->getColumnDimensionByColumn($thisCol)->setWidth(24);//属性值 名称
                        }else if($i == 4){
                            $sheet->getColumnDimensionByColumn($thisCol)->setWidth(24);//属性值 内部名
                        }else if($i == 5){
                            $sheet->getColumnDimensionByColumn($thisCol)->setWidth(24);//属性值 ID
                        }
                    }
                }
            }

        }

        $filePath = __DIR__ . '/../upload/BLN.'. date('Y-m-d') .'.xlsx';
        $zipPath = __DIR__ . '/../upload/BLN.'.date('Y-m-d').'.zip';

        $writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save($filePath);

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE); //打开压缩包
        $zip->addFile($filePath, \basename($filePath)); //向压缩包中添加文件
        $zip->close(); //关闭压缩包
        unlink($filePath);//删除excel文件,保留zip文件

        try{
            $this->sendEmail2($zipPath);
        }catch (\PHPMailer\PHPMailer\Exception $e){
            echo $e->getMessage(), \PHP_EOL;
            if($output){
                $output->write($e->getMessage());
            }
        }
        unlink($zipPath);
    }

    /**
     * @param $filePath
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function sendEmail2($filePath)
    {
        $config = $this->config->get('mail_ispWarm');
        $config['sendMail'] = $this->config->get('BLN_EMAIL');

        $loader = require __DIR__ . '/../vendor/autoload.php';
        $loader->setPsr4('PHPMailer\\PHPMailer\\', __DIR__ . '/../lib/PHPMailer-6.0.5/src/');

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->setLanguage('zh_cn');
        $mail->addAttachment($filePath);
        $mail->isHTML(true);
        $mail->Mailer = 'smtp';
        $mail->CharSet='UTF-8';
        $mail->SMTPAuth   = true;
        $mail->SMTPDebug = false;
        $mail->SMTPSecure = 'ssl';
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            )
        );
        $mail->Port      = 465;
        $mail->Host       = $config['host'];
        $mail->Username   = $config['fromEmail'];
        $mail->Password   = $config['fromPsw'];
        $mail->AuthType = 'LOGIN';
        $mail->setFrom($config['fromEmail'], 'BLN单品站');
        if(is_array($config['sendMail']))
        {
            foreach ($config['sendMail'] as $value)
            {
                $mail->AddAddress($value);
            }
        }

        $mail->Subject  = '【BLN单品站订单】'.\date('Y-m-d');
        $mail->Body = '<p>订单信息请见附件。</p><p>'.
            ($this->config->get('environment') == 'office' ? '内测阶段，仅供参考，请以 后台数据 为准。' : '') .
            '如有疑问请咨询产品经理 <a href="mailto:liujiafa@stosz.com">刘加发 liujiafa@stosz.com</a></p>';
        $mail->WordWrap   = 80; // 设置每行字符串的长度
        $mail->send();

    }


    /**
     * @param $filePath
     * @throws \phpmailerException
     */
    private function sendEmail($filePath)
    {
        require __DIR__ . '/../lib/phpmailer/class.phpmailer.php';

        $mail = new \PHPMailer(true);
        $config = $this->config->get('mail_ispWarm');
        $config['sendMail'] = $this->config->get('BLN_EMAIL');

        $mail->IsSMTP();
        $mail->IsHTML(true);
        $mail->CharSet='UTF-8';
        $mail->SMTPAuth   = true;
        $mail->Port       = 25;
        $mail->Host       = $config['host'];
        $mail->Username   = $config['fromEmail'];
        $mail->Password   = $config['fromPsw'];
        $mail->From       = $config['fromEmail'];
        $mail->FromName   = 'BLN单品站';
        if(is_array($config['sendMail']))
        {
            foreach ($config['sendMail'] as $value)
            {
                $mail->AddAddress($value);
            }
        }

        $mail->Subject  = '【BLN单品站订单】'.\date('Y-m-d');
        $mail->Body = '<p>订单信息请见附件。</p><p>'.
            ($this->config->get('environment') == 'office' ? '内测阶段，仅供参考，请以 后台数据 为准。' : '') .
            '如有疑问请咨询产品经理 <a href="mailto:liujiafa@stosz.com">刘加发 liujiafa@stosz.com</a></p>';
        $mail->AddAttachment( $filePath );
        $mail->WordWrap   = 80; // 设置每行字符串的长度
        $mail->Send();

    }

    //调用ERP接口,获取SKU
    protected function getErpSkuList($requestList)
    {
        //$postData = [
        //    [
        //        "id" => $productId,
        //        "valueList" => [
        //            [
        //                "id" => $attrValueIdList[0]
        //            ]
        //        ]
        //    ]
        //];
        $postData = [];
        foreach($requestList as $k => $tmp){
            $productId = $tmp['productId'];
            $attrValueIdList = $tmp['attrValueIdList'];
            sort($attrValueIdList);
            $skuUnit = [
                'id' => (int) $productId,
                'valueList' => []
            ];
            foreach($attrValueIdList as $attrValueId){
                \array_push($skuUnit['valueList'], ['id' => (int) $attrValueId]);
            }
            \array_push($postData, $skuUnit);
        }
        try{
            $domian = $this->config->get('apiUrl.erp');
            $url = $domian. '/product/manage/findSkuByProValId';
            $response = $this->sendPost($url, ['params' => json_encode($postData) ], $this->getHeaders());
        }catch (\Exception $e){
            return [ 'code' => 400, 'msg' => $e->getMessage() ];
        }
        $response = json_decode($response,true);
        if( \json_last_error() != \JSON_ERROR_NONE ){
            return [ 'code' => 400, 'msg' => \json_last_error_msg() ];
        }
        if( $response['code'] !== 'OK' || ! is_array($response['item']) ){
            return [ 'code' => 400, 'msg'  => $response['desc'] ];
        }

        //复杂的映射
        $skuInfoList = [];
        foreach($requestList as $k => $tmp){
            $productId = $tmp['productId'];
            $attrValueIdList = $tmp['attrValueIdList'];
            sort($attrValueIdList);
            $skuUnit = [
                'id' => $productId,
                'attrValueIdList' => $attrValueIdList,
                'skuInfo' => null
            ];

            foreach($response['item'] as $skuInfo){
                if($skuInfo['id'] != $productId){
                    continue 1;
                }

                foreach($skuInfo['valueList'] as $attrValueInfo){
                    if( ! in_array( $attrValueInfo['id'], $attrValueIdList) ){
                        break 2;
                    }
                }
                unset($skuInfo['valueList']);
                $skuUnit['skuInfo'] = $skuInfo;//找到了就添加进去,暂停循环(避免erp接口返回重复数据)
                array_push($skuInfoList, $skuInfo);
                break 1;
            }
        }

        return [ 'code' => 200, 'data' => $skuInfoList ];
    }

    //获取ERP的属性名称(采购人员输入的中文,不是外文)
    protected function getErpAttrList($erpAttrIdList)
    {
        if(empty($erpAttrIdList)){
            return [ 'code' => 200, 'data' => [] ];
        }
        try{
            $domian = $this->config->get('apiUrl.erp');
            $url = $domian. '/product/manage/findAttributeByIds';
            $response = $this->sendPost($url, ['ids' => \implode(',', $erpAttrIdList) ], $this->getHeaders());
        }catch (\Exception $e){
            return [ 'code' => 400, 'msg' => $e->getMessage() ];
        }
        $response = json_decode($response,true);
        if( \json_last_error() != \JSON_ERROR_NONE ){
            return [ 'code' => 400, 'msg' => \json_last_error_msg() ];
        }
        if( $response['code'] !== 'OK' || ! is_array($response['item']) ){
            return [ 'code' => 400, 'msg'  => $response['desc'] ];
        }

//        {
//            "code" : "OK",
//  "desc" : "批量查询属性成功!",
//  "item" : [ {
//            "creatorId" : null,
//    "id" : 1959,
//    "title" : "鞋子顏色",
//    "createAt" : "2017-10-26 19:58:21",
//    "updateAt" : "2017-10-26 19:58:21",
//    "version" : -1959,
//    "attributeValueList" : null,
//    "attributeLangs" : null,
//    "bindIs" : null,
//    "categoryId" : null,
//    "productId" : null,
//    "bindingNumber" : 0,
//    "relId" : 1959,
//    "table" : "attribute"
//  }]}

        //筛选必要字段
        $attrList = [];
        foreach($response['item'] as &$attrInfo){
            array_push($attrList, [
                'id'    => $attrInfo['relId'],
                'title' => $attrInfo['title']
            ]);
        }
        return [ 'code' => 200, 'data' => $attrList ];
    }

    //获取ERP的属性值名称(采购人员输入的中文,不是外文)
    protected function getErpAttrValueList($erpAttrValueIdList)
    {
        if(empty($erpAttrValueIdList)){
            return [ 'code' => 200, 'data' => [] ];
        }
        try{
            $domian = $this->config->get('apiUrl.erp');
            $url = $domian. '/product/manage/findAttrValByIds';
            $response = $this->sendPost($url, ['ids' => \implode(',', $erpAttrValueIdList) ], $this->getHeaders());
        }catch (\Exception $e){
            return [ 'code' => 400, 'msg' => $e->getMessage() ];
        }
        $response = json_decode($response,true);
        if( \json_last_error() != \JSON_ERROR_NONE ){
            return [ 'code' => 400, 'msg' => \json_last_error_msg() ];
        }
        if( $response['code'] !== 'OK' || ! is_array($response['item']) ){
            return [ 'code' => 400, 'msg'  => $response['desc'] ];
        }

//        {
//            "code" : "OK",
//          "desc" : "批量查询属性值成功!",
//          "item" : [ {
//                "creatorId" : null,
//                "id" : 3875,
//                "title" : "藍色",
//                "createAt" : "2017-10-26 19:58:24",
//                "updateAt" : "2017-10-26 19:58:24",
//                "attributeId" : 919,
//                "version" : -3875,
//                "relId" : 3875,
//                "bindIs" : null,
//                "attributeTitle" : null,
//                "attributeValueLangs" : null,
//                "productId" : null,
//                "productAttributeValueRel" : null,
//                "table" : "attribute_value"
//              } ]
//        }

        //筛选必要字段
        $attrValueList = [];
        foreach($response['item'] as &$attrValueInfo){
            array_push($attrValueList, [
                'id'    => $attrValueInfo['relId'],
                'title' => $attrValueInfo['title']
            ]);
        }
        return [ 'code' => 200, 'data' => $attrValueList ];
    }

    private function getHeaders()
    {
        $token  = $this->config->get('erpToken');
        $timestamp = (int) (microtime(true) * 1000);
        $nonce = $this->getRandomStr(8);
        $sign  = \md5($token.$timestamp.$nonce);
        $headers = [
            "X-PROJECT-ID:frontend.website.build",
            "Accept:application/json,text/plain,*/*",
            "x-requested-with:XMLHttpRequest",
            "X-AUTH-TIMESTAMP:$timestamp",
            "X-AUTH-NONCE:$nonce",
            "X-AUTH-SIGNATURE:$sign",
        ];
        return $headers;
    }

    protected function getMillisecond(){
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    protected function getRandomStr( $length = 8 ) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str ='';
        for ( $i = 0; $i < $length; $i++ )
        {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    protected function sendPost($url,$data,$headers=[]){

        //'Content-Type:application/x-www-form-urlencoded'
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl,CURLOPT_POST,true);
        curl_setopt($curl,CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30000);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30000);
        $str = curl_exec($curl);

        if(curl_errno($curl)){
            throw new \Exception(curl_error($curl));
        }
        $info = curl_getinfo($curl);
        if($info['http_code'] != 200){
            throw new \Exception($str);
        }
        if(empty($str)){
            throw new \Exception('ERP没有返回内容');
        }
        curl_close($curl);
        return $str;
    }

}
