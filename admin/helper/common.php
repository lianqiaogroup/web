<?php

namespace admin\helper;

/**
 * Class common
 * @package admin\helper
 * @property \Medoo\Medoo|\lib\db $db
 * @property \lib\log $log
 */
class common
{
    public $register;
    public $tongji_map;

    public function __construct($register = null)
    {
        $this->register = $register ? $register : \lib\register::getInstance('register');
        $this->tongji_map = new tongji_map();
        if (!$_SESSION['admin']['uid']) {
            //$this->register->get("view")->display('login.twig');
            require_once 'build/login.html';
            exit;
        }
    }

    public function __get($key)
    {
        return $this->register->get($key);
    }

    public function uids(){
        $id_admin = $_SESSION['admin']['is_admin'];
        $company_id = $_SESSION['admin']['company_id'];
        $is_root = $_SESSION['admin']['is_root'];
        $uid = $_SESSION['admin']['uid'];
        $user =  new \admin\helper\oa_users($this->register);
        if($_SESSION['admin']['username'] == 'googleID')
        {
            //如果是谷歌账号
            $uids =$user->getDepartmentUids($_SESSION['admin']['login_name']);
            $uids = array_column($uids,'uid');
            return ['uid'=>$uids,'company_id'=>''];
        }

        if($is_root)
        {
            //如果超管
            return ['uid'=>'','company_id'=>''];
        }
        if($id_admin)
        {
            //那就是系统管理员，只可以看到该公司的产品
            return ['uid'=>'','company_id'=>$company_id];
        }

        // 否则递归找到下级产品

        $data[] = $uid;
        $uuid = $user->getSonUid($uid,$data);

        return ['uid'=>$uuid,'company_id'=>""];
    }

    public function getUids($contain = '1'){
        $id_admin = $_SESSION['admin']['is_admin'];
        $company_id = $_SESSION['admin']['company_id'];
        $is_root = $_SESSION['admin']['is_root'];
        $uid = $_SESSION['admin']['uid'];
        $user =  new \admin\helper\oa_users($this->register);
        if($_SESSION['admin']['username'] == 'googleID')
        {
            //如果是谷歌账号
            $id_department =$user->getDepartmentUids($_SESSION['admin']['login_name']);
            // $uids = array_column($uids,'uid');
            return ['uid'=>[1],'company_id'=>'','id_department'=>$id_department,'is_leader'=>1];
        }

        if($is_root)
        {
            //如果超管
            return ['uid'=>'','company_id'=>''];
        }
        if($id_admin)
        {
            //那就是系统管理员，只可以看到该公司的产品
            return ['uid'=>'','company_id'=>$company_id];
        }

        // 否则 优化： 找到所有下级人员 jade add
        $c = new \admin\helper\company($this->register);
        $data = $c->getUIdAndMemberList($uid,$contain='1');

        $u = $data['data'];
        $is_leader = $data['is_leader'];
        // $data[] = $uid;
        // $uuid = $user->getSonUid($uid,$data);
        return ['uid'=>array_column($u, 'uid'),'company_id'=>$company_id,'ad_member'=>array_column($u, 'ad_member'),'id_department'=>array_unique(array_column($u, 'id_department')),'is_leader'=>$is_leader];
    }

    public function getUidsByUid($uid,$contain = '1'){
        $id_admin = $_SESSION['admin']['is_admin'];
        $company_id = $_SESSION['admin']['company_id'];
        $is_root = $_SESSION['admin']['is_root'];
        $uid = $uid;
        $user =  new \admin\helper\oa_users($this->register);
        if($_SESSION['admin']['username'] == 'googleID')
        {
            //如果是谷歌账号
            $uids =$user->getDepartmentUids($_SESSION['admin']['login_name']);
            $uids = array_column($uids,'uid');
            return ['uid'=>$uids,'company_id'=>''];
        }

        if($is_root)
        {
            //如果超管
            return ['uid'=>'','company_id'=>''];
        }
        if($id_admin)
        {
            //那就是系统管理员，只可以看到该公司的产品
            return ['uid'=>'','company_id'=>$company_id];
        }

        // 否则 优化： 找到所有下级人员 jade add
        $c = new \admin\helper\company($this->register);
        $uuid = $c->getUIdList($uid,$contain='1');
        // $data[] = $uid;
        // $uuid = $user->getSonUid($uid,$data);
        return ['uid'=>$uuid,'company_id'=>$company_id];
    }
    //判断是否有域名和权限
    public function isDoaminPrivate($domain,$id_departments = []){
        $return['code'] = 1;
        $product = new \admin\helper\product($this->register);
        $ret = $product->checkDomain($domain);
        if (!$ret) {
            $return['code'] = 0;
            $return['msg'] = '获取域名信息失败，稍后重试';
            return $return;
        }
        $id_departments = array_merge($id_departments,[$_SESSION['admin']['id_department']]);
        if(($_SESSION['admin']['is_root']!=1) && ($_SESSION['admin']['is_admin']!=1) && (!in_array($ret['erp_department_id'], $id_departments)) ){
            $return['code'] = 0;
            $return['msg'] = '没有权限';
            return $return;
        }
        return $return;
    }




}