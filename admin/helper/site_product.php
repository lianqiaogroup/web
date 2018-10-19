<?php

namespace admin\helper;

class site_product extends common
{
    public $table = 'site_products';

    /**
     * 查询站点设置的的所有产品
     * @param $domain
     * @param $page
     * @return mixed
     */
    public function getAllSiteProduct($domain, $page)
    {
        $pageSize = 20;
        $start = $page <= 0 ? 0:(($page-1)*$pageSize);
        //$filter = ['LIMIT'=>[$start, $pageSize]];

        $map = ['domain' => $domain, 'ORDER' => ['domain' => 'ASC', 'sort' => "DESC", 'add_time' => "DESC"]];
        //$map = array_merge($map, $filter);

        $data = $this->db->pageSelect($this->table, '*', $map, $pageSize);
        if ($data) {
            $product_ids = array_column($data['goodsList'], 'product_id');
            $product = $this->db->select('product', ['title', 'product_id'], ['product_id' => $product_ids]);
            $product    = array_column($product, NULL, 'product_id');
            foreach ($data['goodsList'] as $key=>$value) {
                $value['title'] = $product[$value['product_id']]['title'];
                $value['thumb'] =  \admin\helper\qiniu::get_image_path($value['thumb']);
                $ret[] = $value;
            }
        }
        $data['pageCount'] = ceil($data['count'] / $pageSize);
        $data['goodsList'] = $ret;
        
        return $data;
    }

    /**
     * 查询产品是否存在
     * @param $sid
     * @return mixed
     */
    public function getOneProduct($sid)
    {
        $ret          = $this->db->get($this->table, "*", ['is_del'=>0, 'id' => $sid]);
        $product      = $this->db->get("product", ['product_id'], ['product_id' => $ret['product_id']]);

        if ($ret['product_id'] == $product['product_id']) {
            return $ret;
        } else {
            return [];
        }
    }

    /**
     * 查询产品是否存在
     * @param $sid
     * @return mixed
     */
    public function existsProduct($sid)
    {
        $ret          = $this->db->get($this->table, "*", ['id' => $sid]);
        $product      = $this->db->get("product", ['title'], ['product_id' => $ret['product_id']]);
        $title        = $product['title'];
        return $title;
    }

    /**
     * 如果有则更新 如果没有则添加
     * @param $sid
     * @param array $data
     * @return mixed
     */
    public function saveSiteProduct($sid, $data = [])
    {
        if ($sid) {
            unset($data['domain']);
            $res = $this->db->update($this->table, $data, ['AND'=>['id'=>$sid]]);
            return $res;
        } else {

            //查询是否已经存在了改产品
            $ret = $this->db->get($this->table, '*', ['product_id'=>$data['product_id']]);
            if (!$ret) {
                $data['sort'] = !empty($data['sort']) ? $data['sort'] : 0;
                $lastID = $this->db->insert($this->table, $data);
                return $lastID;
            } else {
                return 0;
            }
        }
    }

    /**
     * 检查是否有相同的产品
     * @param $data
     * @return array
     */
    public function checkModule($data)
    {
        $mid = $data['id'];
        $product_id = $data['product_id'];
        $map = ["AND" => ["is_del" => 0, 'product_id' => $product_id, "id[!]" => $mid]];
        $ret = $this->db->get($this->table, '*', $map);
        if ($ret) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 假删除产品
     * @param $sid
     * @param $data
     * @return array
     */
    public function deleteSiteProduct($sid, $data)
    {
        $map = ['id' => $sid];
        $product = $this->db->get($this->table, "*", $map);
        //当产品还没有删除 检查是有个相同已删除的产品
        if (!$data['is_del']) {
            $ret = $this->checkModule($product);
            if (!$ret) {
                return ['ret' => 0, 'msg' => "恢复失败->产品重复了"];
            }
        }
        $ret = $this->db->update($this->table, $data, $map);
        if (!$ret) {
            return ['ret' => 0, 'msg' => "恢复失败->数据库更改失败" . $this->db->last()];
        }
        return ['ret' => 1, 'msg' => "OK"];
    }
}