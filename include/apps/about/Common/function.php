<?php

/**
 * 获得指定地区名称
 *
 * @access      public
 * @param       int     region_id    编号
 * @return      string
 */
function get_region_name($region_id = 0)
{
    $condition['region_id'] = $region_id;
    return M('region')->where($condition)->getField('region_name');
}