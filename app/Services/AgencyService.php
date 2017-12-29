<?php

namespace App\Services;


class AgencyService
{

    /**
     * 查询配送区域属于哪个办事处管辖
     * @param   array $regions 配送区域（1、2、3、4级按顺序）
     * @return  int     办事处id，可能为0
     */
    function get_agency_by_regions($regions)
    {
        if (!is_array($regions) || empty($regions)) {
            return 0;
        }

        $arr = [];
        $sql = "SELECT region_id, agency_id " .
            "FROM " . $GLOBALS['ecs']->table('region') .
            " WHERE region_id " . db_create_in($regions) .
            " AND region_id > 0 AND agency_id > 0";
        $res = $GLOBALS['db']->query($sql);
        foreach ($res as $row) {
            $arr[$row['region_id']] = $row['agency_id'];
        }
        if (empty($arr)) {
            return 0;
        }

        $agency_id = 0;
        for ($i = count($regions) - 1; $i >= 0; $i--) {
            if (isset($arr[$regions[$i]])) {
                return $arr[$regions[$i]];
            }
        }
    }

}