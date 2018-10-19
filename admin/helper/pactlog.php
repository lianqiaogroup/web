<?php

namespace admin\helper;

use admin\helper\common;

class pactlog extends common
{
    
    public function find($filter =[])
    {
        #$data = $this->db->select('product_act_logs','*',$filter);
        #return $data;
        $joinCondition = ['[>]order_expand'=>['order_id'=>'order_id'],'[>]product'=>['product_id'=>'product_id']];
        $fields = ['order.order_id','order.order_no','order.erp_no','order.product_id','order.num','order.title',
                    'order.name','order.last_name','order.mobile', 'order.erp_status','order.address',
                    'order.payment_amount','order.pay_type','order.add_time', 'order_expand.postal',
                    'order_expand.memo','product.domain', 'product.id_zone'];
        $joinCondition = ['[>]oa_users'=>['act_loginid'=>'username']];
        $fields = ['product_act_logs.act_id','product_act_logs.product_id','product_act_logs.act_loginid','product_act_logs.act_table','product_act_logs.act_type','product_act_logs.act_sql','product_act_logs.act_desc','product_act_logs.act_time','oa_users.name_cn(act_namecn)'];
        $data = $this->db->select('product_act_logs', $joinCondition,$fields,$filter);
        foreach ($data as $k => &$v) {
            $v['act_time'] = date('Y-m-d H:i:s',$v['act_time']);
            $v['act_desc'] = str_replace("<img", "<-img",$v['act_desc']);
        }
        return $data;
        // $data['goodsList'] = $this->register->get('db')->select('order', $joinCondition,$fields,$filter);
        // //查询订单，计算订单总数
        // $count = $this->register->get('db')->count("order",['[>]product'=>['product_id'=>'product_id']],['order.order_id'],$map);
    }
}
