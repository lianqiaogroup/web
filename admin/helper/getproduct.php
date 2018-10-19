<?php

namespace admin\helper;

use lib\controller;

class getproduct extends common
{
    /*
     * 获取每个地区的访问量
     */
    public function gettotal($from,$to,$c,$l = '50')
    {
        $where= '';
        if(!empty($from)){
            $where .= " and add_time >= '$from' ";
        }
        if(!empty($to)){
            $where .= " and add_time <= '$to' ";
        }

        if(!empty($c)){
            $where .= " and post_erp_data like '%currency_code\":\"$c\"%' ";
        }


        $sql = "select product_id,count(1) as c from `order` where 1=1 $where group by product_id order by c desc limit $l";
        $data = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        foreach($data as &$item){
            $msql = "select * from product where product_id = " . $item['product_id'];
            $mdata = $this->db->query($msql)->fetch(\PDO::FETCH_ASSOC);
            $item['domain'] = $mdata['domain'] . '/' . $mdata['identity_tag'];
            $item['title'] = $mdata['title'];
            $item['erp_number'] = $mdata['erp_number'];
            $item['price'] = $mdata['currency_code'].$mdata['price'];
            $item['ctime'] = $mdata['add_time'];

        }
        return $data;
    }

    public function getmonth($id)
    {

        $sql = "select DATE_FORMAT( add_time, '%Y-%m-%d' ) as dayon,count(1) as c from `order` where product_id=$id  group by DATE_FORMAT( add_time, '%Y-%m-%d' )  order by DATE_FORMAT( add_time, '%Y-%m-%d' ) asc";
        $data = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        $msql = "select * from product where product_id = " . $id;
        $mdata = $this->db->query($msql)->fetch(\PDO::FETCH_ASSOC);
        foreach($data as &$item){
            $item['domain'] = $mdata['domain'] . '/' . $mdata['identity_tag'];
            $item['title'] = $mdata['title'];
            $item['erp_number'] = $mdata['erp_number'];
            $item['price'] = $mdata['currency_code'].$mdata['price'];
        }
        return $data;
    }

    public function product_num($type = 'month', $num = 12){
        $date = date("Y-m-d");
        if($type == 'month'){
            $limit = date("Y-m-1", strtotime("-".$num." months"));
            $sql = "select DATE_FORMAT( add_time, '%Y-%m' ) as dayon,count(1) as c from `product` where add_time>'$limit'  group by DATE_FORMAT( add_time, '%Y-%m' )  order by DATE_FORMAT( add_time, '%Y-%m' ) asc limit ".$num;
        }


        if($type == 'day'){
            $limit = date("Y-m-d", strtotime("-".$num." days"));
            $sql = "select DATE_FORMAT( add_time, '%Y-%m-%d' ) as dayon,count(1) as c from `product` where add_time>'$limit'  group by DATE_FORMAT( add_time, '%Y-%m-%d' )  order by DATE_FORMAT( add_time, '%Y-%m-%d' ) asc  limit ".$num;
        }

        $data = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        return $data;
    }

    public function product_chart(){

        $sql = "select product_id,add_time,domain,identity_tag from `product` where is_del=0 ";
        $data = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        $cdate = date("Y-m-d");

        foreach($data as &$item){
            $pid = $item['product_id'];
            $osql = "select count(product_id) as c from `order` where product_id = '$pid' and add_time like '%$cdate%'";
            $lsql = "select add_time from `order` where product_id = '$pid' order by order_id desc";
            $odata = $this->db->query($osql)->fetch(\PDO::FETCH_ASSOC);
            $ldata = $this->db->query($lsql)->fetch(\PDO::FETCH_ASSOC);
            $item['order_count'] = $odata['c'];
            $item['last_date'] = date("Y-m-d",strtotime($ldata['add_time']));
        }

        return $data;
    }

    public function product_chart2(){
        $cdate = date("Y-m-d");
//        $sql = "select p.domain as d,count(1) as c from `product` as p,`order` as o where p.is_del=0 and p.product_id = o.product_id and o.add_time like '%$cdate%' group by p.domain";
        $sql = "select p.domain as d,count(1) as c from `product` as p where p.is_del=0 group by p.domain";
        $data = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        foreach($data as &$item){
            $domain = $item['d'];
            //上新时间
            $osql = "select DATE_FORMAT( add_time, '%Y-%m-%d' ) as addtime from `product` where domain = '$domain' order by add_time desc limit 1";
            $odata = $this->db->query($osql)->fetch(\PDO::FETCH_ASSOC);
            $item['addtime'] = $odata['addtime'];
            //当天订单数
            $osql = "select count(1) as c from `product` as p,`order` as o where p.is_del=0 and p.product_id = o.product_id and p.domain = '$domain' and o.add_time like '%$cdate%'";
            $odata = $this->db->query($osql)->fetch(\PDO::FETCH_ASSOC);
            $item['order_count'] = $odata['c'];
            //最后下单时间
            $osql = "select o.add_time as addtime from `product` as p,`order` as o where p.is_del=0 and p.product_id = o.product_id and p.domain = '$domain' order by o.add_time desc";
            $odata = $this->db->query($osql)->fetch(\PDO::FETCH_ASSOC);
            $item['lasttime'] = $odata['addtime'];
        }

        return $data;
    }

    //排名前50的广告人员，对应的销量前20的产品对应的类目
    public function topproductforads($uid){
        $sql = "select p.erp_number,p.domain,p.identity_tag,p.title,count(1) as c from product as p,`order` as o where p.ad_member_id = $uid and o.product_id = p.product_id group by p.erp_number order by c desc limit 20;";
        $data = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        return $data;
    }

    public function top50ads(){
        $name = array("赵磊","陈仁","刘敬彬","高文博","杨雪","万繁平","谢健锋","张心富","卢伟静","王豫超","朱彩凤","林威","李霓","邓钦中","陈昊全","黄树杰","马钥源","姚磊","刘盛嘉","林朴","吴永安","杨浩","林钦城","郭承卓","徐颜男","张杨杨","罗裕荣","秦艳","杨凤国","陈奋杰","贾茹","陈思","巴雪免","刘帅宏","蒋泥泠","彭金兰","蒋玲萍","李展","黄兰","叶李苏","龚海伦","何依瑾","郑亚飞","董雪明","张岩","冷玉","张闪星","詹翔","刘慧杰","孙耀均","岑健俊","程军菲","吕北媛");
        $uid = array(177,688,205,18,77,98,11,325,105,317,233,259,297,195,193,239,313,484,183,716,514,636,684,247,83,368,197,554,279,556,243,372,263,211,23,564,359,169,624,261,96,63,145,648,130,408,199,592,538,690,400,666,227);

        $alldata = array();
        for($i=0;$i<count($name);$i++){
            $alldata[] = array(
                'name' => $name[$i],
                'uid' => $uid[$i],
                'data' => $this->topproductforads($uid[$i]),
            );
        }

        return $alldata;
    }

}
