<?php
namespace admin\helper;

class user  extends common {

    public function getAllUser()
    {

        $data=  $this->db->pageSelect('user','*',['ORDER'=>['add_time'=>"DESC",'uid'=>"DESC"]],20);
        if($data['goodsList'])
        {
            //find userGroup
             $userGroupId = array_unique(array_column($data['goodsList'],'user_group_id'));
            $userGroupModel = new \admin\helper\userGroup($this->register);
            $userGroup = $userGroupModel->getGroupOfId($userGroupId) ;

            //find addUser
            $uid =    array_unique(array_column($data['goodsList'],'add_aid'));
            $user = $this->getUserOfId($uid);

            foreach ($data['goodsList'] as $row){
               $row['add_user'] =  $user[$row['add_aid']]['username'];
               $row['userGroup'] = $userGroup[$row['user_group_id']]['title'];
               $rows[] = $row;
            }
            $data['goodsList'] = $rows;

        }
        return $data;
    }

    public function getUserOfId($id){
        $map = ['uid'=>$id];
        $data = $this->db->select('user',['username','uid'],$map);
        if($data)
        {
            $data = array_column($data,NULL,'uid');
        }
 
        return $data;
    }
    public function getOneUser($uid)
    {
        $ret = $this->db->get('user',"*",['uid'=>$uid]);

        return $ret;
    }
    public function saveUser($uid ,$data=[])
    {
        $ret = $this->checkUser($data['email'],$uid);
        if(!$ret['ret'])
        {
            return  $ret;
        }
        $data['add_aid'] = $_SESSION['admin']['uid'];
        if($uid)
        {
            $this->db->update('user',$data,['uid'=>$uid]);
            return  ['ret'=>1,'msg'=>'更新成功！'] ;
        }
        else{
            $lastId =   $this->db->insert('user',$data);
            return ['ret'=>$lastId,'msg'=>'增加成功！'] ;
        }
    }

    public function checkUser($email,$uid)
    {
         $map =["AND"=>["email"=>$email,"is_del"=>0,"uid[!]"=>$uid]];
         $ret = $this->db->get('user','*',$map);
         if($ret)
         {
              return ['ret'=>0,'msg'=>'FAIL：邮箱重复'];
         }
        return ['ret'=>1,'msg'=>'OK'];
    }

    public  function deleteUser($uid,$data)
    {
        $map = ['uid'=>$uid];
        $product = $this->db->get('user',['email'],$map);
        if(!$data['is_del'])
        {
            $ret = $this->checkUser($product['email'],$uid);
            if(!$ret['ret'])
            {
                return ['ret'=>0,'msg'=>"恢复失败。=》".$ret['msg']];
            }
        }

        $ret = $this->db->update('user',$data,$map);
        if(!$ret)
        {
            return ['ret'=>$ret,'msg'=>"恢复失败。=》数据库更改失败"];
        }

        return ['ret'=>1,'msg'=>"OK"];
    }

    public function updateUserProduct(){
        ///先更新产品
        $aid = $_SESSION['admin']['uid'];
        $map = ['ad_member'=>trim($_SESSION['admin']['username']),'aid[!]'=>$aid];
        $count  = $this->register->get('db')->count('product',$map);
        if($count)
        {
            $product = $this->register->get('db')->select('product',['product_id','domain'],$map);
            $domains = array_unique(array_column($product,'domain'));
            $ret = $this->register->get('db')->update("product",['aid'=>$aid],$map);

            ///
            /// 后更新产品的评论
            $comment['aid'] = $aid;
            $commentMap['product_id'] = array_column($product,'product_id');
            $ret =  $this->register->get('db')->update('product_comment',$comment,$commentMap);
            ///
            ///再更新文章
            $articleMap['domain'] =$domains;
            $article['aid']  = $aid;
            $ret =  $this->register->get('db')->update('article',$article,$articleMap);
            ///
            /// 在更新首页焦点图
            $indexMap['domain'] = $domains;
            $index['aid']  = $aid;
            $ret =  $this->register->get('db')->update('index_focus',$index,$indexMap);

        }

         return ['ret'=>1,'msg'=>'更新完成'];
    }


}