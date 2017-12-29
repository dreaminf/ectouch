<?php

namespace App\Services;


class BrandService
{
    /**
     * 取得品牌列表
     * @return array 品牌列表 id => name
     */
    function get_brand_list()
    {
        $sql = 'SELECT brand_id, brand_name FROM ' . $GLOBALS['ecs']->table('brand') . ' ORDER BY sort_order';
        $res = $GLOBALS['db']->getAll($sql);

        $brand_list = [];
        foreach ($res as $row) {
            $brand_list[$row['brand_id']] = addslashes($row['brand_name']);
        }

        return $brand_list;
    }

    /**
     * 获得某个分类下
     *
     * @access  public
     * @param   int $cat
     * @return  array
     */
    function get_brands($cat = 0, $app = 'brand')
    {
        global $page_libs;
        $template = basename(PHP_SELF);
        $template = substr($template, 0, strrpos($template, '.'));
        load_helper('template', 'admin');

        static $static_page_libs = null;
        if ($static_page_libs == null) {
            $static_page_libs = $page_libs;
        }

        $children = ($cat > 0) ? ' AND ' . get_children($cat) : '';

        $sql = "SELECT b.brand_id, b.brand_name, b.brand_logo, b.brand_desc, COUNT(*) AS goods_num, IF(b.brand_logo > '', '1', '0') AS tag " .
            "FROM " . $GLOBALS['ecs']->table('brand') . "AS b, " .
            $GLOBALS['ecs']->table('goods') . " AS g " .
            "WHERE g.brand_id = b.brand_id $children AND is_show = 1 " .
            " AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 " .
            "GROUP BY b.brand_id HAVING goods_num > 0 ORDER BY tag DESC, b.sort_order ASC";
        if (isset($static_page_libs[$template]['/library/brands.lbi'])) {
            $num = get_library_number("brands");
            $sql .= " LIMIT $num ";
        }
        $row = $GLOBALS['db']->getAll($sql);

        foreach ($row as $key => $val) {
            $row[$key]['url'] = build_uri($app, ['cid' => $cat, 'bid' => $val['brand_id']], $val['brand_name']);
            $row[$key]['brand_desc'] = htmlspecialchars($val['brand_desc'], ENT_QUOTES);
        }

        return $row;
    }

    /**
     * 获得指定的品牌下的商品
     *
     * @access  public
     * @param   integer $brand_id 品牌的ID
     * @param   integer $num 数量
     * @param   integer $cat_id 分类编号
     * @param   string $order_rule 指定商品排序规则
     * @return  void
     */
    function assign_brand_goods($brand_id, $num = 0, $cat_id = 0, $order_rule = '')
    {
        $sql = 'SELECT g.goods_id, g.goods_name, g.market_price, g.shop_price AS org_price, ' .
            "IFNULL(mp.user_price, g.shop_price * '". session('discount') ."') AS shop_price, " .
            'g.promote_price, g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img ' .
            'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
            "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '". session('user_rank') ."' " .
            "WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.brand_id = '$brand_id'";

        if ($cat_id > 0) {
            $sql .= get_children($cat_id);
        }

        $order_rule = empty($order_rule) ? ' ORDER BY g.sort_order, g.goods_id DESC' : $order_rule;
        $sql .= $order_rule;
        if ($num > 0) {
            $res = $GLOBALS['db']->selectLimit($sql, $num);
        } else {
            $res = $GLOBALS['db']->query($sql);
        }

        $idx = 0;
        $goods = [];
        foreach ($res as $row) {
            if ($row['promote_price'] > 0) {
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            } else {
                $promote_price = 0;
            }

            $goods[$idx]['id'] = $row['goods_id'];
            $goods[$idx]['name'] = $row['goods_name'];
            $goods[$idx]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $goods[$idx]['market_price'] = price_format($row['market_price']);
            $goods[$idx]['shop_price'] = price_format($row['shop_price']);
            $goods[$idx]['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
            $goods[$idx]['brief'] = $row['goods_brief'];
            $goods[$idx]['thumb'] = get_image_path($row['goods_thumb']);
            $goods[$idx]['goods_img'] = get_image_path($row['goods_img']);
            $goods[$idx]['url'] = build_uri('goods', ['gid' => $row['goods_id']], $row['goods_name']);

            $idx++;
        }

        // 分类信息
        $sql = 'SELECT brand_name FROM ' . $GLOBALS['ecs']->table('brand') . " WHERE brand_id = '$brand_id'";

        $brand['id'] = $brand_id;
        $brand['name'] = $GLOBALS['db']->getOne($sql);
        $brand['url'] = build_uri('brand', ['bid' => $brand_id], $brand['name']);

        $brand_goods = ['brand' => $brand, 'goods' => $goods];

        return $brand_goods;
    }


}