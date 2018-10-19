<?php

namespace admin\helper;

class stsiteModel extends common
{
    /**
     * 获取软文站点列表
     * @return mixed
     */
    public function index($filter = [])
    {
        $uid = $this->getUids();
        
        if ($uid['uid']) {
            $filter['uid'] = $uid['uid'];
        }

        if ($uid['id_department']) {
            $filter['oa_id_department'] = $uid['id_department'];
        }

        if ($uid['company_id']) {
            $filter['company_id'] = $uid['company_id'];
        }

        if ($uid['is_leader']) unset($filter['uid']);
        
        $filter['ORDER'] =  ['id' => "DESC"];
         
        $data = $this->db->pageSelect('st_site_basic', '*', $filter, 20);
        if ($data['goodsList']) {
            $oa_id_department = array_unique(array_column($data['goodsList'], 'oa_id_department'));
            $oa_uids = array_unique(array_column($data['goodsList'], 'uid'));
            $oa_model = new \admin\helper\oa_users($this->register);
            $oa = $oa_model->get_department(['id_department'=>$oa_id_department]);
            $oaNameCn = $oa_model->get_ucn_name(['uid'=>$oa_uids]);
            $oa  =array_column($oa,NULL,'id_department');
            $oaNameCn = array_column($oaNameCn,NULL,'uid');

            $language = [
                    'tw' => '繁体中文',
                    'en' => '英文',
                    'ch' => '简体中文',
                    'tha' => '泰文',
                    'cn' => '简体中文'
                ];
            foreach ($data['goodsList'] as $value) {
                // $value['thumb'] = qiniu::get_image_path($value['thumb']);
                $value['department'] = $oa[$value['oa_id_department']]['department'];
                $value['uid_name_cn'] = $oaNameCn[$value['uid']]['name_cn'];
                $value['language'] = $language[$value['language']];
                $ret[] = $value;
            }
            $data['goodsList'] = $ret;
        }

        return $data;

    }


           

     
     /**
      * [getStsiteInfo 根据站点ID查询站点所有信息]
      * @param  [type] $stsiteID [description]
      * @return [type]           [description]
      */
    public function getStsiteInfo($stsiteID)
    {
        $res = false;
        if(is_numeric($stsiteID)){
            $res = $this->db->get('st_site_basic', '*', ['id' => $stsiteID]);    
        }
        return $res;
    }

    /**
     * [insertComment 插入多条评论]
     * @param  array  $commentArray [description]
     * @return [type]               [description]
     */
    public function insertComment($commentArray = [])
    {
        $status = false;
        if (count($commentArray)) {
            $status = $this->db->insert('st_site_comments',$commentArray);
        }
        return $status;
    }

    /**
     * [updateComment description]
     * @param  array  $commentArray [description]
     * @return [type]               [description]
     */
    public function updateComment($commentArray, $stsiteID)
    {
        $status = false;

        $delStatus = $this->db->delete('st_site_comments', ['st_site_id' => $stsiteID]);
        if (count($commentArray)) {
            $status = $this->db->insert('st_site_comments',$commentArray);
        }
        return $status  && $delStatus;

    }


    /**
     * [insertStsiteBasic 插入stsite_basic表]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function insertStsiteBasic($data)
    {
        return $this->db->insert('st_site_basic',$data);
    }

    /**
     * [updateStsiteBasic 更新stsite_basic基本库]
     * @param  array  $data  [description]
     * @param  array  $where [description]
     * @return [type]        [description]
     */
    public function updateStsiteBasic($data = [], $where = [])
    {
        return $this->db->update('st_site_basic', $data, $where);
    }

    /**
     * [getStsiteByDomainAndTag 查重复]
     * @param  string $domain [description]
     * @param  [type] $tag    [description]
     * @return [type]         [description]
     */
    public function getStsiteByDomainAndTag($domain='', $tag)
    {
        $where = ['domain'=> $domain, 'identify_tag' => $tag];
        return $this->db->select('st_site_basic', '*', $where);
    }


    /**
     * [getAllCommentsBySiteID description]
     * @param  integer $stsiteID [description]
     * @return [type]            [description]
     */
    public function getAllCommentsBySiteID($stsiteID = 0){
        $where = ['st_site_id'=> $stsiteID];
        return $this->db->select('st_site_comments', ['id', 'comment_name', 'comment_sex'], $where);
    }

    public function updateSingleComment($name, $id){
        $where = ['id' => $id];
        $data = ['comment_name' => $name];
        return $this->db->update('st_site_comments', $data, $where);

    }

    
}