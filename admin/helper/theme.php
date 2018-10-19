<?php

namespace admin\helper;

class theme extends common
{

    public function getThemeList($filter=[])
    {
        $map['ORDER'] = ['tid' => "DESC"];
        if($filter){
            $map['AND'] = $filter;
        }
        $data = $this->db->pageSelect('theme', '*', $map, 20);

        if (!empty($data['goodsList'])) {
            $keyValue = $this->themeGroupBy($data['goodsList']);
            $data  = $this->addThemeCount($data, $keyValue);
        }
        return $data;
    }

    function addThemeCount($data, $keyValue)
    {
        $goodsList = array();

        foreach ($data['goodsList'] as $item) {
            $item['theme_count'] = (isset($keyValue[$item['theme']])&&$keyValue[$item['theme']]) ? $keyValue[$item['theme']] : 0;
            $goodsList[] = $item;
        }

        $data['goodsList'] = $goodsList;

        return $data;
    }

    /**
     * 构建key-value数组
     * @param $goodsList
     * @return array
     */
    function themeGroupBy($goodsList)
    {
        $keyValue = array();

        if (!empty($goodsList)) {
            $themeString = '';
            foreach ($goodsList as $item) {
                if (!empty($item['theme'])) {
                    $themeString = $themeString . ',\''. $item['theme'] . '\'';
                }
            }
            $themeString = '('. substr($themeString, 1, strlen($themeString)) .')';
            $sql = 'select theme,count(theme) count from  product where is_del=0 and theme in '.$themeString.' group by theme';
            $themeData = $this->db->query($sql)->fetchAll(\PDO::FETCH_NUM);

            if (!empty($themeData)) {
                foreach ($themeData as $value) {
                    $keyValue[$value[0]] = $value[1];
                }
            }
        }

        return $keyValue;
    }

    public function getLang()
    {

        $file = app_path . '/admin/template/config/theme_language';

        $content = file_get_contents($file);

        return $content;
    }

    //保存
    public function save($data) {

        $ret = $this->check($data['title'], $data['theme'], $data['tid']);
        if (!$ret) {
            return ['ret' => 0, 'msg' => "存在重复的代号或标题"];
        }

        if (isset($data['belong_id_department'])) {
            $belong_id_department = array_unique(explode(',', $data['belong_id_department']));
            $belong_id_department = I('data.', '0', ['trim'], $belong_id_department);
            $data['belong_id_department'] = implode(',', $belong_id_department);
        }

        if ($data['tid']) {
            $id = $data['tid'];
            unset($data['tid']);
            $ret = $this->db->update('theme', $data, ['tid' => $id]);
            if ($ret === false) {
                return ['ret' => 0, 'msg' => "保存失败"];
            }
        } else {
            unset($data['tid']);
            $ret = $this->db->insert('theme', $data);
            if (!$ret) {
                return ['ret' => 0, 'msg' => "插入失败"];
            }
        }

        return ['ret' => 1];
    }

    /**
     * 验证模板是否重复
     * @param string $title 模板标题
     * @param string $theme 前台模板路劲
     * @param int $id 模板id
     * @return boolean false：重复  true：不重复
     */
    public function check($title, $theme, $id) {

        $maps["or"]['title'] = $title;
        $maps['or']['theme'] = $theme;
        if ($id) {
            $maps['tid[!]'] = $id;
            $map['and'] = $maps;
        } else {
            $map = $maps;
        }
        $ret = $this->db->count('theme', $map);

        if ($ret) {
            return false;
        }
        return true;
    }

    /**
     * 获取产品支持的模板数据
     * @param string $zone_code 地区编码
     * @param string $lang  语言编码
     * @param string $id_department 地区串 如：0,56
     * @return array 产品支持的模板数据
     */
    public function getProductTheme($zone_code, $lang, $id_department) {
        
        $where = ["is_del=0","FIND_IN_SET('$zone_code',zone)>0", "FIND_IN_SET('$lang',lang)>0"];
        $departmentIdData = array_unique(explode(",", $id_department));
        if (is_array($departmentIdData) && $departmentIdData) {
            $departmentWhere = ["FIND_IN_SET('0',belong_id_department)>0"];
            foreach ($departmentIdData as $departmentId) {
                $departmentWhere[] = "FIND_IN_SET('$departmentId',belong_id_department)>0";
            }
            if($departmentWhere){
                $departmentWhere = implode(' OR ', $departmentWhere);
                $where[] = '('.$departmentWhere.')';
            }
        }
        $where = implode(' AND ', $where);

        $data = [];
        $sql = "SELECT * FROM theme WHERE $where ORDER BY tid DESC";
        $query = $this->db->query($sql);
        if($query){
            $data = $query->fetchAll(\PDO::FETCH_ASSOC);
        }

        $rows = [];
        if ($data) {
            $contry = new \admin\helper\country($this->register);
            $zones = $contry->getAllZone();
            $zones = array_column($zones, NULL, 'code');
            foreach ($data as $row) {
                $row['regions'] = $this->getZoneName($zones, $row['zone']);
                $rows[] = $row;
            }
        }
        
        return $rows;
    }

    public function getZoneName($zone,$code){
        if($code)
        {
            $codes =  explode(",",$code);
            foreach ($codes as $val)
            {
                $ret[]= $zone[$val]['title'];
            }
        }

        return $ret;

    }

    public function del($data)
    {
        $map['tid'] = $data['tid'];
        $info = $this->db->get('theme', '*', $map);

        if ($data['is_del']) {
            $ret = $this->check($info['title'], $info['theme'], $info['tid']);
            if (!$ret) {
                return ['ret' => 0, 'msg' => "存在重复的代号或标题"];
            }
        }
        unset($data['tid']);
        $this->db->update('theme', $data, $map);

        return ['ret' => 1];
    }
}