<?php

namespace admin\helper;

class comment extends common
{
    public function index()
    {
        $map['ORDER'] =  ['comment_id' => "DESC"];
        $uid = $this->getUids();
        $maps=[];
        if($uid['company_id'])
        {
            $maps['company_id'] = $uid['company_id'];
        }
        if($uid['uid']){
            $maps['aid'] = $uid['uid'];
        }
        if($maps){
            $map['and'] = $maps;
        }
        $data = $this->db->pageSelect('product_comment', '*', $map, 20);
        if ($data['goodsList']) {
            $product_id = array_unique(array_column($data['goodsList'], 'product_id'));
            $product = $this->getProduct($product_id);
            foreach ($data['goodsList'] as $value) {
                $value['title'] = $product[$value['product_id']]['title'];
                $ret[] = $value;
            }
            $data['goodsList'] = $ret;
        }

        return $data;
    }

    public function search($filter){
        $map['ORDER'] =  ['comment_id' => "DESC"];
        $uid = $this->uids();
        if($uid['company_id'])
        {
            $map['product_comment.company_id'] = $uid['company_id'];
        }elseif($uid['uid']){
            $map['product_comment.aid'] = $uid['uid'];
        }
        if ($filter) {
            $map = array_merge($map, $filter);
        }
        $column =['product_comment.comment_id','product_comment.name','product_comment.content','product_comment.is_anonymous','product_comment.is_del','product_comment.star','product_comment.product_id','product_comment.add_time'];
        $data = $this->db->tableJoinPage('product_comment', ['[>]product'=>['product_id'=>'product_id']],$column, $map, 20);

        if ($data['goodsList']) {
            $product_id = array_unique(array_column($data['goodsList'], 'product_id'));
            $product = $this->getProduct($product_id);
            foreach ($data['goodsList'] as $value) {
                $value['title'] = $product[$value['product_id']]['title'];
                $ret[] = $value;
            }
            $data['goodsList'] = $ret;
        }

        return $data;
    }

    public function getProduct($product_id)
    {
        $map['product_id'] = $product_id;
        $data = $this->db->select("product", ['currency_code', 'thumb', 'product_id', 'title'], $map);
        $data = array_column($data, NULL, 'product_id');
        return $data;
    }

    public function deleteUser($uid, $data)
    {
        $map = ['comment_id' => $uid];
        $ret = $this->db->update('product_comment', $data, $map);
        if (!$ret) {
            return ['ret' => $ret, 'msg' => "失败=》数据库更改失败"];
        }

        return ['ret' => 1, 'msg' => "OK"];
    }

    public function getComment($comment_id)
    {
        $map['comment_id'] = $comment_id;
        $comment = $this->db->get('product_comment', '*', $map);
        if ($comment) {
            $thumbs = $this->db->select('product_comment_thumb', '*', $map);
            $comment['thumbs'] = $thumbs;
        }

        return $comment;
    }

    /**
     * 导入评论数据
     * @param $comment_data
     * @return int
     */
    public function saveComment($comment_data)
    {
        if ($comment_data && !empty($comment_data['product_id'])) {
            $product_id = $comment_data['product_id'];

            //查询产品id
            $map['product_id'] = $product_id;
            $map['is_del'] = 0;
            $data = $this->db->get('product', ['product_id'], $map);

            //存在该产品 评论插入
            if ($data['product_id']) {
                $ret['is_anonymous'] = intval($comment_data['is_anonymous']);
                $ret['name']    = $ret['is_anonymous'] == 1 ? '' : $comment_data['name'];
                $ret['star']    = intval($comment_data['star']);
                $ret['content'] = $comment_data['content'];
                $ret['aid']     = intval($comment_data['aid']);
                $ret['product_id'] = $data['product_id'];
                $ret['add_time'] = $comment_data['add_time'];

                //插入评论表
                $result = $this->db->insert("product_comment", $ret);
                if ($result) {
                    return 1;
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        }
    }
}