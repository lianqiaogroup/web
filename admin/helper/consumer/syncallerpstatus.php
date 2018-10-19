<?php
/**
 * Created by PhpStorm.
 * User: jade
 * Date: 2017/8/23
 * Time: 11:12
 */
#use GuzzleHttp\Client;


namespace admin\helper\consumer;
use admin\helper\common;

class syncallerpstatus extends consumerbase {

    function exec(){
        //老ERP 接口，已关闭
        /*
      $list = $this->db->query("SELECT erp_no FROM `order` o LEFT JOIN order_expand e  ON o.order_id = e.order_id AND e.erp_code in(1,2,3,4,5,6,7,17,22,25,26,8,18,27,15)  ")->fetchAll();
      $erpno_list = array_column($list, 'erp_no');
      $t = time();
      $params = ['time'=>$t,'key'=>md5($t.'stosz'),'orderdata'=>json_encode($erpno_list)];
      $log = new \lib\consumerlog();
      $log->write('getOrderStatus',"发送data==> ".print_r($params,true));
      if(environment == 'office'){
        $url = 'http://192.168.109.252:8081/order/api/get_order_status';#test
      }else{
        $url = 'http://erp.stosz.com:9090/order/api/get_order_status';#online 
      }
      $res = $this->sendPost($url,$params);
      $log->write('getOrderStatus',"接收data==> ".print_r($res,true));
      if($res['status']){
            $res = json_decode($res['message'],1);
            if(is_array($res['data']) && !empty($res['data'][0]) ){
                $res = array_column($res['data'],null,'id_increment');
                // if($cache){$cache->set($key,serialize($res),600);}
                ## 访问量大的时候 再启用队列入库
                foreach ($erpno_list as $ep_no) {
                    $sql = "SELECT o.order_id,o.erp_no,oe.order_id as _order_id,oe.erp_code,oe.order_code FROM `order` o LEFT JOIN order_expand oe  on oe.order_id=o.order_id  WHERE o.erp_no = '". $ep_no ."' limit 1";
                    if($obj = $this->db->query($sql)){
                        $value =  $obj->fetchAll();
                        if(!empty($value[0])){
                            $value = $value[0];
                            $_data = [];
                            if(empty($res[$value['erp_no']]['id_order_status'])){
                                $_data['erp_code'] = 1;
                            }else{
                                $_data['erp_code'] = $res[$value['erp_no']]['id_order_status'];
                            }
                            $_data['order_code'] = $this->getOrderCode($_data['erp_code']);
                            if(!empty($res[$value['erp_no']]['shipping_name'])){
                                $_data['shipping_name'] = $res[$value['erp_no']]['shipping_name'];
                            }
                            if(!empty($res[$value['erp_no']]['track_number'])){
                                $_data['track_number'] = $res[$value['erp_no']]['track_number'];
                            }
                            if(!empty($res[$value['erp_no']]['remark'])){
                                $_data['memo'] = $res[$value['erp_no']]['remark'];
                            }
                            if(!empty($res[$value['erp_no']]['date_delivery'])){
                                $_data['date_delivery'] = $res[$value['erp_no']]['date_delivery'];
                            }
                            if(empty($value['_order_id'])){
                                $_data['order_id'] = $value['order_id'];
                                $this->db->insert("order_expand",$_data);//状态入库--新增
                                $log->write('updateOrderStatus',"--lastsql==> ".$this->db->last());
                            }else{
                                if($value['erp_code'] != $_data['erp_code']){
                                    $this->db->update("order_expand",$_data,['order_id'=>$value['order_id']]);
                                    $log->write('updateOrderStatus',"--lastsql==> ".$this->db->last());
                                }
                            }
                        }                        
                    }
                }
                return true;
            }
        }*/
        return false;
    }

    private function getOrderCode($erp_code = '')
    {
        switch ($erp_code) {
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '17':
            case '22':
            case '25':
            case '26':
                return '1';
                break;
            case '8':
            case '18':
            case '27':
                return '2';
                break;
            case '9':
                return '3';
                break;
            case '11':
            case '12':
            case '13':
            case '14':
            case '19':
                return '11';
                break;
            case '15':
                return '21';
                break;
            case '16':
                return '22';
                break;
            case '10':
            case '21':
            case '23':
            case '24':
                return '23';
                break;
            default:
                return '1';
                break;
        }
    }
}