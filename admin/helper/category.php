<?php

namespace admin\helper;

class category extends common
{
    //分类表
    protected $table = 'category';
    //产品和分类ID联级表
    protected $table_product_category = 'product_category';
    //产品表
    protected $table_product = 'product';

    /*
     *  ------------------------------------------- 以下是 category 表的操作 -------------------------------------------
     */

    /**
     * 查询产品分类的ID title
     * @param $product_id
     * @return null
     */
    public function getProductCategory($product_id)
    {
        $ret = $this->db->get($this->table_product_category, '*', ['product_id' => $product_id]);
        if (empty($ret)) {
            return null;
        } else {
            $category_id = $ret['category_id'];
            return $this->getOneFullCategory($category_id);
        }
    }

    /**
     * 保存产品分类
     * @param $data
     * @return mixed
     */
    public function saveProductCategory($data)
    {
        //查询是否存在该产品的分类
        $map = ['AND' => ['product_id' => $data['product_id']]];
        $ret = $this->db->get($this->table_product_category, '*', $map);
        if ($ret['id']) {
            $ret = $this->db->update($this->table_product_category, $data, ['id' => $ret['id']]);
            $sql = $this->db->last();
            if($ret){
                //仅成功的日志进入mysql日志
                $log = [];
                $msg = '更新产品关联分类';
                $log['act_table'] = $this->table_product_category;
                $log['act_sql'] = $sql;
                $log['act_desc'] = $msg;
                $log['act_time'] = time();
                $log['act_type'] = 'insert_'.$this->table_product_category;
                $log['product_id'] = $data['product_id'];
                $log['act_loginid'] = $_SESSION['admin']['login_name'];
                $this->db->insert("product_act_logs", $log);
            }
            return $ret;
        } else {
            $ret = $this->db->insert($this->table_product_category, $data);
            $sql = $this->db->last();
            if($ret){
                //仅成功的日志进入mysql日志
                $log = [];
                $msg = '新增产品关联分类';
                $log['act_table'] = $this->table_product_category;
                $log['act_sql'] = $sql;
                $log['act_desc'] = $msg;
                $log['act_time'] = time();
                $log['act_type'] = 'update_'.$this->table_product_category;
                $log['product_id'] = $data['product_id'];
                $log['act_loginid'] = $_SESSION['admin']['login_name'];
                $this->db->insert("product_act_logs", $log);
            }
        }
    }


    public function getProductListsWithCategoryID($category_id, $domain)
    {
        $ret = $this->db->select($this->table_product_category, ['product_id'], ['category_id' => $category_id]);
        if (empty($ret)) {
            return [];
        } else {
            $product_ids = array_column($ret, 'product_id');
            $map = ['domain' => $domain, 'product_id' => $product_ids, 'is_del' => 0];
            $field = ['product_id', 'title', 'theme', 'domain', 'thumb'];
            $ret = $this->db->select($this->table_product, $field, $map);
            return $ret;
        }
    }


    /*
     *  ------------------------------------------- 以下是 category 表的操作 -------------------------------------------
     */

    /**
     * @param $domain
     * @return array|null
     */
    public function getCategory($domain)
    {
        if (empty($domain)) {
            return null;
        } else {
            $map = ['parent_id' => 0, 'is_del' => 0, 'domain' => $domain, 'ORDER' => ['sort' => 'DESC']];
            $ret = $this->db->select($this->table, ['id'], $map);
            $ids = array_column($ret, 'id');
            return $ids;
        }
    }

    /**
     * @param $domain
     * @return array|null
     */
    public function getCategoryParent($domain)
    {
        if (empty($domain)) {
            return null;
        } else {
            $map = ['is_del' => 0, 'domain' => $domain, 'ORDER' => ['sort' => 'DESC']];
            $ret = $this->db->select($this->table, ['id', 'title', 'title_zh', 'parent_id'], $map);
            return $ret;
        }
    }

    /**
     * 根据id查询分类数据 不管是否删除
     * @param $cid
     * @return mixed
     */
    public function getOneFullCategory($cid)
    {
        $ret = $this->db->get($this->table, '*', ['id' => $cid]);
        return $ret;
    }

    /**
     * 获取单个分类数据
     * @param $cid
     * @return mixed
     */
    public function getOneCategory($cid)
    {
        $ret = $this->db->get($this->table, "*", ['is_del' => 0, 'id' => $cid]);
        $parent_ids = $this->getCategory($ret['domain']);

        return ['data' => $ret, 'parent_id' => $parent_ids];
    }

    /**
     * 获取所有分类数据
     * @param $domain 域名
     * @param $page   分页数
     * @return mixed
     */
    public function getAllCategory($domain, $page)
    {
        $map = ['domain' => $domain, 'ORDER' => ['domain' => 'ASC', 'sort' => "DESC", 'add_time' => "DESC"]];
        $data = $this->db->select($this->table, '*', $map);
        return $data;
    }

    /**
     * 保存分类
     * @param $cid   如果是保存则不存在 如果是更新则存在
     * @param $data  数据
     * @return mixed
     */
    public function saveCategory($cid, $data)
    {
        if ($cid) {
            unset($data['domain']);
            //更新前先判断当前分类下是否有子分类 存在的话不允许改变
            $is_exists = $this->db->has($this->table, ['parent_id' => $cid]);
            if ($is_exists) {
                $param['title_zh'] = $data['title_zh'];
                $param['sort']     = $data['sort'];
                $param['title']    = $data['title'];
                $lastID = $this->db->update($this->table, $param, ['id' => $cid]);
            } else {
                $lastID = $this->db->update($this->table, $data, ['id' => $cid]);
            }
        } else {
            $data['sort'] = !empty($data['sort']) ? $data['sort'] : 0;
            $lastID = $this->db->insert($this->table, $data);
        }

        if ($lastID) {
            return ['ret' => 1, 'msg' => '修改分类数据成功', 'data' => $data];
        } else {
            return ['ret' => 0, 'msg' => '修改分类失败'];
        }
    }

    /**
     * 删除分类数据
     * @param $cid
     * @param array $data
     * @return array
     */
    public function deleteCategory($cid, $data = [])
    {
        $map = ['id' => $cid];
        $category = $this->db->get($this->table, "*", $map);

        //当分类还没有删除 检查是有个相同已删除的分类
        if (!$data['is_del']) {
            $ret = $this->checkModule($category);
            if (!$ret) {
                return ['ret' => 0, 'msg' => "恢复失败->分类重复了"];
                exit;
            }
        }

        $ret = $this->db->update($this->table, $data, $map);
        if (!$ret) {
            return ['ret' => 0, 'msg' => "恢复失败->数据库更改失败" . $this->db->last()];
        }
        return ['ret' => 1, 'msg' => "OK"];
    }

    /**
     * 检查是否有相同的分类
     * @param $data
     * @return array
     */
    public function checkModule($data)
    {
        $map = ["AND" => ["is_del[!]" => $data['is_del'], 'title' => $data['title']]];
        $ret = $this->db->get($this->table, '*', $map);
        if ($ret) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $product_id
     * @param  $domain
     * return array
     * 分类树形结构
     */
     public function getTreeCat($product_id,$domain){
         $ret_category = $module = [];
         if($product_id){
             $ret_category = $this->getProductCategory($product_id);
         }
         $modules = $this->getCategoryParent($domain);

         if($modules)
         {
             foreach ($modules as $r) {
                 $r['selected'] = $r['id'] == $ret_category['id'] ? 'selected' : '';
                 $r['spa']      = '&emsp;&emsp;';
                 $array[]       = $r;
             }
             $str = "<option value='\$id' \$selected>\$spacer \$title \$spa\$spa 中文名:\$title_zh</option>";
             $T   = new \lib\tree();
             $T->init($array);

             $module = $T->getTree(0, $str);
         }

         return $module;
     }
}