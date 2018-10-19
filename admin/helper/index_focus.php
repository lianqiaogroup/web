<?php

namespace admin\helper;

class index_focus extends common
{

    public $table = 'index_focus';

    public function getAllModule($domain)
    {
        $data = $this->db->pageSelect($this->table, '*', ['domain' => $domain, 'ORDER' => ['domain' => 'ASC', 'sort' => "DESC", 'add_time' => "DESC"]], 10);

        if ($data['goodsList']) {
            $product_id = array_column($data['goodsList'], 'product_id');
            $product = $this->db->select('product', ['title', 'product_id'], ['product_id' => $product_id]);
            $product = array_column($product, NULL, 'product_id');

            foreach ($data['goodsList'] as $value) {

                $value['name'] = $product[$value['product_id']]['title'];
                $ret[] = $value;
            }
            $data['goodsList'] = $ret;
        }

        return $data;
    }

    public function getOneModule($mid)
    {
        $ret = $this->db->get($this->table, "*", ['id' => $mid]);
        $product = $this->db->get("product", ['title'], ['product_id' => $ret['product_id']]);
        $ret['title'] = $product['title'];
        return $ret;
    }

    public function saveModule($mid, $data = [])
    {
        unset($data['id'], $data['act'], $data['title'], $data['upfile']);
        if ($mid) {
            $this->db->update($this->table, $data, ['id' => $mid]);
            return true;
        } else {

            $data['sort'] = !empty($data['sort']) ? $data['sort'] : 0;
            $lastId = $this->db->insert($this->table, $data);
            return $lastId;
        }
    }

    public function checkModule($data)
    {
        $mid = $data['id'];
        $product_id = $data['product_id'];
        $map = ["AND" => ["is_del" => 0, 'product_id' => $product_id, "id[!]" => $mid]];
        $ret = $this->db->get($this->table, '*', $map);
        if ($ret) {
            return ['ret' => 0, 'msg' => 'FAIL：产品重复'];
        }
        return ['ret' => 1, 'msg' => 'OK'];
    }

    public function deleteModule($uid, $data)
    {
        $map = ['id' => $uid];
        $product = $this->db->get($this->table, "*", $map);
        if (!$data['is_del']) {
            $ret = $this->checkModule($product);
            if (!$ret['ret']) {
                return ['ret' => 0, 'msg' => "恢复失败。=》" . $ret['msg']];
            }
        }

        $ret = $this->db->update($this->table, $data, $map);
        if (!$ret) {
            return ['ret' => $ret, 'msg' => "恢复失败。=》数据库更改失败" . $this->db->last()];
        }
        return ['ret' => 1, 'msg' => "OK"];
    }
}
