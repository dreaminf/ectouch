<?php

namespace App\Services;


class RegionService
{

    /**
     * 创建地区的返回信息
     *
     * @access  public
     * @param   array $arr 地区数组 *
     * @return  void
     */
    function region_result($parent, $sel_name, $type)
    {
        global $cp;

        $arr = get_regions($type, $parent);
        foreach ($arr as $v) {
            $region =& $cp->add_node('region');
            $region_id =& $region->add_node('id');
            $region_name =& $region->add_node('name');

            $region_id->set_data($v['region_id']);
            $region_name->set_data($v['region_name']);
        }
        $select_obj =& $cp->add_node('select');
        $select_obj->set_data($sel_name);
    }

    /**
     * 获得指定国家的所有省份
     *
     * @access      public
     * @param       int     country    国家的编号
     * @return      array
     */
    function get_regions($type = 0, $parent = 0)
    {
        $sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['ecs']->table('region') .
            " WHERE region_type = '$type' AND parent_id = '$parent'";

        return $GLOBALS['db']->getAll($sql);
    }

}