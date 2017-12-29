<?php

namespace App\Services;


class ActivityService
{

    /**
     * 取得优惠活动信息
     * @param   int $act_id 活动id
     * @return  array
     */
    function favourable_info($act_id)
    {
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('favourable_activity') .
            " WHERE act_id = '$act_id'";
        $row = $GLOBALS['db']->getRow($sql);
        if (!empty($row)) {
            $row['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['start_time']);
            $row['end_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['end_time']);
            $row['formated_min_amount'] = price_format($row['min_amount']);
            $row['formated_max_amount'] = price_format($row['max_amount']);
            $row['gift'] = unserialize($row['gift']);
            if ($row['act_type'] == FAT_GOODS) {
                $row['act_type_ext'] = round($row['act_type_ext']);
            }
        }

        return $row;
    }
}