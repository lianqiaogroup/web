<?php

namespace admin\helper;

use admin\helper\common;

class sms extends common
{
    
    public function getAllIsp($filter =[])
    {
        
        // //if not admin
        // if (!$_SESSION['admin']['is_admin'] && $_SESSION['admin']['username'] !='googleID') {
        //     $and['aid'] = $_SESSION['admin']['uid'];
        // }
        $sql = "SELECT * FROM t_sms_isp WHERE 1=1 ";
        if ($filter) {
            if(!empty($filter['ispname'])){
                $sql.=" AND ispname like '%".$filter['ispname']."%'";
            }
            if(isset($filter['status'])){
                $sql.=" AND status = ".$filter['status'];
            }
        }
        
        $data = $this->db->query($sql)->fetchAll();
        // $data = $this->db->select('t_sms_isp', '*', $map);
        return $data;
    }

    public function getAllAvailIsp()
    {
        $map = [];
        $map['status'] = 1;
        $data = $this->db->select('t_sms_isp', '*', $map);
        return $data;
    }

    public function getIsp($id)
    {
        return $this->db->get('t_sms_isp','*',['id'=>$id]);
    }

    public function getIspName($id)
    {
        return $this->db->get('t_sms_isp','ispname',['id'=>$id]);
    }

    

    //saveIsp
    public function saveIsp($data)
    {
        $r = $this->db->get('t_sms_isp','id',['ispname'=>$data['ispname']]);
        if($r && empty($data['id'])){
            return ['ret'=>0,'msg'=>'名称存在重复！'];
        }
        if(!empty($data['id'])){
            $r = $this->db->update('t_sms_isp', $data, ['id'=>$data['id']]);
            return ['ret'=>1,'msg'=>'finished success!'];
        }else{
            $data['add_time'] = time();
            $r = $this->db->insert('t_sms_isp', $data);
            if($r) return ['ret'=>1,'msg'=>'finished success!'];
            return ['ret'=>0,'msg'=>'保存失败!'];
        }
    }

    function getIspRel($id){
        #$data = $this->db->select('t_isp_state', '*', ['ispid'=>$id]);
        $data = $this->db->query('select l.*,s.ispname,r.title from t_isp_state l left join t_sms_isp s on l.ispid = s.id left join zone r on l.nation = r.id_zone where l.ispid='.$id)->fetchAll();
        return $data;
    }

    /**
     * [checkIspByZone 检查该地区是否已经配置短信]
     * @param  [type] $zone [description]
     * @return [type]       [description]
     */
    function checkIspByZone($zone){
        //$zone = $this->db->quote($zone);
        $data = $this->db->query("SELECT s.id FROM t_isp_state s LEFT JOIN t_sms_isp i ON s.ispid = i.id WHERE nation='$zone' AND i.status = 1 LIMIT 1")->fetchAll();
        if(empty($data[0])){
            return ['ret'=>0];
        }else{
            return ['ret'=>1];
        }
    }

    public function getAllIspState($filter = [])
    {
        $sql = "SELECT l.*,s.ispname,r.title FROM t_isp_state l left join t_sms_isp s on l.ispid = s.id left join zone r on l.nation = r.id_zone WHERE 1=1 ";
        if ($filter) {
            if(!empty($filter['ispname'])){
                $sql.=" AND s.ispname like '%".$filter['ispname']."%'";
            }
            if(!empty($filter['nation'])){
                $sql.=" AND r.title like '%".$filter['nation']."%'";
            }
            if(!empty($filter['styles'])){
                $sql.=" AND l.styles like '%".$filter['styles']."%'";
            }
            if(!empty($filter['ncode'])){
                $sql.=" AND l.ncode like '%".$filter['ncode']."%'";
            }
            if(!empty($filter['prefix'])){
                $sql.=" AND l.prefix like '%".$filter['prefix']."%'";
            }
        }
        $data = $this->db->query($sql)->fetchAll();
        // $data = $this->db->query('select l.*,s.ispname,r.title from t_isp_state l left join t_sms_isp s on l.ispid = s.id left join zone r on l.nation = r.id_zone')->fetchAll();
        return $data;
    }

    public function getIspState($id)
    {
        return $this->db->get('t_isp_state','*',['id'=>$id]);
    }

    //saveIspState
    public function saveIspState($data)
    {
        unset($data['theme']);
        unset($data['use_default']);
        $map = ['ispid'=>$data['ispid'],'nation'=>$data['nation'],'styles'=>$data['styles']];
        $r = $this->db->get('t_isp_state','id',$map);
        if($r && empty($data['id'])){
            return ['ret'=>0,'msg'=>'提供商-地区-模板 存在重复关联！'];
        }
        // 
        //toshow online
        //$data['ncode'] = $this->getNcodeByZoneId($data['nation']);
        if(!empty($data['id'])){
            $r = $this->db->update('t_isp_state', $data, ['id'=>$data['id']]);
            return ['ret'=>1,'msg'=>'finished success!'];
        }else{
            $data['add_time'] = time();
            $r = $this->db->insert('t_isp_state', $data);
            if($r){
                return ['ret'=>1,'msg'=>'finished success!'];
            }
            return ['ret'=>0,'msg'=>'finished failed!'];
        }
    }

    public function getNcodeByZoneId($id)
    {
        $ret = $this->db->get("SELECT ncode FROM country c LEFT JOIN zone z where c.id_country = z.id_country AND z.id_zone = $id")->fetchAll();
        return $ret['ncode']?'':$ret['ncode'];
    }

    public function deleteIspStateById($id)
    {
        return $this->db->delete('t_isp_state', ['id'=>$id]);
    }

    public function deleteIspById($id)
    {
        if($this->db->get('t_isp_state','id',['ispid'=>$id])){
            return ['ret'=>0,'msg'=>'该服务商下还有关联绑定！'];
        }
        $ret = $this->db->delete('t_sms_isp', ['id'=>$id]);
        return ['ret'=>(int)$ret,'msg'=>'finished failed!'];
    }

}
