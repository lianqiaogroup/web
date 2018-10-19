<?php
namespace admin\helper;
use Exception;

class country  extends common {
      public $table ='zone';
      public $table_product ='product';

      public function lists()
      {
           $data=  $this->db->pageSelect($this->table,'*',['parent_id'=>0,'ORDER'=>['id_zone'=>"DESC"]],20);
           if($data['goodsList'])
           {
               $county_id = array_unique(array_column($data['goodsList'],'id_country'));
               //获取国家
               $country = $this->getCountry($county_id);

               //获取货币
               $currency_id =  array_column($data['goodsList'],'currency_id');
               $currency = $this->getCurrency($currency_id);

               //获取子区域
               $zone = $this->getSon(array_column($data['goodsList'],'id_zone'));

               foreach ($data['goodsList'] as $row)
               {
                  $row['country'] = $country[$row['id_country']]['title'];
                  $row['currency'] = $currency[$row['currency_id']]['currency_code'];
                  $row['son'] =   $zone[$row['id_zone']] ;
                  $info[] = $row;
               }
               $data['goodsList'] = $info;
           }
          return $data;
      }


      public function getLists()
      {
          $data=  $this->db->pageSelect('country','*',['ORDER'=>['id_country'=>"DESC"]],20);
          return $data;
      }

      public function getSon($id_zone =[])
      {
          $map =['parent_id'=>$id_zone];
          $ret = $this->db->select('zone',['title','parent_id','id_zone','lang'],$map);
          if($ret)
          {
              foreach ($ret as $value)
              {
                  $parent_id = $value['parent_id'] ;
                  $data[$parent_id][] = $value;
              }

          }
          return $data;
      }


      public function gCountry($id_country)
      {
          $map =['id_country'=>$id_country];
          $ret = $this->db->get('country',['title','id_country','ncode'],$map);
          // if($ret)
          // {
          //     $ret = array_column($ret,NULL,'id_country') ;
          // }
          return $ret;
      }

      public function saveCountry($data)
      {
          if(empty($data) || empty($data['id_country']) || empty($data['ncode'])){
            return ['ret'=>0,'msg'=>'nodata passed'];
          }
          try {
            $ret = $this->db->update('country',['ncode'=>$data['ncode']],['id_country'=>$data['id_country']]);
            return ['ret'=>1];
          } catch (Exception $e) {
            return ['ret'=>0,'msg'=>'更新失败'];
          }
      }

      public function getCountry($id_country)
      {
          $map =['id_country'=>$id_country];
          $ret = $this->db->select('country',['title','id_country'],$map);
          if($ret)
          {
              $ret = array_column($ret,NULL,'id_country') ;
          }

          return $ret;
      }

      public function getCurrency($id_currency)
      {
          $map =['currency_id'=>$id_currency];
          $ret = $this->db->select('currency',['currency_code','currency_id'],$map);
          if($ret)
          {
              $ret = array_column($ret,NULL,'currency_id') ;
          }

          return $ret;
      }

      public function getOne($id)
      {
          return $this->db->get($this->table,'*',['id_zone'=>$id]);
      }

      public function getAllCountry()
      {
          $ret = $this->db->select('country',['title','id_country']);
          return $ret;
      }

      public function getAllCurrency(){
          $ret = $this->db->select('currency',['currency_code','currency_id','currency_title']);
          return $ret;
      }

      public function getAllZone($filter=""){
          $map  = ['parent_id'=>0];
          if($filter)
          {
              $map = $filter;
          }
          $ret = $this->db->select('zone',['id_zone','title','id_country','code','email'],$map);
          return $ret;
      }

      //统一邮箱
      public function unifiedMail($files,$map){
          $this->db->update($this->table_product,$files,$map);
          if(!empty($this->db->error()[2]))throw new Exception();
      }

      //域名一一对应
      public function domain_email($map){
          $link = [];
          $result= $this->db->select($this->table_product,['domain','product_id'],$map);
          if(!empty($this->db->error()[2]))throw new Exception();
          $product  =  array_combine(array_column($result, 'product_id'), array_column($result, 'domain'));
          $domain = array_unique($product);
          $objname = 'admin\helper\api\domain';
          $c = new $objname();

          foreach ($domain as $k=>$v){
              $product_id = array_keys($product,$v);
              $v =substr($v,4);
              $r = $c->getRegionDomain($v);
              if (!$r){
                  $link[] = $v;     //邮箱未进行修改的域名
              } else {
                  $this->db->update($this->table_product,['service_email'=>$r['user_name'].'@'.$v],['product_id'=>$product_id]);
                  if(!empty($this->db->error()[2]))throw new Exception();
              }
          }
          return $link;

      }

      public function update($data,$register){

          $diy_email  = $data['diy_email'];
          $diy_email_2  = $data['diy_email_2'];
          $diy_email_3  = $data['diy_email_3'];
          unset($data['diy_email']);
          unset($data['diy_email_2']);
          unset($data['diy_email_3']);
          $link = [];

          try {

              //更新
              if($data['id'])
              {
                      $map['id_zone']  = $data['id'];
                      unset($data['id']);
                      $ret = $this->db->update($this->table,$data,$map);
                      if(!empty($this->db->error()[2]))throw new Exception();

                      //布谷鸟邮箱
//                      $map['company_id']  = 1;
//                      if($diy_email == 1){
//                          $ret  = $this->unifiedMail(['service_email'=>$data['email']],$map);
//                      }else{
//                          $link = $this->domain_email($map);
//                      }
//
//                      //渡渡鸟邮箱
//                      $map['company_id']  = 8;
//                      if($diy_email_2 == 1){
//                          $ret  = $this->unifiedMail(['service_email'=>$data['dodo_email']],$map);
//                      }else{
//                          $link = $this->domain_email($map);
//                      }
//
//                      //百灵鸟邮箱
//                      $map['company_id']  = 9;
//                      if($diy_email_3 == 1){
//                          $ret  = $this->unifiedMail(['service_email'=>$data['lark_email']],$map);
//                      }else{
//                          $link = $this->domain_email($map);
//                      }
//                      if(!empty($link)) return ['ret'=>1,'msg'=>"请求接口错误，请到域名管理系统查看详情，以下域名未设置成功",'errorList'=>$link];

              }else{
                  unset($data['id']);
                  $ret = $this->db->insert($this->table,$data);
              }

          } catch (Exception $e) {
              /* Recognize mistake and roll back changes */
              return ['ret'=>0,'msg'=>$this->db->error()[2]];
          }

          return ['ret'=>1,'msg'=>"OK",'errorList'=>$link];
      }

      public function delete($id){
          //检查产品是否存在删除地区
          $ret = $this->db->select('product','*',['id_zone'=>$id,'is_del'=>0]);
          if($ret)
          {
              return ['ret'=>0,'msg'=>'禁止删除，存在含有正在删除地区的产品'];
          }
          //检查是否有子区域
          $ret =  $this->db->select('zone','*',['parent_id'=>$id]);
          if($ret)
          {
              return ['ret'=>0,'msg'=>'禁止删除，存在含有正在删除地区的下级区域'];
          }
          //删除
          $this->db->delate('zone',['id_zone'=>$id]);
          return ['ret'=>1,'msg'=>'删除成功'];
      }

      public function syncZone(){
        $objname = 'admin\helper\api\erpzone';
        $obj = new $objname();
        $fail_list = [];
        $zone_list = $obj->getZone();
        if(is_array($zone_list) && (count($zone_list)>1) ){
          try {
            // since use MyISAM and cannnot use Transaction，check before db change
            $_update = $_insert = [];
            foreach ($zone_list as $k => $v) {
              if($ret = $this->db->get('zone',['id_zone'],['title'=>$v['title']])){
                $currency_id = (int)$this->db->get('currency','currency_id',['currency_code'=>$v['currency']]);
                if($currency_id){
                  $_update[] = [$ret['id_zone'],['erp_id_zone'=>$v['id'],'erp_country_id'=>$v['countryId'],'erp_parent_id'=>$v['parentId'],'code'=>$v['code'],'currency'=>$v['currency'],'currency_id'=>$currency_id]];
                }else{
                  $fail_list[] = ['data'=>['erp_id_zone'=>$v['id'],'erp_country_id'=>$v['countryId'],'erp_parent_id'=>$v['parentId'],'code'=>$v['code'],'currency'=>$v['currency'],'currency_id'=>$currency_id],'type'=>'update','id_zone'=>$ret['id_zone'],'msg'=>'请先同步货币--没有货币编码为'.$v['currency'].'对应的货币'];
                  //return ['ret'=>0,'msg'=>'请先同步货币--没有货币编码为'.$v['currency'].'对应的货币'];
                }
              }else{
                if($v['title'] == '港澳台新'){
                  //return true;//不添加 港澳台新  地区
                }else{
                  $currency_id = (int)$this->db->get('currency','currency_id',['currency_code'=>$v['currency']]);
                  if(!$currency_id){
                    $fail_list[] = ['data'=>['erp_id_zone'=>$v['id'],'erp_country_id'=>$v['countryId'],'erp_parent_id'=>$v['parentId'],'code'=>$v['code'],'currency'=>$v['currency'],'currency_id'=>$currency_id],'type'=>'insert','id_zone'=>0,'msg'=>'请先同步货币--没有货币编码为'.$v['currency'].'对应的货币'];
                    //return ['ret'=>0,'msg'=>'请先同步货币--没有货币编码为'.$v['currency'].'对应的货币'];
                    #$currency_id = 1;//默认人名币
                  }else{
                    $parent_id = $id_country = 0;
                    if($v['parentId']){
                      $parent_id = (int)$this->db->get('zone','id_zone',['erp_id_zone'=>$v['parentId']]);
                    }
                    $_insert[] = ['parent_id'=>$parent_id,'id_country'=>$id_country,'erp_id_zone'=>$v['id'],'erp_country_id'=>$v['countryId'],'erp_parent_id'=>$v['parentId'],'code'=>$v['code'],'currency'=>$v['currency'],'title'=>$v['title'],'currency_id'=>$currency_id];
                  }
                  
                }
              }
            }
            if($_update){
              foreach ($_update as $k => $v) {
                $this->db->update('zone',$v[1],['id_zone'=>$v[0]]);
              }
            }
            if($_insert){
              $this->db->insert('zone',$_insert);
            }
            // $this->db->pdo->commit();
            return ['ret'=>1,'msg'=>'同步地区完成','fail_list'=>$fail_list];
          } catch (Exception $e) {
            // $this->db->pdo->rollBack();
            return ['ret'=>0,'msg'=>'地区入库出现错误'];
          }
        }else{
          return ['ret'=>1,'msg'=>'同步地区:接口返回数据为空'];
        }
        
      }

      public function getAllZoneEmail($filter=""){
          $map  = ['parent_id'=>0];
          if($filter)
          {
              $map = $filter;
          }
          $ret = $this->db->select('zone',['id_zone','email'],$map);
          $ret = array_column($ret, 'email','id_zone');
          return $ret;
      }

      public function getZoneEmail($filter=''){
          $map  = ['parent_id'=>0];
          if($filter)
          {
              $map = array_merge($map,$filter);
          }
          $ret = $this->db->get('zone','email',$map);
          $ret = $ret?$ret:'';
          return $ret;
      }

}