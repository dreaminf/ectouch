<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：IndexController.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTouch首页控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

class Welcome extends IndexController {

    /**
     * 首页信息
     */
    public function index() {
        // 自定义导航栏
        $navigator = get_navigator();
        $this->assign('navigator', $navigator['middle']);
        $this->assign('best_goods', $this->goods_list('best', C('page_size')));
        $this->assign('new_goods', $this->goods_list('new', C('page_size')));
        $this->assign('hot_goods', $this->goods_list('hot', C('page_size')));
        //首页推荐分类
        $cat_rec = $this->get_recommend_res();
        $this->assign('cat_best', $cat_rec[1]);
        $this->assign('cat_new', $cat_rec[2]);
        $this->assign('cat_hot', $cat_rec[3]);
        // 促销活动
        $this->assign('promotion_info', get_promotion_info());
        // 团购商品
        //$this->assign('group_buy_goods', group_buy_list(C('page_size'),1,'goods_id','ASC'));
        // 获取分类
        $this->assign('categories', get_categories_tree());
        // 获取品牌
        $this->assign('brand_list', get_brands($app = 'brand', C('page_size'), 1));
        $this->display('index.dwt');
    }

    public function cat_rec()
    {
        $rec_array = array(1 => 'best', 2 => 'new', 3 => 'hot');
        $rec_type = !empty($_REQUEST['rec_type']) ? intval($_REQUEST['rec_type']) : '1';
        $cat_id = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : '0';
        $json = new JSON;
        $result   = array('error' => 0, 'content' => '', 'type' => $rec_type, 'cat_id' => $cat_id);

        $children = get_children($cat_id);
        $this->assign($rec_array[$rec_type] . '_goods',      get_category_recommend_goods($rec_array[$rec_type], $children));    // 推荐商品
        $this->assign('cat_rec_sign', 1);
        $result['content'] = $this->fetch('library/recommend_' . $rec_array[$rec_type] . '.lbi');
        die($json->encode($result));
    }

    /**
     * ajax获取商品
     */
    public function ajax_goods() {
        if (IS_AJAX) {
            $type = I('get.type');
            $start = $_POST['last'];
            $limit = $_POST['amount'];
            $hot_goods = $this->goods_list($type, $limit, $start);
            $list = array();
            // 热卖商品
            if ($hot_goods) {
                foreach ($hot_goods as $key => $value) {
                    $this->assign('hot_goods', $value);
                    $list [] = array(
                        'single_item' => $this->fetch('library/asynclist_index.lbi')
                    );
                }
            }
            echo json_encode($list);
            exit();
        } else {
            $this->redirect(url('index'));
        }
    }

    /**
     * 获取推荐商品
     * @param  $type
     * @param  $limit
     * @param  $start
     */
    public function goods_list($type = 'best', $limit = 10, $start = 0) {
        if ($type == 'new') {
            $type = 'g.is_new = 1';
        } else if ($type == 'hot') {
            $type = 'g.is_hot = 1';
        } else {
            $type = 'g.is_best = 1';
        }
        // 取出所有符合条件的商品数据，并将结果存入对应的推荐类型数组中
        $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.shop_price AS org_price, g.promote_price, ' . "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, " . "promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img, RAND() AS rnd " . 'FROM {pre}goods AS g ' . "LEFT JOIN {pre}member_price AS mp " . "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ";
        $sql .= ' WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND ' . $type;
        $sql .= ' ORDER BY g.sort_order, g.last_update DESC limit ' . $start . ', ' . $limit;

        $result = $this->model->query($sql);
        foreach ($result as $key => $vo) {
            if ($vo['promote_price'] > 0) {
                $promote_price = bargain_price($vo['promote_price'], $vo['promote_start_date'], $vo['promote_end_date']);
                $goods[$key]['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
            } else {
                $goods[$key]['promote_price'] = '';
            }
            $goods[$key]['id'] = $vo['goods_id'];
            $goods[$key]['name'] = $vo['goods_name'];
            $goods[$key]['brief'] = $vo['goods_brief'];
            $goods[$key]['goods_style_name'] = add_style($vo['goods_name'], $vo['goods_name_style']);
            $goods[$key]['short_name'] = C('goods_name_length') > 0 ? sub_str($vo['goods_name'], C('goods_name_length')) : $vo['goods_name'];
            $goods[$key]['short_style_name'] = add_style($goods[$key] ['short_name'], $vo['goods_name_style']);
            $goods[$key]['market_price'] = price_format($vo['market_price']);
            $goods[$key]['shop_price'] = price_format($vo['shop_price']);
            $goods[$key]['thumb'] = get_image_path($vo['goods_id'], $vo['goods_thumb'], true);
            $goods[$key]['goods_img'] = get_image_path($vo['goods_id'], $vo['goods_img']);
            $goods[$key]['url'] = url('goods/index', array('id' => $vo['goods_id']));
            $goods[$key]['sales_count'] = get_sales_count($vo['goods_id']);
            $goods[$key]['sc'] = get_goods_collect($vo['goods_id']);
            $goods[$key]['mysc'] = 0;
            // 检查是否已经存在于用户的收藏夹
            if ($_SESSION ['user_id']) {
                // 用户自己有没有收藏过
                $condition['goods_id'] = $vo['goods_id'];
                $condition['user_id'] = $_SESSION ['user_id'];
                $rs = $this->model->table('collect_goods')->where($condition)->count();
                $goods[$key]['mysc'] = $rs;
            }
            $goods[$key]['promotion'] = get_promotion_show($vo['goods_id']);
            $type_goods[$type][] = $goods[$key];
        }
        return $type_goods[$type];
    }

    /**
     * 获得促销商品
     *
     * @access  public
     * @return  array
     */
    function get_promote_goods($cats = '') {
        $time = gmtime();
        $order_type = C('recommend_order');

        /* 取得促销lbi的数量限制 */
        $num = model('Common')->get_library_number("recommend_promotion");
        $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.shop_price AS org_price, g.promote_price, ' .
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, " .
                "promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, goods_img, b.brand_name, " .
                "g.is_best, g.is_new, g.is_hot, g.is_promote, RAND() AS rnd " .
                'FROM ' . $this->pre . 'goods AS g ' .
                'LEFT JOIN ' . $this->pre . 'brand AS b ON b.brand_id = g.brand_id ' .
                "LEFT JOIN " . $this->pre . "member_price AS mp " .
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
                'WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' .
                " AND g.is_promote = 1 AND promote_start_date <= '$time' AND promote_end_date >= '$time' ";
        $sql .= $order_type == 0 ? ' ORDER BY g.sort_order, g.last_update DESC' : ' ORDER BY rnd';
        $sql .= " LIMIT $num ";
        $result = $this->model->query($sql);

        $goods = array();
        foreach ($result AS $idx => $row) {
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
            $goods[$idx]['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
            $goods[$idx]['short_name'] = C('goods_name_length') > 0 ? sub_str($row['goods_name'], C('goods_name_length')) : $row['goods_name'];
            $goods[$idx]['short_style_name'] = add_style($goods[$idx]['short_name'], $row['goods_name_style']);
            $goods[$idx]['market_price'] = price_format($row['market_price']);
            $goods[$idx]['shop_price'] = price_format($row['shop_price']);
            $goods[$idx]['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
            $goods[$idx]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
            $goods[$idx]['url'] = url('goods/index', array('id' => $row['goods_id']));
        }

        return $goods;
    }

    /**
     * 首页推荐分类
     * @return type
     *  by Leah
     */
    function get_recommend_res() {
        $cat_recommend_res = $this->model->query("SELECT c.cat_id, c.cat_name, cr.recommend_type FROM {pre}cat_recommend AS cr INNER JOIN {pre}category AS c ON cr.cat_id=c.cat_id AND c.is_show = 1 ORDER BY c.sort_order ASC, c.cat_id ASC");
        if (!empty($cat_recommend_res)) {
            $cat_rec = array();
            foreach ($cat_recommend_res as $cat_recommend_data) {
                $cat_rec[$cat_recommend_data['recommend_type']][] = array(
                    'cat_id' => $cat_recommend_data['cat_id'], 
                    'cat_name' => $cat_recommend_data['cat_name'],
                    'url' => url('category/index', array('id' => $cat_recommend_data['cat_id'])), 
                    'child_id' => get_parent_id_tree($cat_recommend_data['cat_id']), 
                    'goods_list' => assign_cat_goods($cat_recommend_data['cat_id'],3),
                    'cat_image' => get_banner_path(get_cat_image($cat_recommend_data['cat_id'])),
                    
                );
            }
            return $cat_rec;
        }
    }

}
