<?php
namespace admin\helper;

class oa_users  extends common
{

    public function getAllUser($filter =[])
    {
        if($filter){
            $map = $filter;
        }
        $map['ORDER'] = ['uid'=>"DESC"];

        $data=  $this->db->pageSelect('oa_users','*',$map,20);
        unset($data['pageHtml']);
        return $data;
    }


    public function getSonUid($uid,$uids=[]){
        $user = $this->db->select('oa_users',['uid'],['manager_id'=>$uid]);
        if($user)
        {
            $uuid = array_column($user,'uid');
            $uids = array_merge($uids,$uuid);
            return $this->getSonUid($uuid,$uids);
        }
        return $uids;
    }


    public function getUserOfId($id)
    {
        $map = ['uid'=>$id];
        $data = $this->db->select('oa_users',['username','uid'],$map);
        if($data)
        {
            $data = array_column($data,NULL,'uid');
        }
 
        return $data;
    }


    public function getOneUser($uid,$name='')
    {
        if($uid)
        {
            $map['uid'] = $uid;
        }
        if($name)
        {
            $map['name_cn'] = $name;

        }
        $map['password[!]']  ='';
        $map['id_department[!]']  = 0;
        $ret = $this->db->get('oa_users',"*",$map);

        return $ret;
    }


    public function saveUser($uid ,$data=[])
    {
        if($_SESSION['admin']['company_id'] ==1)
        {
            return  ['ret'=>0,'msg'=>'不允许的操作！'] ;
        }
        $ret = $this->checkUser($data['email'],$uid);
        if(!$ret['ret'])
        {
            return  $ret;
        }
        if($uid)
        {
            $this->db->update('oa_users',$data,['uid'=>$uid]);
            return  ['ret'=>1,'msg'=>'更新成功！'] ;
        }
        else{
            $lastId = $this->getUserLastId();
            if($lastId)
            {
                $data['uid'] = $lastId;
                $ret = $this->db->insert('oa_users',$data);
                if(!$ret)
                {
                  //  echo $this->db->last();
                    return  ['ret'=>0,'msg'=>'保存失败！'] ;
                }

                return ['ret'=>$lastId,'msg'=>'增加成功！'] ;
            }
        }
    }


    public function  getUserLastId()
    {

        $uid = $this->db->get('oa_users',['uid'],['ORDER'=>['uid'=>"DESC"]]);
        if($uid['uid'])
        {
            $uuid = $uid['uid'];
            if($uuid <100000)
            {
                return 100000;
            }
            return $uuid+1;
        }

        return false;

    }


    public function checkUser($email,$uid)
    {
         $map =["AND"=>["email"=>$email,"is_del"=>0,"uid[!]"=>$uid]];
         $ret = $this->db->get('oa_users','*',$map);
         if($ret)
         {
              return ['ret'=>0,'msg'=>'FAIL：邮箱重复'];
         }
        return ['ret'=>1,'msg'=>'OK'];
    }


    public  function deleteUser($uid,$data)
    {
        if($_SESSION['admin']['company_id'] ==1)
        {
            return  ['ret'=>0,'msg'=>'不允许的操作！'] ;
        }
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

    public  function  getDepartmentUids($name)
    {
        switch ($name)
        {
            case "googleID1":$id_department =[41,50,51,52,53,56,57,58,59,60,183,189];break;
            case "googleID2":$id_department =[43,62,63,64,65,66,67,68,69,101,181,182,188,510,511,512,513];break;//2部新增部门
            case "googleID3":$id_department =[45,190,72,70,71,91,187,186];break;
            case "googleID4":$id_department =[74,75,76,184,185];break;
            case "googleID5":$id_department =[78];break;
            case "googleID6":$id_department =[81];break;
        }

      //  $uids = $this->db->select('oa_users',['uid'],['id_department'=>$id_department]);
        return $id_department;
    }


    public function getUsernameByNameCn($name_cn, $type = false)
    {
        if($name_cn){
            $map = ['AND'=>['name_cn'=>$name_cn,'username[!]'=>'','password[!]'=>'']];
            if ($type) {
                $ret = $this->db->get('oa_users',['username', 'password','id_department'],$map);
            }else{
                $ret = $this->db->get('oa_users','username', $map);
            }
            return $ret;
        }else{
            return '';
        }
        
    }

    public function getUsernameByUid($uid)
    {
        if($uid){
            $map = ['AND'=>['uid'=>$uid]];
            $ret = $this->db->get('oa_users','username',$map);
            return $ret;
        }else{
            return '';
        }
        
    }


    public function getAdminList($filter = []){
        $page = isset($_GET['p']) ? (int)$_GET['p']:1;
        $page = ($page<=0)?1:$page;
        $pageSize = 20;
        $start =   ($page - 1)* $pageSize;
        $filter = array_merge($filter,['id_department'=>[7,402,472,473,474,475,462,461] ]);//限制 技术部
        $limit = ['LIMIT'=>[$start,$pageSize]];
        $filters = array_merge($limit,$filter);
        $data['goodsList'] = $this->db->select('oa_users',['uid','username','name_cn','id_department','department','is_admin','is_root'],$filters);
        $data['goodsList'] = empty($data['goodsList'])?[]:$data['goodsList'];
        $data['page'] = $data['p'] = $page;
        $count = $this->db->count("oa_users",'uid',$filter);
        $data['total_p'] = ceil ( $count / $pageSize );
        $data['count'] = $count;
        $data['pageCount'] = count($data['goodsList']);
        $data['pageHtml'] = $this->db->Pagebarht(['p'=>$page],$pageSize,$page,$count);
        if($data){
            return ['res'=>'succ','data'=>['admin_list'=>$data]];
        }else{
            return ['res'=>'fail','data'=>['admin_list'=>[],'msg'=>'no data found']];
        }
    }


    public function adminSave($data)
    {
        try {
            $username_arr = explode(',', $data['username']);
            $ret = $this->db->update('oa_users',['is_admin'=>$data['is_admin']],['username'=>$username_arr]);
            #log
            $sql = $this->db->last();
            $this->log->write('basic', $sql.'=>1'.$ret);
            $log = [];
            $msg = '管理员更新';
            $log['loginid'] = $data['username'];
            // $log['name_cn'] = $data['name_cn'];
            $log['act_sql'] = $sql;
            if(empty($data['is_admin'])){
                $act = '取消';
                $log['act_type'] = 'del_admin';
            }else{
                $act = '新增';
                $log['act_type'] = 'add_admin';
            }
            $log['act_table'] = 'admin_logs';
            $act = empty($data['is_admin'])?'取消':'新增';
            $log['act_desc'] = $_SESSION['admin']['login_name'].' 对'.$data['username'].$act.'管理员权限';
            $log['act_loginid'] = $_SESSION['admin']['login_name'];
            $log['act_time'] = time();
            $this->db->insert("admin_logs", $log);
            return ['res'=>'succ','data'=>['msg'=>'']];
        } catch (Exception $e) {
            return ['res'=>'fail','data'=>['msg'=>'update fail']];
        }
    }


    public function selectAdmin()
    {
        $map = ['is_admin'=>0,'id_department'=>[7,402,472,473,474]];//进一步限制 技术部才可以是管理员
        $ret = $this->db->select('oa_users',['uid','username','name_cn','id_department','department'],$map);
        if($ret){
            return ['res'=>'succ','data'=>['admin_list'=>$ret]];
        }else{
            return ['res'=>'fail','data'=>['admin_list'=>[],'msg'=>'no data found']];
        }
    }


    public function adminLog($filter)
    {
        if(!empty($filter['username'])){
            unset($filter['name_cn']);
        }else{
            if(!empty($filter['name_cn'])){
                $map = ['AND'=>['name_cn'=>$filter['name_cn'],'username[!]'=>'']];
                $filter['username'] = $this->db->get('oa_users','username',$map);
            }
            unset($filter['name_cn']);
        }
        $ret = $this->db->select('admin_logs','*',$filter);

        return $ret;
    }


    public function set_admin($uid,$is_root=0)
    {
        if($_SESSION['admin']['is_root'] !=1){
            return ['ret'=>0,'msg'=>'没有权限操作'];
        }

        $data['is_root'] =$is_root;
        $map['uid'] = $uid;
        $ret = $this->db->update('oa_users',$data,$map);
        return ['ret'=>1];
    }


    public function getAllDepartment()
    {
        $department = $this->db->query("SELECT id_department,department FROM oa_users WHERE department!='' AND id_department!=0 AND username!=''");
        if(!$department || !$department = $department->fetchAll()){
            return [];
        }
        return $department;
    }


    public function  get_department($filter)
    {
        $data=  $this->db->select('oa_users',['department','id_department'],$filter);

        return $data;
    }

    public function get_ucn_name($filter)
    {
        $data=  $this->db->select('oa_users',['uid','username', 'name_cn'],$filter);

        return $data;
    }

    /**
     * 根据oa用户id
     * 查询用户的中文名
     * @param array $filter
     * @return mixed
     */
    public function getUserNameWithID($filter = [])
    {
        $data = $this->db->get('oa_users', ['uid','name_cn'], $filter);

        return $data;
    }

}