<?php

namespace admin\helper;

/**
 * 统计条件封装类
 * Class tongji_map
 * @package admin\helper
 */
class tongji_map
{
    /**
     * UV表条件封装
     * @param $v_date       具体时间
     * @param $v_day_num    相隔天数
     * @param $v_time_type  时间类型
     * @return null|string
     */
    public function mapUV($v_date, $v_day_num, $v_time_type)
    {
        //相隔天数查询
        $map = '1';
        if ($v_day_num > 0) {
            //查询多天的数据
            $queryDate = date('Y-m-d 00:00:00', strtotime("-$v_day_num day"));

            $map = "visit_time > '$queryDate'";
        } else {
            //具体时间查询
            $s_day_time = date('Y-m-d 00:00:00', strtotime($v_date));
            $e_day_time = null;
            //判断日期格式是否正确
            if ($s_day_time == '1970-01-01 00:00:00' || $s_day_time == '0000-00-00 00:00:00') {
                $s_day_time = date('Y-m-d 00:00:00');
                $e_day_time = date('Y-m-d 00:00:00', strtotime("$s_day_time+1 day"));
            } else {
                //查询一天的时间
                $e_day_time = date('Y-m-d 00:00:00', strtotime("$s_day_time+1 day"));
            }

            $map = "visit_time > '$s_day_time' and visit_time < '$e_day_time'";
        }

        if ($v_time_type > 0 && $v_time_type <= 3) {
            $map = $map . " and time_type=$v_time_type";
        }

        return $map;
    }

    /**
     * PV表条件封装
     * @param $v_date       具体时间
     * @param $v_day_num    相隔天数
     * @param $v_time_type  时间类型
     * @return null|string
     */
    public function mapPV($v_date, $v_day_num, $v_time_type)
    {
        //相隔天数查询
        $map = '1';
        if ($v_day_num > 0) {
            //查询多天的数据
            $queryDate = date('Y-m-d 00:00:00', strtotime("-$v_day_num day"));

            $map = "first_visit_time > '$queryDate'";
        } else {
            //具体时间查询
            $s_day_time = date('Y-m-d 00:00:00', strtotime($v_date));
            $e_day_time = null;
            //判断日期格式是否正确
            if ($s_day_time == '1970-01-01 00:00:00' || $s_day_time == '0000-00-00 00:00:00') {
                $s_day_time = date('Y-m-d 00:00:00');
                $e_day_time = date('Y-m-d 00:00:00', strtotime("$s_day_time+1 day"));
            } else {
                //查询一天的时间
                $e_day_time = date('Y-m-d 00:00:00', strtotime("$s_day_time+1 day"));
            }

            $map = "first_visit_time > '$s_day_time' and first_visit_time < '$e_day_time'";
        }

        if ($v_time_type > 0 && $v_time_type <= 3) {
            $map = $map . " and time_type=$v_time_type";
        }

        return $map;
    }

    /**
     * ORDER表条件封装
     * @param $v_date       具体时间
     * @param $v_day_num    相隔天数
     * @param $v_time_type  时间类型
     * @return null|string
     */
    public function mapOrder($v_date, $v_day_num, $v_time_type)
    {
        //相隔天数查询
        $map = '1';
        if ($v_day_num > 0) {
            //查询多天的数据
            $queryDate = date('Y-m-d 00:00:00', strtotime("-$v_day_num day"));

            $map = "add_time > '$queryDate'";
        } else {
            //具体时间查询
            $s_day_time = date('Y-m-d 00:00:00', strtotime($v_date));
            $e_day_time = null;
            //判断日期格式是否正确
            if ($s_day_time == '1970-01-01 00:00:00' || $s_day_time == '0000-00-00 00:00:00') {
                $s_day_time = date('Y-m-d 00:00:00');
                $e_day_time = date('Y-m-d 00:00:00', strtotime("$s_day_time+1 day"));
            } else {
                //查询一天的时间
                $e_day_time = date('Y-m-d 00:00:00', strtotime("$s_day_time+1 day"));
            }

            $map = "add_time > '$s_day_time' and add_time < '$e_day_time'";
        }

        return $map;
    }
}