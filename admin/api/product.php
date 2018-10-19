<?php
/**
 * Created by PhpStorm.
 * User: jade
 * Date: 2017/11/11
 * Time: 下午1:52
 */
namespace admin\api;

class product extends openapi{

	/**
	 * 消档产品根据erp_id,分部门批量下架
	 *
	 */
	function deleteProduct($data){
		$map = ['erp_number'=>$data['erp_id'],'is_del'=>0,'id_department'=>$data['oldid_department']];
		try {
			$product_ids = $product_sids = [];
			$product_data = $this->db->select('product',['product_id','id_zone'],$map);
			if(empty($product_data)){
				return ['res'=>'succ','data'=>['msg'=>'no product data to delete','finish_time'=>time()]];
			}
			foreach ($product_data as $k => $v) {
				if(in_array($v['id_zone'],[29])){
					$product_sids[] = $v['product_id'];//需求 印度尼西亚-特殊修改
				}else{
					$product_ids[] = $v['product_id'];
				}
			}
			$sql = '';
			$t = time();
			$this->db->pdo->beginTransaction();
			if($product_ids){
				$res = $this->db->update('product',['is_del'=>1,'del_time'=>date("Y-m-d H:i:s")],['AND'=>['product_id'=>$product_ids]]);
				$_sql .= $this->db->last();
				$sql .= $_sql;
				if($res){
			        $log = [];
			        $msg = '下架商品';
			        $log['act_table'] = 'product';
			        $log['act_sql'] = $_sql;
			        $log['act_desc'] = $msg;
			        $log['act_time'] = time();
			        $log['act_type'] = 'api_del_product';
			        $log['act_loginid'] = $data['loginid'];
			        foreach ($product_ids as $pid) {
			        	$log['product_id'] = $pid;
			        	$this->db->insert("product_act_logs", $log);//批量太多数据可能中断
			        }
			        $this->db->insert("product_act_logs", $log);
				}
				//下架的product 关联的套餐也会删除
				$combo_ids = $this->db->select('combo_goods','combo_id',['AND'=>['product_id'=>$product_ids]]);
				if($combo_ids && ($combo_ids = array_filter($combo_ids))){
					$res = $this->db->update('combo',['is_del'=>1],['AND'=>['combo_id'=>$combo_ids]]);
					if($res){
						$sql2 = $this->db->last();
		                $log = [];
		                $msg = '非物理删除包含该产品的套餐';
		                $log['act_table'] = 'combo';
		                $log['act_sql'] = $sql2;
		                $log['act_desc'] = $msg;
		                $log['act_time'] = time();
		                $log['act_type'] = 'del_combos';
		                $log['product_id'] = $pproduct_id;
		                $log['act_loginid'] = $_SESSION['admin']['login_name'];
		                $this->db->insert("product_act_logs", $log);
					}
				}
			}
			if($product_sids){
				$res = $this->db->update('product',['is_del'=>10,'del_time'=>date("Y-m-d H:i:s")],['AND'=>['product_id'=>$product_sids]]);
				$_sql .= $this->db->last();
				$sql .= $_sql;
				if($res){
			        $log = [];
			        $msg = '下架商品--特殊处理';
			        $log['act_table'] = 'product';
			        $log['act_sql'] = $_sql;
			        $log['act_desc'] = $msg;
			        $log['act_time'] = time();
			        $log['act_type'] = 'api_del_product';
			        $log['act_loginid'] = $data['loginid'];
			        foreach ($product_sids as $pid) {
			        	$log['product_id'] = $pid;
			        	$this->db->insert("product_act_logs", $log);//批量太多数据可能中断
			        }
			        $this->db->insert("product_act_logs", $log);
				}
			}
			(new \lib\log())->write('product',print_r($data,1).$sql);
			$this->db->pdo->commit();
			return ['res'=>'succ','data'=>['msg'=>'OK','finish_time'=>time()]];
		} catch (Exception $e) {
			$this->db->pdo->rollBack();
			return ['res'=>'fail','data'=>['msg'=>'update fail',]];
		}
	}

	


	/**
	 * 产品接口，根据路径获取产品信息
	 *
	 */
	function getProductByPath($data){
		// path_list
		#$path_list = json_encode([['www.dzpas.com','213123'],['www.dzpas.com','rr']]);
		$path_list = $data['path_list'];
		$path_list = json_decode($path_list,1);
		if(!is_array($path_list) || count($path_list) <1){
			return ['res'=>'fail','data'=>['msg'=>'json格式错误']];
		}
		$product_list = [];
		foreach ($path_list as $k => $v) {
			$map =['AND'=>['domain'=>$v['0'],'identity_tag'=>$v['1']]];
			$product = $this->db->get('product',['product_id','title','price','market_price','sales','thumb','theme','id_department','domain','identity_tag','is_del','currency','currency_code','discount','lang','ad_member_id','ad_member','add_time','tags','aid','oa_id_department'],$map);
			if($product){
				$product_list[] = $product;
			}
		}
		if($product_list){
			$res = ['res'=>'succ','data'=>['product_list'=>$product_list]];
		}else{
			$res = ['res'=>'fail','data'=>['msg'=>'no data find','product_list'=>[] ]];
		}
		#log
		$sql = $this->db->last();
		(new \lib\log())->write('product',$data['path_list'].' =>>> '.print_r($res,1));
		return $res;
	}

	/**
	 * 产品部门变更
	 *
	 */
	/*function changeDepartment($data){
		$data['domain'] = ['www.dzpas.com'];
		$map =['AND'=>['domain'=>$data['domain'],'is_del'=>0]];
		try {
			$product_ids = $this->db->select('product','product_id',$map);
			if(!is_array($product_ids) || (count($product_ids) <1)){
				return ['res'=>'succ','data'=>['msg'=>'no product data to change department','finish_time'=>time()]];
			}
			$res = $this->db->update('product',['id_department'=>$data['oldid_department']],['AND'=>['product_id'=>$product_ids]]);
			$sql = $this->db->last();
            (new \lib\log())->write('product',print_r($data,1).$sql);
            if($res){
            	//仅成功的日志进入mysql日志
                $sql = $this->db->last();
                $log = [];
                $msg = '更新商品所属部门';
                $log['act_table'] = 'product';
                $log['act_sql'] = $sql;
                $log['act_desc'] = $msg;
                $log['act_time'] = time();
                $log['act_type'] = 'update_product';
                $log['act_loginid'] = $data['loginid'];
                foreach ($product_ids as $pid) {
                	$log['product_id'] = $pid;
                	$this->db->insert("product_act_logs", $log);//批量太多数据可能中断
                }
                $this->db->insert("product_act_logs", $log);
            }
			return ['res'=>'succ','data'=>['msg'=>'OK','finish_time'=>time()]];
		} catch (Exception $e) {
			return ['res'=>'fail','data'=>['msg'=>'update fail',]];
		}
	}*/


	/**
	 * erp产品根据erp_id,分部门批量恢复
	 *
	 */
	/*function recoveryProduct($data){
		$map =['erp_number'=>$data['erp_id'],'is_del[!]'=>0,'id_department'=>$data['oldid_department']];
		try {
			$product_ids = $this->db->select('product','product_id',$map);
			$res  = $this->db->update('product',['is_del'=>0],$map);
			(new \lib\log())->write('product',print_r($data,1).$sql);
            if($res){
            	//仅成功的日志进入mysql日志
                $sql = $this->db->last();
                $log = [];
                $msg = '恢复下架商品';
                $log['act_table'] = 'product';
                $log['act_sql'] = $sql;
                $log['act_desc'] = $msg;
                $log['act_time'] = time();
                $log['act_type'] = 'update_product';
                $log['act_loginid'] = $data['loginid'];
                foreach ($product_ids as $pid) {
                	$log['product_id'] = $pid;
                	$this->db->insert("product_act_logs", $log);//批量太多数据可能中断
                }
            }
			return ['ret'=>'fail','data'=>['msg'=>'OK','finish_time'=>time()]];
		} catch (Exception $e) {
			return ['res'=>'fail','data'=>['msg'=>'update fail',]];
		}
	}*/


}
