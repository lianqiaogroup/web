<?php

namespace admin\helper;

class currency extends common
{
    /**
     * 获取货币列表
     * @return mixed
     */
    public function lists()
    {
        $data = $this->db->pageSelect('currency', '*', ['ORDER' => ['currency_id' => "DESC"]], 20);
        return $data;
    }

    /**
     * 获取单个货币信息
     * @param $id       货币ID
     * @return mixed
     */
    public function getOne($id,$code='')
    {
        if($id){
            return $this->db->get('currency', '*', ['currency_id' => $id]);    
        }else{
            return $this->db->get('currency', '*', ['currency_code' => $code]);
        }
        
    }

    /**
     * 添加货币信息
     * @param $data     货币数据
     * @return mixed
     */
    public function addCurrency($data)
    {
        $ret = $this->db->insert('currency', $data);
        return $ret;
    }

    /**
     * 更新货币信息
     * @param $data     货币数据
     * @param $map
     */
    public function updateCurrency($data, $map)
    {
        $ret = $this->db->update('currency', $data, $map);
        return $ret;
    }

    /**
     * 同步货币信息
     * @param 
     * @param 
     */
    public function syncCurrency()
    {
        $objname = 'admin\helper\api\erpcurrency';
        $obj = new $objname();
        $currency_list = $obj->getCurrency();

        if(is_array($currency_list) && (count($currency_list)>1) ){
            $nameList = [];
            foreach ($currency_list as $value) {
                array_push($nameList, $value['name']);
            }
            try {
                $_update = $_insert = [];
                $sql = "select currency_id,currency_title,currency_code,symbol_left,symbol_right,update_time from `currency` where currency_title in ('" . implode("','", $nameList) . "')";
                $list = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($currency_list as $k => $v) {
                    // 指示是否是新增货币
                    $needle = true;

                    if (is_array($list) && count($list)) {
                        foreach ($list as $k2 => $v2) {
                            if ($v['name'] == $v2['currency_title']) {
                                $needle = false;
                                // 只要有一个不相同就认为要更新
                                if ($v['currencyCode'] != $v2['currency_code'] || 
                                    $v['symbolLeft'] != $v2['symbol_left'] ||
                                    $v['symbolRight'] != $v2['symbol_right']
                                ) {
                                    $_update[] = [
                                                    $v2['currency_id'],
                                                    [
                                                        'currency_title'=>$v['name'],
                                                        'symbol_left'=>$v['symbolLeft'],
                                                        'currency_code'=>$v['currencyCode'],
                                                        'symbol_right'=>$v['symbolRight'],
                                                        'update_time'=>date('Y-m-d H:i:s')
                                                    ],
                                                    [
                                                        'oldCurrencyCode' => $v2['currency_code'], 
                                                        'newCurrencyCode' => $v['currencyCode'], 
                                                    ]
                                                ];
                                }
                                break;
                            } 
                        }
                    }
                    // 
                    if ($needle) {
                        $_insert[] = [
                                        'currency_title' => $v['name'],
                                        'currency_code'  => $v['currencyCode'],
                                        'symbol_left'    => $v['symbolLeft'],
                                        'symbol_right'   => $v['symbolRight']
                                    ];
                    }
                }

                // 开启事务
                $this->db->pdo->beginTransaction(); 
                if($_update){
                    foreach ($_update as $ku => $vu) {
                        $productUpdateTmp = [];

                        $this->db->update('currency',$vu[1],['currency_id'=>$vu[0]]);
                        
                        $productUpdateTmp['currency']        = $vu[2]['newCurrencyCode'];
                        $productUpdateTmp['currency_prefix'] = 0;
                        $productUpdateTmp['currency_code']   = $vu[1]['symbol_right'];
                        if ($vu[1]['symbol_left']) {
                            $productUpdateTmp['currency_prefix'] = 1;
                            $productUpdateTmp['currency_code']   = $vu[1]['symbol_left'];
                        }

                        $this->db->update(
                                            'product', 
                                            $productUpdateTmp, 
                                            ['currency' => $vu[2]['oldCurrencyCode']]
                                        );
                    }
                }
                if($_insert){
                    $this->db->insert('currency',$_insert);
                }
                $this->db->pdo->commit();
                return ['ret'=>1,'msg'=>'同步货币完成'];
            } catch (Exception $e) {
                $this->db->pdo->rollBack();
                return ['ret'=>0,'msg'=>'货币入库出现错误'];
            }
        }else{
          return ['ret'=>1,'msg'=>'同步货币:接口返回数据为空'];
        }
    }
    
}