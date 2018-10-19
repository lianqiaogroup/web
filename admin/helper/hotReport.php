<?php
// +----------------------------------------------------------------------
// | ChenHK [ 新增统计报表模数据库操作类-实体 ]
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Team:   Cuckoo
// +----------------------------------------------------------------------
// | Date:   2018/1/2 17:53
// +----------------------------------------------------------------------

namespace admin\helper;


class hotReport extends common
{
    /**
     * 查询爆款数据
     * @param array $filter
     * @return null
     */
    public function selectedHot(Array $filter = [])
    {
        if (!empty($filter)) {
            $sql1 = 'select p.product_id,concat(p.domain,\'/\',p.identity_tag) as url,p.title,p.erp_number,p.is_del,count(o.order_id) as cnt,p.add_time ';
            $sql2 = 'from `product` as p left join `order` as o on p.product_id=o.product_id ';
            $sql3 = 'where p.erp_number in ('. implode(',', $filter) .') group by p.product_id order by count(1) desc';

            $sql = $sql1 . $sql2 . $sql3;
           
            $data = $this->db->query($sql)->fetchAll(\PDO::FETCH_NUM);

            return $data;
        } else {
            return null;
        }
    }

    /**
     * 查找爆款数据列表
     * @param array $filter
     * @return null
     */
    public function selectedHotList(Array $filter = [])
    {
        if (!empty($filter)) {
            $data = $this->selectedHot($filter);
            $data = $this->subSelectedHotFilter($data);

            return $this->array_to_object($data);
        } else {

            return null;
        }
    }
    /**
     * @param array $arr 数组
     * @return object
     */
    function array_to_object($arr) {
        if (!empty($arr)) {
            foreach ($arr as $k => $v) {
                $obj = [];
                $obj['product_id'] = $v[0];
                $obj['url']        = $v[1];
                $obj['title']      = $v[2];
                $obj['erp_number'] = $v[3];
                $obj['is_del']     = intval($v[4]);
                $obj['cnt']        = $v[5];
                $arr[$k] = $obj;
            }

            return $arr;
        } else {
            return null;
        }
    }
    /**
     * 筛选最大的销售量
     * @param $data
     * @return array|null
     */
    function subSelectedHotFilter($data)
    {
        if (!empty($data)) {
            //已经有的erpID
            $erp_numbers = array_unique(array_column($data, 3));

            //用于保存用户的搜索结果
            $result = array();
            foreach ($erp_numbers as $erp_number) {
                $max = 0;
                $last_time = '1970-01-01 00:00:00';
                foreach ($data as $value) {
                    if ($erp_number != $value[3]) {
                        continue;
                    }
                    if (intval($value[5]) > $max) {
                        $max = $value[5];
                    }
                    if ($value[6] > $last_time) {
                        $last_time = $value[6];
                    }
                }

                if ($max == 0) {
                    //找出最近时间新建站点
                    foreach ($data as $value) {
                        if ($erp_number != $value[3]) {
                            continue;
                        }
                        if ($value[6] == $last_time) {
                            $result[] = $value;
                        }
                    }
                } else {
                    //找出相同订单量的erp_number
                    foreach ($data as $value) {
                        if ($erp_number != $value[3]) {
                            continue;
                        }
                        if (intval($value[5]) == $max) {
                            $result[] = $value;
                        }
                    }
                }
            }

            return $result;
        } else {
            return null;
        }
    }

    /**
     * 更新
     * @param array $filter
     * @return null
     */
    public function updateDelHot(Array $filter = [])
    {
        if (!empty($filter)) {

            //删除先判断是否存在相同二级目录的站点
            $this->db->update('product', ['is_del'=>10], $filter);
            return 1;
        } else {
            return null;
        }
    }

    /**
     * 判断是否有相同的二级目录,如果存在返回0,不存在返回1
     * @param $p_id
     * @return int
     */
    function checkProductIdentityTay($p_id)
    {
        $sql1 = 'select p.domain, p.identity_tag, p.is_del, p.add_time ';
        $sql2 = 'from product p left join product d on p.domain=d.domain and p.identity_tag=d.identity_tag ';
        $sql3 = 'where p.product_id=' . $p_id;

        $sql = $sql1 . $sql2 . $sql3;

        $data = $this->db->query($sql)->fetchAll(\PDO::FETCH_NUM);

        return count($data) > 1 ? 0 : 1;
    }

}