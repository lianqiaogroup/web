<?php
namespace admin\helper;

class api extends common {

    public function getApiErpList($filter = []){
    	return $this->db->select('erp_api','*',$filter);
    }

    public function getApiErp($id){
    	return $this->db->get('erp_api','*',['id'=>$id]);
    }

    public function saveApiErp($data){
    	if($data['id']){
    		try {
    			$this->db->update('erp_api',$data,['id'=>$data['id']]);
                return ['ret'=>1];
    		} catch (Exception $e) {
    			return ['ret'=>0];
    		}
    	}else{
    		$ret = $this->db->insert('erp_api',$data);
    		if($ret) return ['ret'=>1];
    		return ['ret'=>0];
    	}
    }

    public function deleteApiErp($id){
    	if($id){
    		$ret = $this->db->delete('erp_api',['id'=>$id]);
    		if($ret) return ['ret'=>1];
    	}
    	return ['ret'=>0];
    }
}