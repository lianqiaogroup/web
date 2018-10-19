<?php

namespace admin\helper;

class article extends common
{
    public $table = 'article';

    public function getAllArticle($domain)
    {
        // 检查域名权限
        $company = new \admin\helper\company($register);
        $id_departments = $company->get_id_departments();
        $ret = $this->isDoaminPrivate($domain, $id_departments);
        if(!$ret['code']){
            return [];
        }
        $data = $this->db->pageSelect($this->table, '*', ['domain' => $domain, 'ORDER' => ['domain' => 'ASC', 'sort' => "DESC", 'article_id' => "DESC"]], 20);
        return $data;
    }

    public function getOneArticle($article_id)
    {
        $ret = $this->db->get($this->table, "*", ['article_id' => $article_id]);

        return $ret;
    }

    public function saveArticle($data = [])
    {
        // 检查域名权限
        $company = new \admin\helper\company($register);
        $id_departments = $company->get_id_departments();
        $ret = $this->isDoaminPrivate($data['domain'], $id_departments);
        if(!$ret['code']){
            return ['ret' => 0, 'msg' => '没有权限！'];
        }
        if(substr($data['domain'], 0,4) != 'www.'){
            $data['domain'] = 'www.'.$data['domain'];
        }
        $article_id = $data['article_id'];
        if ($article_id) {
            $this->db->update($this->table, $data, ['article_id' => $article_id]);
            return ['ret' => 1, 'msg' => '更新成功！'];
        } else {
            $lastId = $this->db->insert($this->table, $data);
            return ['ret' => $lastId, 'msg' => '增加成功！'];
        }
    }

    public function deleteArticle($article_id, $data)
    {
        $map = ['article_id' => $article_id];

        $ret = $this->db->update($this->table, $data, $map);
        if (!$ret) {
            return ['ret' => $ret, 'msg' => "恢复失败。=》数据库更改失败"];
        }
        return ['ret' => 1, 'msg' => "OK"];
    }

    public function sort($article_id = [], $sort = [])
    {
        foreach ($article_id as $key => $row) {
            $data['sort'] = $sort[$key];
            $map = ['article_id' => $row];
            $this->db->update($this->table, $data, $map);
        }
        return ['ret' => 1, 'msg' => "OK"];
    }

}
