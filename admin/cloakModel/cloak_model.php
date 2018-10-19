<?php
/**
 * Created by PhpStorm.
 * User: yangpan
 * Date: 2018/3/17
 * Time: 11:00
 */

namespace admin\cloakModel;


class cloak_model
{
    public $register;
    public $db;

    public function __construct($register,$db)
    {
        $this->register = $register;
        $this->db  = $db;
    }

    //输出
    public function encode($str){
        $json = json_encode($str);
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', create_function('$matches', 'return iconv("UCS-2BE","UTF-8",pack("H*", $matches[1]));'), $json);
    }

    //验证站点
    public function web_verification($map){
        if(strpos($map,'/')!==false)
        {
            list($map,$tag) = explode('/',$map);
            $filter['identity_tag'] = $tag;
            $filter['domain'] = $map;
            return $this->get_productId($filter);
        }
        return false;
    }

    //获取产品ID
    public  function get_productId($filter){
        return   $this->db->get("product",'product_id',$filter);
    }

    //查询cloak
    public function get_cloak($filter){
        return   $this->db->get('cloak', '*', $filter);
    }

    //查询cloak
    public function select_cloak($filter){
        $filter['ORDER'] = ['create_at' => "DESC"];
        return   $this->db->pageSelect('cloak', '*', $filter, 20);
    }

    //统计cloak
    public function cloak($filter){
        return   $this->db->count("cloak",'id',$filter);
    }

    //更新cloak
    public function update_cloak($data,$filter){
        return   $this->db->update("cloak",$data,$filter);
    }

    //插入cloak数据
    public function insert_cloak($data){
        return   $this->db->insert("cloak",$data);
    }

    public function get_zone($filter)
    {
      return    $this->db->get("product",['[>]zone'=>['id_zone'=>'id_zone']],'zone.title',$filter);

    }


}