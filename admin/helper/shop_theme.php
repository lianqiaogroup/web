<?php
// +----------------------------------------------------------------------
// | ChenHK [ 店铺模板数据库操作类-实体 ]
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Team:   Cuckoo
// +----------------------------------------------------------------------
// | Date:   2018/1/2 17:53
// +----------------------------------------------------------------------

namespace admin\helper;

class shop_theme extends common
{
    //店铺模板表名
    protected $table = 'shop_theme';


    /**
     * 获取店铺模板
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        if ($filter) {
            $data = $this->db->get($this->table, '*', $filter);
        } else {
            $data = [];
        }

        return $data;
    }

    /**
     * 获取店铺模板-select
     * @param $filter
     * @return mixed
     */
    public function select($filter)
    {
        $condition['ORDER'] = ['created_at' => "DESC"];

        if ($filter) {
            $condition['AND'] = $filter;
        }

        $data = $this->db->select($this->table, '*', $condition);

        return $data;
    }


    /**
     * 获取店铺模板列表
     * @param $filter
     * @return mixed
     */
    public function getLists($filter)
    {
        $condition['ORDER'] = ['created_at' => "DESC"];

        if ($filter) {
            $condition['AND'] = $filter;
        }

        $data = $this->db->pageSelect($this->table, '*', $condition, 20);

        $sql = 'select s.theme theme, count(`theme`) count from `site` s where s.is_del=0 group by s.`theme`';
        $countTheme = $this->db->query($sql)->fetchAll(\PDO::FETCH_NUM);

        $themeArray = [];
        foreach ($countTheme as $item) {
            $themeArray[$item[0]] = $item[1];
        }

        foreach ($data['goodsList'] as $key => $theme) {
            $theme['theme_count'] = $themeArray[$theme['theme']] ? $themeArray[$theme['theme']] : 0;
            $data['goodsList'][$key] = $theme;
        }

        return $data;
    }

    /**
     * 插入或者更新
     * @param $data
     * @return array
     */
    public function insertOrUpdate($data) {
        $ret = $this->check($data['title'], $data['theme'], $data['sid']);

        if (!$ret) {
            return ['ret' => 0, 'msg' => "存在重复的代号或标题"];
        }

        if (isset($data['belong_id_department'])) {
            $belong_id_department = array_unique(explode(',', $data['belong_id_department']));
            $belong_id_department = I('data.', '0', ['trim'], $belong_id_department);
            $data['belong_id_department'] = implode(',', $belong_id_department);
        }

        if ($data['sid']) {
            $id = $data['sid'];
            unset($data['sid']);
            $ret = $this->db->update($this->table, $data, ['sid' => $id]);
            if ($ret === false) {
                return ['ret' => 0, 'msg' => "保存失败"];
            }
        } else {
            unset($data['sid']);
            $data['created_at'] = date("Y-m-d H:i:s", time());
            $ret = $this->db->insert($this->table, $data);
            if (!$ret) {
                return ['ret' => 0, 'msg' => "插入失败"];
            }
        }

        return ['ret' => 1];
    }

    /**
     * 删除
     * @param $data
     * @return array
     */
    public function delete($data)
    {
        $condition['sid'] = $data['sid'];
        $info = $this->db->get($this->table, '*', $condition);

        if ($data['is_del']) {
            $ret = $this->check($info['title'], $info['theme'], $info['sid']);
            if (!$ret) {
                return ['ret' => 0, 'msg' => "存在重复的代号或标题"];
            }
        }

        unset($data['sid']);

        $this->db->update($this->table, $data, $condition);

        return ['ret' => 1];
    }

    /**
     * 检测
     * @param $title
     * @param $theme
     * @param $id
     * @return bool
     */
    public function check($title, $theme, $id)
    {
        $maps["or"]['title'] = $title;
        $maps['or']['theme'] = $theme;

        if ($id) {
            $maps['sid[!]'] = $id;
            $map['and'] = $maps;
        } else {
            $map = $maps;
        }

        $ret = $this->db->count($this->table, $map);

        if ($ret) {
            return false;
        }

        return true;
    }
    
    /**
     * 获取部门店铺模板
     * @param array $where where条件
     * @return array
     */
    public function getThemeByQuery($where = []) {

        $where = array_merge(["is_del=0"], $where);
        $departmentWhere = [];
        $departmentIdData = [0, $_SESSION['admin']['id_department']];
        foreach ($departmentIdData as $departmentId) {
            $departmentWhere[] = "FIND_IN_SET('$departmentId',belong_id_department)>0";
        }
        if ($departmentWhere) {
            $departmentWhere = implode(' OR ', $departmentWhere);
            $where[] = '(' . $departmentWhere . ')';
        }
        $where = implode(' AND ', $where);

        $data = [];
        $sql = "SELECT * FROM " . $this->table . " WHERE $where ORDER BY created_at DESC";
        $query = $this->db->query($sql);
        if ($query) {
            $data = $query->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $data;
    }

}