<?php
namespace admin\helper;

class payment extends common {

    public function getPayment()
    {
        $data = $this->db->pageSelect('domain_payment', '*', ['ORDER' => ['payment_id' => "DESC"]], 20);
        if($data['goodsList'])
        {
            $list =[];
            foreach ($data['goodsList'] as $goods){
                $goods['data'] = unserialize($goods['data']);
                $list[] = $goods;
            }
            $data['goodsList'] = $list;
        }
        return $data['goodsList'];
    }
    public function getOnePay($id)
    {

        $map['payment_id'] = $id;
        return $this->db->get('domain_payment',"*",$map);
    }

    public function save($data,$payConfig){
           //read config filed
           $filed = $payConfig[$data['code']]['field'];
           foreach ($filed as  $key=>$v)
           {
               $f[$key] = $data[$key];
           }
            $value = serialize($f);
            $info['data'] = $value;
            $info['code'] = $data['code'];
            $info['title'] = $payConfig[$data['code']]['title'];
            $info['domain'] = $data['domain'];
            $info['aid'] = $_SESSION['admin']['uid'];
            if($data['id'])
            {
                //编辑
                $map=['payment_id'=>$data['id']];
                $this->db->update('domain_payment',$info,$map);

            } else{
                //新增
                $this->db->insert('domain_payment',$info);
        }
        return ['ret'=>1,'msg'=>'OK'];
    }

    public function supportOcean($domain,$payment_code = ''){
		
		$map = ['domain'=>$domain];
		if(!empty($payment_code)){
			$map['code'] = $payment_code;
		}
		
        $ret = $this->db->get('domain_payment',"*",$map);
        if($ret)
        {
            return true;
        }

        return false;

    }

}