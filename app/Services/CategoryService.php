<?php

namespace App\Services;


class CategoryService
{

    /**
     * 获得指定分类下的子分类的数组
     *
     * @access  public
     * @param   int $cat_id 分类的ID
     * @param   int $selected 当前选中分类的ID
     * @param   boolean $re_type 返回的类型: 值为真时返回下拉列表,否则返回数组
     * @param   int $level 限定返回的级数。为0时返回所有级数
     * @param   int $is_show_all 如果为true显示所有分类，如果为false隐藏不可见分类。
     * @return  mix
     */
    function cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true)
    {
        static $res = null;

        if ($res === null) {
            $data = read_static_cache('cat_pid_releate');
            if ($data === false) {
                $sql = "SELECT c.cat_id, c.cat_name, c.measure_unit, c.parent_id, c.is_show, c.show_in_nav, c.grade, c.sort_order, COUNT(s.cat_id) AS has_children " .
                    'FROM ' . $GLOBALS['ecs']->table('category') . " AS c " .
                    "LEFT JOIN " . $GLOBALS['ecs']->table('category') . " AS s ON s.parent_id=c.cat_id " .
                    "GROUP BY c.cat_id " .
                    'ORDER BY c.parent_id, c.sort_order ASC';
                $res = $GLOBALS['db']->getAll($sql);

                $sql = "SELECT cat_id, COUNT(*) AS goods_num " .
                    " FROM " . $GLOBALS['ecs']->table('goods') .
                    " WHERE is_delete = 0 AND is_on_sale = 1 " .
                    " GROUP BY cat_id";
                $res2 = $GLOBALS['db']->getAll($sql);

                $sql = "SELECT gc.cat_id, COUNT(*) AS goods_num " .
                    " FROM " . $GLOBALS['ecs']->table('goods_cat') . " AS gc , " . $GLOBALS['ecs']->table('goods') . " AS g " .
                    " WHERE g.goods_id = gc.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 " .
                    " GROUP BY gc.cat_id";
                $res3 = $GLOBALS['db']->getAll($sql);

                $newres = [];
                foreach ($res2 as $k => $v) {
                    $newres[$v['cat_id']] = $v['goods_num'];
                    foreach ($res3 as $ks => $vs) {
                        if ($v['cat_id'] == $vs['cat_id']) {
                            $newres[$v['cat_id']] = $v['goods_num'] + $vs['goods_num'];
                        }
                    }
                }

                foreach ($res as $k => $v) {
                    $res[$k]['goods_num'] = !empty($newres[$v['cat_id']]) ? $newres[$v['cat_id']] : 0;
                }
                //如果数组过大，不采用静态缓存方式
                if (count($res) <= 1000) {
                    write_static_cache('cat_pid_releate', $res);
                }
            } else {
                $res = $data;
            }
        }

        if (empty($res) == true) {
            return $re_type ? '' : [];
        }

        $options = cat_options($cat_id, $res); // 获得指定分类下的子分类的数组

        $children_level = 99999; //大于这个分类的将被删除
        if ($is_show_all == false) {
            foreach ($options as $key => $val) {
                if ($val['level'] > $children_level) {
                    unset($options[$key]);
                } else {
                    if ($val['is_show'] == 0) {
                        unset($options[$key]);
                        if ($children_level > $val['level']) {
                            $children_level = $val['level']; //标记一下，这样子分类也能删除
                        }
                    } else {
                        $children_level = 99999; //恢复初始值
                    }
                }
            }
        }

        // 截取到指定的缩减级别
        if ($level > 0) {
            if ($cat_id == 0) {
                $end_level = $level;
            } else {
                $first_item = reset($options); // 获取第一个元素
                $end_level = $first_item['level'] + $level;
            }

            // 保留level小于end_level的部分
            foreach ($options as $key => $val) {
                if ($val['level'] >= $end_level) {
                    unset($options[$key]);
                }
            }
        }

        if ($re_type == true) {
            $select = '';
            foreach ($options as $var) {
                $select .= '<option value="' . $var['cat_id'] . '" ';
                $select .= ($selected == $var['cat_id']) ? "selected='ture'" : '';
                $select .= '>';
                if ($var['level'] > 0) {
                    $select .= str_repeat('&nbsp;', $var['level'] * 4);
                }
                $select .= htmlspecialchars(addslashes($var['cat_name']), ENT_QUOTES) . '</option>';
            }

            return $select;
        } else {
            foreach ($options as $key => $value) {
                $options[$key]['url'] = build_uri('category', ['cid' => $value['cat_id']], $value['cat_name']);
            }

            return $options;
        }
    }

    /**
     * 过滤和排序所有分类，返回一个带有缩进级别的数组
     *
     * @access  private
     * @param   int $cat_id 上级分类ID
     * @param   array $arr 含有所有分类的数组
     * @param   int $level 级别
     * @return  void
     */
    function cat_options($spec_cat_id, $arr)
    {
        static $cat_options = [];

        if (isset($cat_options[$spec_cat_id])) {
            return $cat_options[$spec_cat_id];
        }

        if (!isset($cat_options[0])) {
            $level = $last_cat_id = 0;
            $options = $cat_id_array = $level_array = [];
            $data = read_static_cache('cat_option_static');
            if ($data === false) {
                while (!empty($arr)) {
                    foreach ($arr as $key => $value) {
                        $cat_id = $value['cat_id'];
                        if ($level == 0 && $last_cat_id == 0) {
                            if ($value['parent_id'] > 0) {
                                break;
                            }

                            $options[$cat_id] = $value;
                            $options[$cat_id]['level'] = $level;
                            $options[$cat_id]['id'] = $cat_id;
                            $options[$cat_id]['name'] = $value['cat_name'];
                            unset($arr[$key]);

                            if ($value['has_children'] == 0) {
                                continue;
                            }
                            $last_cat_id = $cat_id;
                            $cat_id_array = [$cat_id];
                            $level_array[$last_cat_id] = ++$level;
                            continue;
                        }

                        if ($value['parent_id'] == $last_cat_id) {
                            $options[$cat_id] = $value;
                            $options[$cat_id]['level'] = $level;
                            $options[$cat_id]['id'] = $cat_id;
                            $options[$cat_id]['name'] = $value['cat_name'];
                            unset($arr[$key]);

                            if ($value['has_children'] > 0) {
                                if (end($cat_id_array) != $last_cat_id) {
                                    $cat_id_array[] = $last_cat_id;
                                }
                                $last_cat_id = $cat_id;
                                $cat_id_array[] = $cat_id;
                                $level_array[$last_cat_id] = ++$level;
                            }
                        } elseif ($value['parent_id'] > $last_cat_id) {
                            break;
                        }
                    }

                    $count = count($cat_id_array);
                    if ($count > 1) {
                        $last_cat_id = array_pop($cat_id_array);
                    } elseif ($count == 1) {
                        if ($last_cat_id != end($cat_id_array)) {
                            $last_cat_id = end($cat_id_array);
                        } else {
                            $level = 0;
                            $last_cat_id = 0;
                            $cat_id_array = [];
                            continue;
                        }
                    }

                    if ($last_cat_id && isset($level_array[$last_cat_id])) {
                        $level = $level_array[$last_cat_id];
                    } else {
                        $level = 0;
                    }
                }
                //如果数组过大，不采用静态缓存方式
                if (count($options) <= 2000) {
                    write_static_cache('cat_option_static', $options);
                }
            } else {
                $options = $data;
            }
            $cat_options[0] = $options;
        } else {
            $options = $cat_options[0];
        }

        if (!$spec_cat_id) {
            return $options;
        } else {
            if (empty($options[$spec_cat_id])) {
                return [];
            }

            $spec_cat_id_level = $options[$spec_cat_id]['level'];

            foreach ($options as $key => $value) {
                if ($key != $spec_cat_id) {
                    unset($options[$key]);
                } else {
                    break;
                }
            }

            $spec_cat_id_array = [];
            foreach ($options as $key => $value) {
                if (($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id) ||
                    ($spec_cat_id_level > $value['level'])) {
                    break;
                } else {
                    $spec_cat_id_array[$key] = $value;
                }
            }
            $cat_options[$spec_cat_id] = $spec_cat_id_array;

            return $spec_cat_id_array;
        }
    }


    /**
     * 获得指定分类同级的所有分类以及该分类下的子分类
     *
     * @access  public
     * @param   integer $cat_id 分类编号
     * @return  array
     */
    function get_categories_tree($cat_id = 0)
    {
        if ($cat_id > 0) {
            $sql = 'SELECT parent_id FROM ' . $GLOBALS['ecs']->table('category') . " WHERE cat_id = '$cat_id'";
            $parent_id = $GLOBALS['db']->getOne($sql);
        } else {
            $parent_id = 0;
        }

        /*
         判断当前分类中全是是否是底级分类，
         如果是取出底级分类上级分类，
         如果不是取当前分类及其下的子分类
        */
        $sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('category') . " WHERE parent_id = '$parent_id' AND is_show = 1 ";
        if ($GLOBALS['db']->getOne($sql) || $parent_id == 0) {
            // 获取当前分类及其子分类
            $sql = 'SELECT cat_id,cat_name ,parent_id,is_show ' .
                'FROM ' . $GLOBALS['ecs']->table('category') .
                "WHERE parent_id = '$parent_id' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC";

            $res = $GLOBALS['db']->getAll($sql);

            foreach ($res as $row) {
                if ($row['is_show']) {
                    $cat_arr[$row['cat_id']]['id'] = $row['cat_id'];
                    $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
                    $cat_arr[$row['cat_id']]['url'] = build_uri('category', ['cid' => $row['cat_id']], $row['cat_name']);

                    if (isset($row['cat_id']) != null) {
                        $cat_arr[$row['cat_id']]['cat_id'] = get_child_tree($row['cat_id']);
                    }
                }
            }
        }
        if (isset($cat_arr)) {
            return $cat_arr;
        }
    }

    function get_child_tree($tree_id = 0)
    {
        $three_arr = [];
        $sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('category') . " WHERE parent_id = '$tree_id' AND is_show = 1 ";
        if ($GLOBALS['db']->getOne($sql) || $tree_id == 0) {
            $child_sql = 'SELECT cat_id, cat_name, parent_id, is_show ' .
                'FROM ' . $GLOBALS['ecs']->table('category') .
                "WHERE parent_id = '$tree_id' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC";
            $res = $GLOBALS['db']->getAll($child_sql);
            foreach ($res as $row) {
                if ($row['is_show']) {
                    $three_arr[$row['cat_id']]['id'] = $row['cat_id'];
                }
                $three_arr[$row['cat_id']]['name'] = $row['cat_name'];
                $three_arr[$row['cat_id']]['url'] = build_uri('category', ['cid' => $row['cat_id']], $row['cat_name']);

                if (isset($row['cat_id']) != null) {
                    $three_arr[$row['cat_id']]['cat_id'] = get_child_tree($row['cat_id']);
                }
            }
        }
        return $three_arr;
    }

    /**
     * 获得指定分类下的推荐商品
     *
     * @access  public
     * @param   string $type 推荐类型，可以是 best, new, hot, promote
     * @param   string $cats 分类的ID
     * @param   integer $brand 品牌的ID
     * @param   integer $min 商品价格下限
     * @param   integer $max 商品价格上限
     * @param   string $ext 商品扩展查询
     * @return  array
     */
    function get_category_recommend_goods($type = '', $cats = '', $brand = 0, $min = 0, $max = 0, $ext = '')
    {
        $brand_where = ($brand > 0) ? " AND g.brand_id = '$brand'" : '';

        $price_where = ($min > 0) ? " AND g.shop_price >= $min " : '';
        $price_where .= ($max > 0) ? " AND g.shop_price <= $max " : '';

        $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.shop_price AS org_price, g.promote_price, ' .
            "IFNULL(mp.user_price, g.shop_price * '". session('discount') ."') AS shop_price, " .
            'promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, goods_img, b.brand_name ' .
            'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' .
            "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '". session('user_rank') ."' " .
            'WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . $brand_where . $price_where . $ext;
        $num = 0;
        $type2lib = ['best' => 'recommend_best', 'new' => 'recommend_new', 'hot' => 'recommend_hot', 'promote' => 'recommend_promotion'];
        $num = get_library_number($type2lib[$type]);

        switch ($type) {
            case 'best':
                $sql .= ' AND is_best = 1';
                break;
            case 'new':
                $sql .= ' AND is_new = 1';
                break;
            case 'hot':
                $sql .= ' AND is_hot = 1';
                break;
            case 'promote':
                $time = gmtime();
                $sql .= " AND is_promote = 1 AND promote_start_date <= '$time' AND promote_end_date >= '$time'";
                break;
        }

        if (!empty($cats)) {
            $sql .= " AND (" . $cats . " OR " . get_extension_goods($cats) . ")";
        }

        $order_type = $GLOBALS['_CFG']['recommend_order'];
        $sql .= ($order_type == 0) ? ' ORDER BY g.sort_order, g.last_update DESC' : ' ORDER BY RAND()';
        $res = $GLOBALS['db']->selectLimit($sql, $num);

        $idx = 0;
        $goods = [];
        foreach ($res as $row) {
            if ($row['promote_price'] > 0) {
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                $goods[$idx]['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
            } else {
                $goods[$idx]['promote_price'] = '';
            }

            $goods[$idx]['id'] = $row['goods_id'];
            $goods[$idx]['name'] = $row['goods_name'];
            $goods[$idx]['brief'] = $row['goods_brief'];
            $goods[$idx]['brand_name'] = $row['brand_name'];
            $goods[$idx]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $goods[$idx]['market_price'] = price_format($row['market_price']);
            $goods[$idx]['shop_price'] = price_format($row['shop_price']);
            $goods[$idx]['thumb'] = get_image_path($row['goods_thumb']);
            $goods[$idx]['goods_img'] = get_image_path($row['goods_img']);
            $goods[$idx]['url'] = build_uri('goods', ['gid' => $row['goods_id']], $row['goods_name']);

            $goods[$idx]['short_style_name'] = add_style($goods[$idx]['short_name'], $row['goods_name_style']);
            $idx++;
        }

        return $goods;
    }

    /**
     * 获得指定分类下的商品
     *
     * @access  public
     * @param   integer $cat_id 分类ID
     * @param   integer $num 数量
     * @param   string $from 来自web/wap的调用
     * @param   string $order_rule 指定商品排序规则
     * @return  array
     */
    function assign_cat_goods($cat_id, $num = 0, $from = 'web', $order_rule = '')
    {
        $children = get_children($cat_id);

        $sql = 'SELECT g.goods_id, g.goods_name, g.market_price, g.shop_price AS org_price, ' .
            "IFNULL(mp.user_price, g.shop_price * '". session('discount') ."') AS shop_price, " .
            'g.promote_price, promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img ' .
            "FROM " . $GLOBALS['ecs']->table('goods') . ' AS g ' .
            "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '". session('user_rank') ."' " .
            'WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND ' .
            'g.is_delete = 0 AND (' . $children . 'OR ' . get_extension_goods($children) . ') ';

        $order_rule = empty($order_rule) ? 'ORDER BY g.sort_order, g.goods_id DESC' : $order_rule;
        $sql .= $order_rule;
        if ($num > 0) {
            $sql .= ' LIMIT ' . $num;
        }
        $res = $GLOBALS['db']->getAll($sql);

        $goods = [];
        foreach ($res as $idx => $row) {
            if ($row['promote_price'] > 0) {
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                $goods[$idx]['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
            } else {
                $goods[$idx]['promote_price'] = '';
            }

            $goods[$idx]['id'] = $row['goods_id'];
            $goods[$idx]['name'] = $row['goods_name'];
            $goods[$idx]['brief'] = $row['goods_brief'];
            $goods[$idx]['market_price'] = price_format($row['market_price']);
            $goods[$idx]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $goods[$idx]['shop_price'] = price_format($row['shop_price']);
            $goods[$idx]['thumb'] = get_image_path($row['goods_thumb']);
            $goods[$idx]['goods_img'] = get_image_path($row['goods_img']);
            $goods[$idx]['url'] = build_uri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
        }

        if ($from == 'web') {
            $GLOBALS['smarty']->assign('cat_goods_' . $cat_id, $goods);
        } elseif ($from == 'wap') {
            $cat['goods'] = $goods;
        }

        // 分类信息
        $sql = 'SELECT cat_name FROM ' . $GLOBALS['ecs']->table('category') . " WHERE cat_id = '$cat_id'";
        $cat['name'] = $GLOBALS['db']->getOne($sql);
        $cat['url'] = build_uri('category', ['cid' => $cat_id], $cat['name']);
        $cat['id'] = $cat_id;

        return $cat;
    }

    /**
     * 获得指定分类的所有上级分类
     *
     * @access  public
     * @param   integer $cat 分类编号
     * @return  array
     */
    function get_parent_cats($cat)
    {
        if ($cat == 0) {
            return [];
        }

        $arr = $GLOBALS['db']->getAll('SELECT cat_id, cat_name, parent_id FROM ' . $GLOBALS['ecs']->table('category'));

        if (empty($arr)) {
            return [];
        }

        $index = 0;
        $cats = [];

        while (1) {
            foreach ($arr as $row) {
                if ($cat == $row['cat_id']) {
                    $cat = $row['parent_id'];

                    $cats[$index]['cat_id'] = $row['cat_id'];
                    $cats[$index]['cat_name'] = $row['cat_name'];

                    $index++;
                    break;
                }
            }

            if ($index == 0 || $cat == 0) {
                break;
            }
        }

        return $cats;
    }

    /**
     * 获得指定分类下所有底层分类的ID
     *
     * @access  public
     * @param   integer $cat 指定的分类ID
     * @return  string
     */
    function get_children($cat = 0)
    {
        return 'g.cat_id ' . db_create_in(array_unique(array_merge([$cat], array_keys(cat_list($cat, 0, false)))));
    }

}