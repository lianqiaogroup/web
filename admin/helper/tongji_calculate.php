<?php

namespace admin\helper;

/**
 * 统计计算工具类
 * Class tongji_calculate
 * @package admin\helper
 */
class tongji_calculate
{
    /**
     * 计算用户停留时间
     * @param $sum  时间总和（毫秒）
     * @param $count 访问个数
     * @return float|int
     */
    public function userStayAverageTime($sum, $count)
    {
        $average = 0;
        if (intval($count) <= 0) {
            return $average;
        } else {
            $average = $sum / ($count * 1000);
            return round($average, 3);
        }
    }
}