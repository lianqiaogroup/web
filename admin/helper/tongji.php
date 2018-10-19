<?php

namespace admin\helper;

use lib\controller;

class tongji extends common
{
    /*
     * 获取每个地区的访问量
     */
    public function getZoneData($v_date =null,$v_day_num=0,$v_time_type=0)
    {
        $map = $this->tongji_map->mapUV($v_date, $v_day_num, $v_time_type);
        $sql = "select country,count(*) as count from web_uv where $map and country !='' group by country order by count desc limit 10";
        $data = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        return $data;
    }

    //获取设备
    public function getDevice($v_date =null,$v_day_num=0,$v_time_type=0){
        $map = $this->tongji_map->mapUV($v_date, $v_day_num, $v_time_type);
        $sql = "select os,count(*) as count from web_uv where $map and os !=''  group by os";
        $data = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        return $data;
    }

    /**
     * 获取每天的总访问量
     * @return mixed
     */
    public function getAllVisited()
    {
        $map['visit_time[>]'] = date('Y-m-d 00:00:00', time());
        $count = $this->db->count('web_uv', $map);
        return $count;
    }

    /**
     * 获取转化率
     * 总下单人数/总访问人数
     * @return float|int
     */
    public function getConversionPer()
    {
        //下单人数
        $map['erp_status'] = 'SUCCESS';
        $map['add_time[>]'] = date('Y-m-d 00:00:00', time());

        $orderCount = $this->db->count('order', $map);
        $uvMap['visit_time[>]'] = date('Y-m-d 00:00:00', time());

        $uvCount = $this->db->count('web_uv', $uvMap);

        $data = round(($uvCount / $orderCount)) / 100;
        return $data;
    }

    //获取所有域名数据
    public function getAllHost($v_date=null,$v_day_num=null,$v_time_type=null,$host=null)
    {
        $map = $this->tongji_map->mapPV($v_date, $v_day_num, $v_time_type);
        if($host)$map .=" and host = '".$host."' ";
        $pvsql ="select min(first_visit_time) as t_min,host,count(distinct uuid) as u_count,sum(total_visit_time)/count(distinct uuid) as t_average,group_concat(distinct product_id) as product_id from web_pv where $map group by host order by u_count desc limit 10";
        $data  =$this->db->query($pvsql)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($data as $key => &$value) {
            $oMap = $this->tongji_map->mapOrder($v_date, $v_day_num, $v_time_type);
            if($value['product_id'])$oMap .= "and product_id in(".$value['product_id'].") ";
            $osql="select count(1) as o_count from `order` where $oMap";

            $odata  =$this->db->query($osql)->fetch(\PDO::FETCH_ASSOC);
            $value['o_count']=$odata['o_count'];
            $value['conversion']=$value['o_count']*100/$value['u_count'];
            $value['t_average']=round($value['t_average']/1000,2);
        }
        return $data;
    }

    //获取所有产品
    public function getAllProduct($v_date=null,$v_day_num=null,$v_time_type=null,$product_id=null)
    {
        $map = $this->tongji_map->mapPV($v_date, $v_day_num, $v_time_type);
        if($product_id)$map .= " and product_id = '".$product_id."' ";
        $map .= ' and product_id>0 ';
        $pvsql ="select min(first_visit_time) as t_min,product_id,title,count(distinct uuid) as u_count,sum(total_visit_time)/count(distinct uuid) as t_average from web_pv where $map  group by product_id order by u_count desc limit 10";
        $data  =$this->db->query($pvsql)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($data as $key => &$value) {
            $oMap = $this->tongji_map->mapOrder($v_date, $v_day_num, $v_time_type);
            if($value['product_id'])$oMap .= "and product_id =".$value['product_id']." ";
            $osql ="select count(1) as o_count,sum(payment_amount) as o_p_sum,count(distinct mobile) as o_u_count from `order` where $oMap";

            $odata  =$this->db->query($osql)->fetch(\PDO::FETCH_ASSOC);
            $value['o_count']=$odata['o_count'];
            $value['conversion']=$value['o_count']*100/$value['u_count'];
            $value['t_average']=round($value['t_average']/1000,2);
            $value['o_p_sum']=$odata['o_p_sum']/100;
            $value['o_u_count']=$odata['o_u_count'];
        }
        return $data;
    }


    /**
     * 查询用户访客量
     * @param $v_date
     * @param $v_day_num
     * @param $v_time_type
     * @return array
     */
    public function getUsersVisited($v_date = null, $v_day_num = 0, $v_time_type = 0)
    {
        //构建请求条件
        $map = $this->tongji_map->mapUV($v_date, $v_day_num, $v_time_type);
        if (!$map) {
            return 0;
        }

        $sql = "select count(*) quantity from `web_uv` where " . $map;
        $quantity = $this->db->query($sql)->fetch(\PDO::FETCH_ASSOC);

        return intval($quantity['quantity']);
    }

    /**
     * 查询订单量
     * @param $v_date
     * @param $v_day_num
     * @param $v_time_type
     * @return array
     */
    public function getOrderQuantity($v_date = null, $v_day_num = 0, $v_time_type = 0)
    {
        //构建请求条件
        $map = $this->tongji_map->mapOrder($v_date, $v_day_num, $v_time_type);
        if (!$map) {
            return 0;
        }

        $sql = 'select add_time from `order` where ' . $map;
        $orders = $this->db->query($sql)->fetchAll(\PDO::FETCH_COLUMN);

        if ($orders) {
            $start_time = null; //开始时间
            $end_time = null;   //结束时间
            switch ($v_time_type) {
                case 1: {
                    $start_time = '00:00:00';
                    $end_time = '08:00:00';
                }
                    break;
                case 2: {
                    $start_time = '08:00:00';
                    $end_time = '16:00:00';
                }
                    break;
                case 3: {
                    $start_time = '16:00:00';
                    $end_time = '24:00:00';
                }
                    break;
                default: {
                    $start_time = '00:00:00';
                    $end_time = '24:00:00';
                }
                    break;
            }

            $quantity = 0;
            //循环筛选时间间隔数据
            foreach ($orders as $key => $val) {
                $add_time = date('H:i:s', strtotime($val));
                if ($add_time >= $start_time && $add_time <= $end_time) {
                    $quantity++;
                }
            }
            return $quantity;
        } else {
            return 0;
        }
    }


    /**
     * 平均停留时间
     * @param $v_date
     * @param $v_day_num
     * @param $v_time_type
     * @return array
     */
    public function averageStayTime($v_date = null, $v_day_num = 0, $v_time_type = 0)
    {
        //查询一天的数据
        $map = $this->tongji_map->mapPV($v_date, $v_day_num, $v_time_type);
        if (!$map) {
            return 0;
        }

        $sumSql = 'select sum(total_visit_time) sum from `web_pv` where ' . $map;
        $visited = $this->getUsersVisited($v_date, $v_day_num, $v_time_type);

        $sum = $this->db->query($sumSql)->fetch(\PDO::FETCH_ASSOC);

        $tongji_calculate = new tongji_calculate();

        //计算平均停留时间
        $average = $tongji_calculate->userStayAverageTime($sum['sum'], $visited);

        return $average;
    }

    /**
     * 查询用户访客量
     * 列出每个时间段的访客量
     * @param $v_date
     * @param $v_day_num
     * @param $v_time_type
     * @return array
     */
    public function getUsersVisitedGroupTimeType($v_date = null, $v_day_num = 0, $v_time_type = 0)
    {
        //构建请求条件
        $map = $this->tongji_map->mapUV($v_date, $v_day_num, $v_time_type);
        if (!$map) {
            return 0;
        }

        $sql = "select visit_time from `web_uv` where " . $map;
        $res = $this->db->query($sql)->fetchAll(\PDO::FETCH_COLUMN);

        $data = $this->calQtyGroupTimeType($res);
        return ['time_type'=>$v_time_type, 'data'=>$data];
    }

    /**
     * 查询订单量
     * 列出每个时间段的订单量
     * @param $v_date
     * @param $v_day_num
     * @param $v_time_type
     * @return array
     */
    public function getOrderQuantityGroupTimeType($v_date = null, $v_day_num = 0, $v_time_type = 0)
    {
        //构建请求条件
        $map = $this->tongji_map->mapOrder($v_date, $v_day_num, $v_time_type);
        if (!$map) {
            return 0;
        }

        $sql = 'select add_time from `order` where ' . $map;
        $res = $this->db->query($sql)->fetchAll(\PDO::FETCH_COLUMN);

        $data = $this->calQtyGroupTimeType($res);
        return ['time_type'=>$v_time_type, 'data'=>$data];
    }
    private function calQtyGroupTimeType($data)
    {
        $lists = array();
        foreach ($data as $key=>$val) {
            $time = date('H:i:s', strtotime($val));
            array_push($lists, $time);
        }
        return $lists;
    }
}