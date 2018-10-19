<?php
namespace admin\helper;

class userGroup  extends common {

    public function getAllUserGroup()
    {

        $data=  $this->db->pageSelect('user_group','*',['ORDER'=>['create_at'=>"DESC"]],20);

        return $data;
    }

    public function publicGroup()
    {
        $data=  $this->db->select('user_group','*',['ORDER'=>['create_at'=>"DESC"]]);

        return $data;
    }

    public function getGroupOfId($id){
        $map = ['user_group_id'=>$id];
        $data = $this->db->select('user_group',['title','user_group_id'],$map);
        $data = array_column($data,NULL,'user_group_id');
        return $data;
    }

    public function getOne($uid)
    {
        $ret = $this->db->get('user_group',"*",['user_group_id'=>$uid]);

        return $ret;
    }
    public function saveUserGroup($data=[])
    {
        $id = $data['id'];
        if($id)
        {
            $ret = $this->check($data['title'],$id);
            if(!$ret['ret'])
            {
                return  $ret;
            }
            unset($data['id']);
            $ret = $this->db->update('user_group',$data,['user_group_id'=>$id]);
            if($ret === false)
            {
                return  ['ret'=>0,'msg'=>'更新失败！'.$this->db->last()] ;
            }
            return  ['ret'=>1,'msg'=>'更新成功！'] ;
        }
        else{

            $title = array_column($data,'title');
            $ret = $this->check($title,$id);
            if(!$ret['ret'])
            {
                return  $ret;
            }
            
            $lastId =   $this->db->insert('user_group',$data);
            return ['ret'=>$lastId,'msg'=>'增加成功！'] ;
        }
    }

    public function check($column,$id)
    {
        $map =["AND"=>["title"=>$column,"user_group_id[!]"=>$id]];
        $ret = $this->db->count('user_group',$map);
        if($ret)
         {
              return ['ret'=>0,'msg'=>'FAIL：名称重复'];
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


}