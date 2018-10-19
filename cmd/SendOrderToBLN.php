<?php

namespace cmd;

use lib\register;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class SendOrderToBLN extends OrderToBLN
{

    public function __construct($msg = '')
    {
        $this->msg = $msg;
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('SendOrderToBLN');
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
cast((`order`.`payment_amount`/100) as decimal(16,2))  `订单总额`,
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
`order`.`add_time` >= concat(date_sub(curdate(),interval 1 day), ' 00:00:00') and
`order`.`add_time` < concat(CURRENT_DATE, ' 00:00:00') and
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
        return [];
    }

}
