<?php

namespace admin\helper;

class productvideo extends common
{
    // 产品视频表
    protected $table = 'product_video';
   
    //产品表
    protected $table_product = 'product';

    // 原图表
    protected $origin_thumb_table = 'product_original_thumb';

    /*
     *  ------------------------------------------- 以下是 产品视频 表的操作 -------------------------------------------
     */

    /**
     * 查询产品对应的视频信息（一对一关系）
     * @param $product_id
     * @return null
     */
    public function getProductVideo($product_id)
    {
        $ret = $this->db->get($this->table, '*', ['product_id' => $product_id]);
        if (empty($ret)) {
            return null;
        } else {
            return $ret;
        }
    }

    /**
     * 保存产品的视频
     * @param $data
     * @return mixed
     */
    public function saveProductVideo($data)
    {

        //查询是否存在该产品的视频
        $map = ['AND' => ['product_id' => $data['product_id']]];
        $ret = $this->db->get($this->table, '*', $map);
        if ($ret['id']) {
            $ret = $this->db->update($this->table, $data, ['id' => $ret['id']]);
            $sql = $this->db->last();
            if($ret){
                //仅成功的日志进入mysql日志
                $log = [];
                $msg = '更新产品视频信息';
                $log['act_table'] = $this->table;
                $log['act_sql'] = $sql;
                $log['act_desc'] = $msg;
                $log['act_time'] = time();
                $log['act_type'] = 'insert_'.$this->table;
                $log['product_id'] = $data['product_id'];
                $log['act_loginid'] = $_SESSION['admin']['login_name'];
                $this->db->insert("product_act_logs", $log);
            }
            return $ret;
        } else {
            $ret = $this->db->insert($this->table, $data);
            $sql = $this->db->last();
            if($ret){
                //仅成功的日志进入mysql日志
                $log = [];
                $msg = '新增产品视频';
                $log['act_table'] = $this->table;
                $log['act_sql'] = $sql;
                $log['act_desc'] = $msg;
                $log['act_time'] = time();
                $log['act_type'] = 'update_'.$this->table;
                $log['product_id'] = $data['product_id'];
                $log['act_loginid'] = $_SESSION['admin']['login_name'];
                $this->db->insert("product_act_logs", $log);
            }
        }
    }


    /**
     * [copyProductVideo 产品图集复制]
     * @param  [type] $originProductID [原始产品ID]
     * @param  [type] $targetProductID [复制目标的产品ID]
     * @return [type]                  [返回目标产品ID信息]
     */
    public function copyProductVideo($originProductID, $targetProductID) 
    {
        $data = $this->db->get($this->table, ['product_id', 'video_url', 'cover_url'], ['product_id' => $originProductID]);
        if (empty($data)) {
            $data = [];
        }
        $data['add_time'] = date('Y-m-d H:i:s');
        $data['product_id'] = $targetProductID;
        return $this->db->insert($this->table, $data);
    }

    /**
     * [deleteCover 删除视频封面]
     * @return [type] [description]
     */
    public function deleteCover($productID)
    {

        $data['cover_url'] = '';
        $this->db->update($this->table, $data, ['product_id' => $productID]);
        $originCoverFile = $this->db->get($this->origin_thumb_table, ['id', 'thumb'], ['product_id' => $productID, 'type'=>7]);
        
        if ($originCoverFile) {
            $picFile = substr(app_path,0,-1) . $originCoverFile['thumb'];
            if (file_exists($picFile )) {
                unlink($picFile);
            }
        }

        if ($originCoverFile) {
            return $this->db->delete($this->origin_thumb_table, ['id'=>$originCoverFile['id']]);
        }
    }

    /**
     * [deleteVideo 删除视频]
     * @return [bool] [true、false]
     */
    public function deleteVideo($productID)
    {
        $data['video_url'] = '';
        return $this->db->update($this->table, $data, ['product_id' => $productID]);
    }
}