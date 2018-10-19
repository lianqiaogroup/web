<?php

namespace admin\helper;

use admin\helper\common;

class combo extends common
{

    public function findCombo($product_id)
    {
        $map['product_id'] = $product_id;
        $map['is_del'] = 0;
        $map['ORDER'] = ['combo_id'=>'DESC'];
        $ret = $this->db->select('combo', "*", $map);
        $combo = [];
        if ($ret) {
            $combo_id = array_column($ret, 'combo_id');
            $comboGoods = $this->getComboGoods($combo_id);

            foreach ($ret as $value) {
                $value['price'] = money_int($value['price'], 'float');
                foreach ($comboGoods as $goods) {
                    if ($goods['combo_id'] == $value['combo_id']) {
                        $goods['promotion_price'] = money_int($goods['promotion_price'], 'float');
                        $value['goodsList'][] = $goods;
                    }
                }
                $combo[] = $value;
            }
        }
        return $combo;
    }

    public function getComboGoods($combo_id)
    {
        $map['combo_id'] = $combo_id;
        $map['ORDER'] = ['combo_goods_id'=>'DESC'];
        $ret = $this->db->select('combo_goods', "*", $map);
        foreach ($ret as &$v) {
            if($v['attr_id_desc']){
                $v['attr_name_desc'] = $this->getAttrNameDesc($v['attr_id_desc']);
            }
        }
        

        return $ret;
    }

    /**
     * 删除套餐、套餐产品列表
     * @param $combo_id
     * @return array
     */
    public function delCombo($combo_id)
    {
        //查询订单中是否存在该套餐
        //存在is_del=1 不存在才删除
        $map['combo_id'] = $combo_id;

        // $ret = $this->db->select('order', ['order_id'], $map);
        // if (!$ret) {
        //     $this->db->delete('combo', $map);
        //     $this->db->delete('combo_goods', $map);
        // } else {
        //     $data['is_del'] = 1;
        //     $this->db->update('combo', $data, $map);
        //     $this->db->update('combo_goods', $data, $map);
        // }
        $data['is_del'] = 1;
        $this->db->update('combo', $data, $map);
        $this->db->update('combo_goods', $data, $map);
        return ['ret' => 1, "msg" => 'OK'];
    }

    public function  delComboGoods($id){
        $map['combo_goods_id'] = $id;
        $comboGoods =  $this->db->get('combo_goods',['combo_id'],$map);
        if(!$comboGoods)
        {
            return ['ret' => 0, "msg" => '删除失败，该套餐下没有此产品'];
        }
        $mapWhere = ['combo_id'=>$comboGoods['combo_id']];
        $count = $this->db->count('combo_goods',$mapWhere);
        if($count == 1)
        {
            return ['ret' => 0, "msg" => '删除失败，套餐至少存在一个产品'];
        }
        $this->db->delete('combo_goods', $map);
        return ['ret' => 1, "msg" => 'OK'];
    }

    function getAttrNameDesc($attr_id_desc){
        $attr_name_desc = '';
        if($attr_id_desc && ($attr_id_desc = json_decode($attr_id_desc,1)) ){
            foreach ($attr_id_desc as $k => $v) {
                $v = array_values($v);
                $attr_name_desc .= $this->getAttrGroupName($k,$v[0]) . ':' . implode(',', $this->getAttrValueNameArr($v)) . ';';
            }
        }
        return $attr_name_desc;
    }

    function getAttrGroupName($attr_group_id,$product_attr_id){
        $res = $this->db->get('product_attr','attr_group_title',['product_attr_id'=>$product_attr_id,'is_del'=>0]);
        return $res;
    }

    function getAttrValueNameArr($product_attr_id){
        $r = $this->db->select('product_attr','name',['product_attr_id'=>$product_attr_id]);
        return $r?$r:[];
    }
}