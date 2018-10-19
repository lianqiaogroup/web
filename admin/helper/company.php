<?php
namespace admin\helper;

class company extends common {

    public function  getInfo(){

            $info = $this->db->get('company',"*",['company_id'=>$_SESSION['admin']['company_id']]);
            return $info;
    }

    function getUIdList($uid,$contain='1'){
        $obj = $this->db->query("SELECT uid,username,id_department,department,find_in_set(".$uid.",path) AS i FROM oa_users  HAVING i>0  and username != ''   and id_department !=0  and department !='' ");
        if(!$obj) {
            if($contain){
                return [$uid];
            }
            return NULL;
        } else {
            $res = $obj->fetchAll();
            if(!$res){
                if($contain){
                    return [$uid];
                }
                return NULL;
            }else{
                $res = array_column($res, 'uid');
                if($contain){
                    $res[] = $uid;
                }
                return $res;
            }
        }
    }

    function getUIdAndMemberList($uid,$contain='1'){
        //fix 改造返回是否是领导，如果是领导直接按照部门查更快！
        $obj = $this->db->query("SELECT uid,name_cn,id_department,department,username as ad_member,find_in_set(".$uid.",path) AS i FROM oa_users WHERE `password`!='' HAVING i>0  and username != '' and id_department !=0 and department !='';");
        if(!$obj){
            if($contain){
                $data = $this->db->select('oa_users',['uid','name_cn(ad_member)','id_department'],['uid'=>$uid]);
                return ['is_leader'=>0,'data'=>$data];
            }
            return NULL;
        }else{
            $res = $obj->fetchAll();
            if(!$res){ //如果没有数据，则说明是个小喽喽
                if($contain){
                    $data = $this->db->select('oa_users',['uid','name_cn(ad_member)','id_department'],['uid'=>$uid]);
                    return ['is_leader'=>0,'data'=>$data];
                }
                return NULL;
            }else{
                //有数据说明是个大人物，我曹 惹不起
                if($contain){
                    $res[] = $this->db->get('oa_users',['uid','name_cn(ad_member)','id_department'],['uid'=>$uid]);
                }
                return ['is_leader'=>1,'data'=>$res];
            }
        }
    }
    

    function getAidList($uid,$contain ='1'){
        $obj = $this->db->query("SELECT uid aid,username,id_department,department,find_in_set(".$uid.",path) AS i FROM oa_users  HAVING i>0  and username != ''   and id_department !=0  and department !='' ");
        if(!$obj){
            if($contain){
                return [$uid];
            }
            return NULL;
        }else{
            $res = $obj->fetchAll();
            if(!$res){
                if($contain){
                    return [$uid];
                }
                return NULL;
            }else{
                $res = array_column($res, 'aid');
                if($contain){
                    $res[] = $uid;
                }
                return $res;
            }
        }
    }

    function getAUser($uid,$contain ='1'){
        //manager_id
        $obj = $this->db->query("SELECT uid aid,username,password,name_cn name,id_department,department,manager_id,find_in_set(".$uid.",path) AS i FROM oa_users  HAVING i>0 and username != '' and password != '' and id_department !=0  and department !='' ");
        if(!$obj){
            if($contain){
                return $this->db->select('oa_users',['uid(aid)','username','name_cn(name)','id_department','department','manager_id'],['uid'=>$uid]);
            }
            return NULL;
        }else{
            $res = $obj->fetchAll();
            if(!$res){
                if($contain){
                    return $this->db->select('oa_users',['uid(aid)','username','name_cn(name)','id_department','department','manager_id'],['uid'=>$uid]);
                }
                return NULL;
            }else{
                if($contain){
                    $res[] = $this->db->get('oa_users',['uid(aid)','username','name_cn(name)','id_department','department','manager_id'],['uid'=>$uid]);
                }
                return $res;
            }
        }
    }

    function getAdUser($uid,$contain ='1'){
        //manager_id
        $obj = $this->db->query("SELECT uid ad_member_id,username,password,name_cn name,id_department,department,manager_id,find_in_set(".$uid.",path) AS i FROM oa_users  HAVING i>0  and username != '' and password != ''  and id_department !=0  and department !='' ");
        if(!$obj){
            if($contain){
                return $this->db->select('oa_users',['uid(ad_member_id)','username','name_cn(name)','id_department','department','manager_id'],['uid'=>$uid]);
            }
            return NULL;
        }else{
            $res = $obj->fetchAll();
            if(!$res){
                if($contain){
                    return $this->db->select('oa_users',['uid(ad_member_id)','username','name_cn(name)','id_department','department','manager_id'],['id_department'=>$_SESSION['admin']['id_department'], 'password[!]'=>'']);

                }
                return NULL;
            }else{
                if($contain){
                    $res[] = $this->db->get('oa_users',['uid(ad_member_id)','username','name_cn(name)','id_department','department','manager_id'],['uid'=>$uid]);
                }
                return $res;
            }
        }
    }

    function getErpProductObjname(){
        $res = $this->db->query("SELECT * FROM erp_api e LEFT JOIN company c ON e.id = c.product_erp_api WHERE c.company_id = ".$_SESSION['admin']['company_id'] ." LIMIT 1");
        if(!$res) return '';
        $res = $res->fetchAll();
        return $res[0]['classname'];
    }

    function getErpDomainObjname(){
        $res = $this->db->query("SELECT * FROM erp_api e LEFT JOIN company c ON e.id = c.domain_erp_api WHERE c.company_id = ".$_SESSION['admin']['company_id'] ." LIMIT 1");
        if(!$res) return '';
        $res = $res->fetchAll();
        return $res[0]['classname'];
    }

    function getErpOrderObjname(){
        $res = $this->db->query("SELECT * FROM erp_api e LEFT JOIN company c ON e.id = c.order_erp_api WHERE c.company_id = ".$_SESSION['admin']['company_id'] ." LIMIT 1");
        if(!$res) return '';
        $res = $res->fetchAll();
        return $res[0]['classname'];
    }

    function getErpSeoObjname(){
        $res = $this->db->query("SELECT * FROM erp_api e LEFT JOIN company c ON e.id = c.seo_erp_api WHERE c.company_id = ".$_SESSION['admin']['company_id'] ." LIMIT 1");
        if(!$res) return '';
        $res = $res->fetchAll();
        return $res[0]['classname'];
    }

    

    public function getCompanylist($filter = []){
        return $this->db->select('company','*',$filter);
        // $obj = $this->db->query("SELECT c.*, FROM company c LEFT JOIN erp_api e WHERE c.");
        // return $this->db->query('company','*',$filter);
        // $list = $this->db->select('company','*',$filter);
    }

    public function getCompany($company_id){
        return $this->db->get('company','*',['company_id'=>$company_id]);
    }

    public function saveCompany($data){
        if($data['company_id']){
            try {
                $this->db->update('company',$data,['company_id'=>$data['company_id']]);
                return ['ret'=>1];
            } catch (Exception $e) {
                return ['ret'=>0];
            }
        }else{
            $ret = $this->db->insert('company',$data);
            if($ret) return ['ret'=>1];
            return ['ret'=>0];
        }
    }

    public function deleteCompany($company_id){
        if($company_id){
            $ret = $this->db->delete('company',['company_id'=>$company_id]);
            if($ret) return ['ret'=>1];
        }
        return ['ret'=>0];
    }

    public function get_id_departments(){
        $uid = $_SESSION['admin']['uid'];
        $obj = $this->db->query("SELECT id_department,username,find_in_set(".$uid.",path) AS i FROM oa_users WHERE `password`!='' HAVING i>0  and username != '';");
        if(!$obj){
           return [$_SESSION['admin']['id_department']];
        }else{
            $res = $obj->fetchAll();
            if(!$res){
                $res = [$_SESSION['admin']['id_department']];
                return $res;
            }else{
                $res = array_unique(array_column($res, 'id_department'));
                return $res;
            }

        }

    }

    public function getDepartmentUsers($id_department){
        $ret = [];
        if($id_department){
            $ret = $this->db->select('oa_users',['uid','username','name_cn'],['id_department'=>$id_department,'username[!]'=>'','department[!]'=>'']);
            if(!$ret) return [];
        }
        return $ret;
    }

    

}