<?php

namespace admin\helper\consumer;
/*
 * jimmy
 * 短信渠道监控
 * 每隔半小时查询两小时内的订单发送情况
 */
class monitorIsp extends consumerbase
{

    const zone = [
        2 => ['name' => '台湾', 'rate' => 70],
        11 => ['name' => '泰国', 'rate' => 60],
    ];
    const isp = [
        2 =>'Paasoo',
        4=>'Nexmo',
        6=>'云片',
        8=>'云之讯',
    ];

    public function exec()
    {
        $date  = time();
        $preTime = date('Y-m-d H:i', strtotime('-2 hour'));
        $time = date('Y-m-d H:i',$date);

        $failedZone = [];
        $title ='';
        foreach (self::zone as $key => $zones) {
            //获取全部
            $sql = "select t.isp,count(1) as c from `order` o left join t_sms_order t on t.order_id = o.order_id left join product p on o.product_id = p.product_id where o.add_time  between  '$preTime' and '$time'  and p.id_zone = $key and p.is_open_sms =1  and o.name not like '%测试%' and o.name not like '%test%'  group by t.isp";

            $ob = $this->db->query($sql);
            $count = $ob->fetchAll();
            $ispCount = array_sum(array_column($count, 'c'));
            echo $sql;

            //获取发送成功的
            $sql = "select t.isp,count(1) as c from `order` o left join t_sms_order t on t.order_id = o.order_id left join product p on o.product_id = p.product_id where o.add_time  between  '$preTime' and '$time'  and t.status=1 and p.id_zone = $key and p.is_open_sms =1 and o.name not like '%测试%' and o.name not like '%test%' group by t.isp";

            $ob = $this->db->query($sql);
            $successCount = $ob->fetchAll();
            $ispSuccessCount = array_sum(array_column($successCount, 'c'));
            $successCount = array_column($successCount, NULL, 'isp');

            if ($ispCount) {
                //计算成功率
                $rate_success = floor(($ispSuccessCount / $ispCount) * 100);
                if ($rate_success > $zones['rate']) {
                    continue;
                }
                $title .= $zones['name'] .' ';
                //组装要发送的数据
                foreach ($count as $c) {
                    if($c['isp']){
                        $row['date'] = $preTime.'-'.$time;
                        $row['zone']  = $zones['name'];
                        $row['isp'] = self::isp[$c['isp']];
                        $row['count'] = $c['c'];
                        $row['successCount'] = $successCount[$c['isp']]['c'] ?:0;
                        $row['successRate'] =  floor(($row['successCount'] / $row['count']) * 100).'%';
                        $failedZone[] = $row;
                    }
                }
            }
        }
        if($failedZone){
            $content =  $this->make_table($failedZone);
            require app_path.'lib/mail.php';
            (new \lib\Mail)->sendIspWarming($title,$content);
        }
    }


    public function make_table($data)
    {
        $str = '<table align="center" border="1" cellpadding="0" cellspacing="0"><tr><th>日期</th><th>地区</th><th>渠道</th><th>发送条数</th><th>验证成功条数</th><th>成功率</th></tr>';
        foreach ($data as $val){
            $str .="<tr><td>{$val['date']}</td><td width='100' align='center'>{$val['zone']}</td><td width='100' align='center'>{$val['isp']}</td><td width='100' align='center'>{$val['count']}</td><td width='100' align='center'>{$val['successCount']}</td><td width='100' align='center'>{$val['successRate']}</td></tr>";

        }

        $str .='</table>';
        return $str;
    }
}