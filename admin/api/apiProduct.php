<?php
/**
 * Created by PhpStorm.
 * User: jimmy
 * Date: 2017/8/22
 * Time: 下午1:52
 */
namespace admin\api;

class apiProduct extends api {

    /*
   * api接口
   * 给mike综合站提供产品图片
   */
    function getProductPhotos($erp_id){

        $map =['erp_number'=>$erp_id,'is_del'=>0,'id_zone'=>2,'ORDER'=>['product_id'=>"DESC"]];

        $product  = $this->db->get('product',['thumb','content','fb_px','product_id'],$map);
        //echo $this->db->last();
        if(!$product)
        {
            return ['ret'=>0,'msg'=>'没有找到该产品'];
        }

        $product_id = $product['product_id'];
        //图集
        $photos = $this->db->select('product_thumb','*',['product_id'=>$product_id]);
        $product['photos'] = $photos;


        return ['ret'=>1,'data'=>$product];
    }
}