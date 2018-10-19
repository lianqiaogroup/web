<?php

namespace admin\helper;

//use function PHPSTORM_META\map;

class site extends common
{
    /**
     * 获取当前人的域名<通过添加的商品>
     * @return mixed
     */
    public function getAllDomain()
    {
        $sql = "select distinct domain from product where is_del = 0 ";
        //if not admin
        if (!$_SESSION['admin']['is_admin']) {
            $sql .= ' and  aid = ' . $_SESSION['admin']['uid'];
        }
        $data = $this->db->query($sql)->fetchAll(\PDO::FETCH_COLUMN);
        return $data;
    }

    /**
     * 获取所有模板代号的列表
     * @return mixed
     */
    public function getThemeCodeList()
    {
        $sql = 'select theme from shop_theme group by theme';
        $themeCodeList = $this->db->query($sql)->fetchAll(\PDO::FETCH_COLUMN);

        return $themeCodeList;
    }

    /**
     * [deleteSite 删除站点]
     * @param  [type] $domain [description]
     * @param  [type] $data   [description]
     * @return [type]         [description]
     */
    public function deleteSite($domain, $data)
    {
        $map = ['domain' => $domain];
        $siteData = $this->getSingleSite($map);
        $ret = $this->updateSite($map, $data);
        if (!$siteData['is_del']) {
            //删除
            if (!$ret) {
                return ['ret' => 0, 'msg' => "删除失败"];
            }
        } else {
            //恢复
            if (!$ret) {
                return ['ret' => 0, 'msg' => "恢复失败。=》数据库更改失败"];
            }
        }

        return ['ret' => 1, 'msg' => "OK"];
    }

    /**
     * 更新
     * @param string $filter
     * @param $data
     * @return mixed
     */
    public function updateSite($filter = '', $data)
    {
        $map = [];
        if ($filter) {
            $filter = ['AND' => $filter];
            $map = array_merge($map, $filter);
        }
        $ret = $this->db->update('site', $data, $map);
        return $ret;
    }

    /**
     * 保存
     * @param array $data
     */
    public function saveSite($data)
    {
        $ret = $this->db->insert('site', $data);
        return $ret;
    }

    /**
     * 查询单个域名站点信息
     * @param string $filter
     * @return mixed
     */
    public function getSingleSite($filter = '')
    {
        $map = [];
        if ($filter) {
            $filter = ['AND' => $filter];
            $map = array_merge($map, $filter);
        }
        $ret = $this->db->get('site', '*', $map);

        if ($ret) {
            $oa_id_department = $ret['oa_id_department'];
            $oa_model = new \admin\helper\oa_users($this->register);
            $oa = $oa_model->get_department(['id_department'=>$oa_id_department]);
            $oa  =array_column($oa,NULL,'id_department');
            $ret['department'] = $oa[$ret['oa_id_department']]['department'];
        }
      
        if ($ret) {
            return $ret;
        } else {
            return [];
        }
    }

    /**
     * 获取基本站点信息
     * @param string $domain
     * @return mixed
     */
    public function getSiteInfo($domain)
    {
        $filter['domain'] = $domain;
        $data = $this->getSingleSite($filter);
        if (!$data) {
            $data['domain'] = $domain;
        }
        return $data;
    }

    /**
     * 获取可用产品的所有域名
     * @return array
     */
    public function getListDomain()
    {
        $data = $this->getAllDomain();
        //根据可编辑域名查询查询域名详情
        if (!$data) {
            return [];
        } else {
            foreach ($data as $key => $val) {
                $filter['domain'] = $val;
                $sgData = $this->getSingleSite($filter);
                if ($sgData) {
                    $item[] = $sgData;
                } else {
                    $sgData['domain'] = $val;
                    $item[] = $sgData;
                }
            }
            $ret['domainList'] = $item;
            return $ret;
        }
    }

    /**
     * [getAllSite 获取所有站点]
     * @return [type] [description]
     */
    public function getAllSite()
    {
        $ret = $this->db->select('site', 'domain', ['is_del' => 0]);
        return $ret;
    }

    //////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////
    /**
     * 根据域名搜索信息
     * @param $filter
     * @return array
     */
    public function searchDomain($filter = '')
    {
        $data = $this->getAllDomain();

        //根据可编辑域名查询查询域名详情
        if (!$data) {
            return [];
        } else {

            $domain = $filter['domain'];
            $map['domain[~]'] = $domain;

            if (!strchr($domain, '.com')) {
                $ret['domainList'] = [];
                return $ret;
            }

            $count = 0;
            $strDomain = [];
            foreach ($data as $key => $value) {
                if (strchr($value, $domain)) {
                    $count++;
                    $strDomain[$key] = $value;
                }
            }

            if ($count) {
                $sgData = $this->getSearchSite($map);
                foreach ($sgData as $key => $val) {
                    $item[] = $val;
                }
            }

            if (!$item) {
                foreach ($strDomain as $key => $value) {
                    $data['domain'] = $value;
                    $item[] = $data;
                }
            }

            $ret['domainList'] = $item;
            return $ret;
        }
    }

    /**
     * 条件搜索
     * @param string $filter
     * @return array|null
     */
    public function getSearchSite($filter = '')
    {
        $map = [];
        if ($filter) {
            $filter = ['AND' => $filter];
        }
        $map = array_merge($map, $filter);
        $ret = $this->db->select('site', '*', $map);
        return $ret;
    }

    //////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////


   

    /**
     * 查询 域名站点信息列表
     * @param string $filter
     * @return mixed
     */
    public function getAllSites($filter = '')
    {
        $map = [];
        if ($filter) {
            $filter = ['AND' => $filter];
            $map = array_merge($map, $filter);
        }

        $ret = $this->db->pageSelect('site', '*', $map);
        if ($ret['goodsList']) {
            $oa_id_department = array_unique(array_column($ret['goodsList'], 'oa_id_department'));
            $oa_model = new \admin\helper\oa_users($this->register);
            $oa = $oa_model->get_department(['id_department'=>$oa_id_department]);
            $oa  =array_column($oa,NULL,'id_department');
            
            foreach ($ret['goodsList'] as $value) {
                $value['department'] = $oa[$value['oa_id_department']]['department'];
                $tmp[] = $value;
            }
            $ret['goodsList'] = $tmp;
        }
         
        if ($ret) {
            return $ret;
        } else {
            return [];
        }
    }


    /**
     * [getSitesByPrivilege 根据用户的权限获取相应的站点]
     * 权限： 同部门共享， 上级看下级
     * @return [type] [description]
     */
    public function getSitesByPrivilege($filter = [])
    {
        // 判断权限： 管理员所有站点; 普通员工该部门; 部门领导看下属
        $uid = $this->getUids();
        if ($uid['uid']) {
            $filter['oa_id_department'] = $uid['id_department'];
        }

        $map = [];
        if ($filter) {
            $filter = ['AND' => $filter];
            $map = array_merge($map, $filter);
        }

        $ret = $this->db->pageSelect('site', '*', $map);
        if ($ret['goodsList']) {
            $oa_id_department = array_unique(array_column($ret['goodsList'], 'oa_id_department'));
            $oa_model = new \admin\helper\oa_users($this->register);
            $oa = $oa_model->get_department(['id_department'=>$oa_id_department]);
            $oa  =array_column($oa,NULL,'id_department');
            
            foreach ($ret['goodsList'] as $value) {
                $value['department'] = $oa[$value['oa_id_department']]['department'];
                $tmp[] = $value;
            }
            $ret['goodsList'] = $tmp;
        }
        return $ret?$ret:[];

    }
    

    

   

   

    
}
