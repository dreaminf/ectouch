<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 取得页面标题
 *
 * @access  public
 * @return  string
 */
function get_page_title($cat = 0, $str = '') {
    /* 初始化“页面标题”和“当前位置” */
    $page_title = C('shop_title');
    $ur_here = '<a href="' . __ROOT__ . '">' . L('home') . '</a>';
    /* 控制器名称 */
    $controller_name = strtolower(CONTROLLER_NAME);
    /* 处理有分类的 */
    if (in_array($controller_name, array('category', 'goods', 'article', 'brand'))) {
        /* 商品分类或商品 */
        if ('category' == $controller_name || 'goods' == $controller_name || 'brand' == $controller_name) {
            if ($cat > 0) {
                $cat_arr = get_parent_cats($cat);
                $key = 'cid';
                $type = 'category/index';
            } else {
                $cat_arr = array();
            }
        } elseif ('article' == $controller_name) { /* 文章分类或文章 */
            if ($cat > 0) {
                $cat_arr = get_article_parent_cats($cat);
                $key = 'acid';
                $type = 'article/index';
            } else {
                $cat_arr = array();
            }
        }
        /* 循环分类 */
        if (!empty($cat_arr)) {
            krsort($cat_arr);
            foreach ($cat_arr AS $val) {
                $page_title = htmlspecialchars($val['cat_name']) . '_' . $page_title;
                $args = array($key => $val['cat_id']);
                $ur_here .= ' <code>&gt;</code> <a href="' . url($type, $args) . '">' . htmlspecialchars($val['cat_name']) . '</a>';
            }
        }
    } else { /* 处理无分类的 */
        /* 团购 */
        if ('groupbuy' == $controller_name) {
            $page_title = L('group_buy_goods') . '_' . $page_title;
            $args = array('gbid' => '0');
            $ur_here .= ' <code>&gt;</code> <a href="' . url('groupbuy/index', $args) . '">' . L('group_buy_goods') . '</a>';
        }
        /* 拍卖 */ elseif ('auction' == $controller_name) {
        $page_title = L('auction') . '_' . $page_title;
        $args = array('auid' => '0');
        $ur_here .= ' <code>&gt;</code> <a href="' . url('auction/index', $args) . '">' . L('auction') . '</a>';
        }
        /* 夺宝 */ elseif ('snatch' == $controller_name) {
        $page_title = L('snatch') . '_' . $page_title;
        $args = array('id' => '0');
        $ur_here .= ' <code> &gt; </code><a href="' . url('snatch/index', $args) . '">' . L('snatch') . '</a>';
        }
        /* 批发 */ elseif ('wholesale' == $controller_name) {
        $page_title = L('wholesale') . '_' . $page_title;
        $args = array('wsid' => '0');
        $ur_here .= ' <code>&gt;</code> <a href="' . url('wholesale/index', $args) . '">' . L('wholesale') . '</a>';
        }
        /* 积分兑换 */ elseif ('exchange' == $controller_name) {
        $page_title = L('exchange') . '_' . $page_title;
        $args = array('wsid' => '0');
        $ur_here .= ' <code>&gt;</code> <a href="' . url('exchange/index', $args) . '">' . L('exchange') . '</a>';
        }
        /* 其他的在这里补充 */
    }

    /* 处理最后一部分 */
    if (!empty($str)) {
        $page_title = $str . '_' . $page_title;
        $ur_here .= ' <code>&gt;</code> ' . $str;
    }

    /* 返回值 */
    return array('title' => $page_title, 'ur_here' => $ur_here);
}

/**
 * 获得分类的信息
 *
 * @param integer $cat_id
 *
 * @return void
 */
function get_cat_info($cat_id) {
    $global = getInstance();
    return $global->model->queryRow("SELECT cat_name, keywords, cat_desc, style, grade, filter_attr, parent_id FROM  {pre}category WHERE cat_id = '$cat_id'");
}

/**
 * 取得最近的上级分类的grade值
 *
 * @access  public
 * @param   int     $cat_id    //当前的cat_id
 *
 * @return int
 */
function get_parent_grade($cat_id) {
    $global = getInstance();
    
    static $res = NULL;

    if ($res === NULL) {
        $data = read_static_cache('cat_parent_grade');
        if ($data === false) {
            $sql = "SELECT parent_id, cat_id, grade " .
                " FROM {pre}category";
            $res = $global->model->query($sql);
            write_static_cache('cat_parent_grade', $res);
        } else {
            $res = $data;
        }
    }

    if (!$res) {
        return 0;
    }

    $parent_arr = array();
    $grade_arr = array();

    foreach ($res as $val) {
        $parent_arr[$val['cat_id']] = $val['parent_id'];
        $grade_arr[$val['cat_id']] = $val['grade'];
    }

    while ($parent_arr[$cat_id] > 0 && $grade_arr[$cat_id] == 0) {
        $cat_id = $parent_arr[$cat_id];
    }

    return $grade_arr[$cat_id];
}

/**
 * 获取一级分类信息
 */
function get_top_category() {
    $global = getInstance();
    $sql = 'SELECT c.cat_id,c.cat_name,c.parent_id,c.is_show ' .
        'FROM {pre}category as c ' .
        "WHERE c.parent_id = 0 AND c.is_show = 1 ORDER BY c.sort_order ASC, c.cat_id ASC";

    $res = $global->model->query($sql);

    foreach ($res AS $row) {
        if ($row['is_show']) {
            $cat_arr[$row['cat_id']]['id'] = $row['cat_id'];
            $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
            //$cat_arr[$row['cat_id']]['cat_image'] = get_image_path(0,$row['cat_image'],false);
            $cat_arr[$row['cat_id']]['url'] = url('category/index', array('id' => $row['cat_id']));
        }
    }
    return $cat_arr;
}

/**
 *
 * @access private
 * @param string $children
 * @param unknown $brand
 */
function category_get_count($children,$brand, $type, $min, $max, $ext, $keyword) {

    $global = getInstance();
    
    $where = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND " . "g.is_delete = 0 ";
    if ($keyword != '') {
        $where .= " AND (( 1 " . $keyword . " ) ) ";
    } else {
        $where.=" AND ($children OR " .get_extension_goods($children) . ') ';
    }
    if ($type) {
        switch ($type) {
            case 'best':
                $where .= ' AND g.is_best = 1';
                break;
            case 'new':
                $where .= ' AND g.is_new = 1';
                break;
            case 'hot':
                $where .= ' AND g.is_hot = 1';
                break;
            case 'promotion':
                $time = gmtime();
                $where .= " AND g.promote_price > 0 AND g.promote_start_date <= '$time' AND g.promote_end_date >= '$time'";
                break;
            default:
                $where .= '';
        }
    }
    if ($brand > 0) {
        $where .= "AND g.brand_id=$brand ";
    }
    if ($min > 0) {
        $where .= " AND g.shop_price >= $min ";
    }
    if ($max > 0) {
        $where .= " AND g.shop_price <= $max";
    }


    $sql = 'SELECT COUNT(*) as count FROM {pre}goods AS g LEFT JOIN {pre}member_price AS mp ' . "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " . "WHERE $where $ext ";
    $res = $global->model->queryRow($sql);
    return $res['count'];
}

/**
 * 获取商品所有评论数量 by Leah
 * @param type $goods_id
 * @param type $type
 * @return type
 */
function get_goods_comment($goods_id, $type) {
    $global = getInstance();
    $sql = "SELECT COUNT(*) as count FROM {pre}comment WHERE id_value = '$goods_id' AND comment_type = '$type' AND status = 1 AND parent_id = 0" .
    ' ORDER BY comment_id DESC';
    $result = $global->model->queryRow($sql);
    return $result['count'];
}

/**
 * 获取商品好评百分比 by Leah
 * @param type $goods_id
 * @param type $type
 * @return type
 */
function favorable_comment($goods_id, $type) {
    $global = getInstance();
    $sql = "SELECT COUNT(*) as count FROM {pre}comment WHERE id_value = '$goods_id' AND comment_type = '$type' AND status = 1 AND parent_id = 0" .
    ' ORDER BY comment_id DESC';
    $result = $global->model->queryRow($sql);
    $count = $result['count'];

    $sql = "SELECT COUNT(*) as count FROM {pre}comment WHERE id_value = '$goods_id' AND comment_type = '$type' AND (comment_rank= 5 OR comment_rank = 4) AND status = 1 AND parent_id = 0" .
    ' ORDER BY comment_id DESC';
    $goods_result = $global->model->queryRow($sql);
    $good_count = $goods_result['count'];
    $round = 100;
    if ($count > 0) {
        $round = round(($good_count / $count) * 100);
    }
    return $round;
}

/**
 * 取得某用户等级当前时间可以享受的优惠活动
 *
 * @param int $user_rank
 *        	用户等级id，0表示非会员
 * @return array
 */
function favourable_list_flow($user_rank) {
    $global = getInstance();
    /* 购物车中已有的优惠活动及数量 */
    $used_list = cart_favourable();
    /* 当前用户可享受的优惠活动 */
    $favourable_list = array();
    $user_rank = ',' . $user_rank . ',';
    $now = gmtime();
    $sql = "SELECT * " . "FROM {pre}favourable_activity WHERE CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'" . " AND start_time <= '$now' AND end_time >= '$now'" . " AND act_type = '" . FAT_GOODS . "'" . " ORDER BY sort_order";
    $res = $global->model->query($sql);
    foreach ($res as $favourable) {
        $favourable ['start_time'] = local_date(C('time_format'), $favourable ['start_time']);
        $favourable ['end_time'] = local_date(C('time_format'), $favourable ['end_time']);
        $favourable ['formated_min_amount'] = price_format($favourable ['min_amount'], false);
        $favourable ['formated_max_amount'] = price_format($favourable ['max_amount'], false);
        $favourable ['gift'] = unserialize($favourable ['gift']);
        foreach ($favourable ['gift'] as $key => $value) {
            $favourable ['gift'] [$key] ['formated_price'] = price_format($value ['price'], false);
            $sql = "SELECT COUNT(*) as count FROM {pre}goods WHERE is_on_sale = 1 AND goods_id = " . $value ['id'];
            $res = $global->model->queryRow($sql);
            $is_sale = $res['count'];
            if (!$is_sale) {
                unset($favourable ['gift'] [$key]);
            }
        }
        $favourable ['act_range_desc'] = act_range_desc($favourable);
        $favourable ['act_type_desc'] = sprintf(L('fat_ext.' . $favourable ['act_type']), $favourable ['act_type_ext']);
        /* 是否能享受 */
        $favourable ['available'] = favourable_available($favourable);
        if ($favourable ['available']) {
            /* 是否尚未享受 */
            $favourable ['available'] = !favourable_used($favourable, $used_list);
        }
        if (!$favourable ['available']) {
            continue;
        }
        $favourable_list [] = $favourable;
    }
    return $favourable_list;
}

/**
 * 取得购物车中已有的优惠活动及数量
 *
 * @return array
 */
function cart_favourable() {
    $global = getInstance();
    $list = array();
    $sql = "SELECT is_gift, COUNT(*) AS num " . "FROM {pre}cart  WHERE session_id = '" . SESS_ID . "'" . " AND rec_type = '" . CART_GENERAL_GOODS . "'" . " AND is_gift > 0" . " GROUP BY is_gift";
    $res = $global->model->query($sql);
    foreach ($res as $row) {
        $list [$row ['is_gift']] = $row ['num'];
    }
    return $list;
}

/**
 * 取得购物车中某优惠活动范围内的总金额
 *
 * @param array $favourable
 *        	优惠活动
 * @return float
 */
function cart_favourable_amount($favourable) {
    $global = getInstance();
    /* 查询优惠范围内商品总额的sql */
    $sql = "SELECT SUM(c.goods_price * c.goods_number) as sum " . "FROM {pre}cart AS c, {pre}goods AS g " . "WHERE c.goods_id = g.goods_id " . "AND c.session_id = '" . SESS_ID . "' " . "AND c.rec_type = '" . CART_GENERAL_GOODS . "' " . "AND c.is_gift = 0 " . "AND c.goods_id > 0 ";

    /* 根据优惠范围修正sql */
    if ($favourable ['act_range'] == FAR_ALL) {
        // sql do not change
    } elseif ($favourable ['act_range'] == FAR_CATEGORY) {
        /* 取得优惠范围分类的所有下级分类 */
        $id_list = array();
        $cat_list = explode(',', $favourable ['act_range_ext']);
        foreach ($cat_list as $id) {
            $id_list = array_merge($id_list, array_keys(cat_list(intval($id), 0, false)));
        }

        $sql .= "AND g.cat_id " . db_create_in($id_list);
    } elseif ($favourable ['act_range'] == FAR_BRAND) {
        $id_list = explode(',', $favourable ['act_range_ext']);

        $sql .= "AND g.brand_id " . db_create_in($id_list);
    } else {
        $id_list = explode(',', $favourable ['act_range_ext']);

        $sql .= "AND g.goods_id " . db_create_in($id_list);
    }
    $res = $global->model->queryRow($sql);
    /* 优惠范围内的商品总额 */
    return $res['sum'];
}

/**
 * 取得优惠范围描述
 *
 * @param array $favourable
 *        	优惠活动
 * @return string
 */
function act_range_desc($favourable) {

    if ($favourable ['act_range'] == FAR_BRAND) {
        $condition = "brand_id " . db_create_in($favourable ['act_range_ext']);
        $field = 'brand_name';
        $this->table = 'brand';
        $array = $this->gecol($condition, $field);
        $array = $array ? $array : array();
        return join(',', $array);
    } elseif ($favourable ['act_range'] == FAR_CATEGORY) {
        $this->table = 'category';
        $condition = "cat_id " . db_create_in($favourable ['act_range_ext']);
        $field = 'cat_name';
        $array = $this->gecol($condition, $field);
        $array = $array ? $array : array();
        return join(',', $array);
    } elseif ($favourable ['act_range'] == FAR_GOODS) {
        $this->table = 'goods';
        $condition = "goods_id " . db_create_in($favourable ['act_range_ext']);
        $field = 'goods_name';
        $array = $this->gecol($condition, $field);
        $array = $array ? $array : array();
        return join(',', $array);
    } else {

        return '';
    }
}

/**
 * 根据购物车判断是否可以享受某优惠活动
 *
 * @param array $favourable
 *        	优惠活动信息
 * @return bool
 */
function favourable_available($favourable) {
    /* 会员等级是否符合 */
    $user_rank = $_SESSION ['user_rank'];
    if (strpos(',' . $favourable ['user_rank'] . ',', ',' . $user_rank . ',') === false) {
        return false;
    }

    /* 优惠范围内的商品总额 */
    $amount = $this->cart_favourable_amount($favourable);

    /* 金额上限为0表示没有上限 */
    return $amount >= $favourable ['min_amount'] && ($amount <= $favourable ['max_amount'] || $favourable ['max_amount'] == 0);
}


/**
 * 购物车中是否已经有某优惠
 *
 * @param array $favourable
 *        	优惠活动
 * @param array $cart_favourable购物车中已有的优惠活动及数量
 */
function favourable_used($favourable, $cart_favourable) {
    if ($favourable ['act_type'] == FAT_GOODS) {
        return isset($cart_favourable [$favourable ['act_id']]) && $cart_favourable [$favourable ['act_id']] >= $favourable ['act_type_ext'] && $favourable ['act_type_ext'] > 0;
    } else {
        return isset($cart_favourable [$favourable ['act_id']]);
    }
}

/**
 * 根据商品id获取购物车中此id的数量
 */
function get_goods_number($goods_id) {
    $global = getInstance();
    // 查询
    $sql = "SELECT IFNULL(SUM(goods_number), 0) as number " .
        " FROM {pre}cart WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GENERAL_GOODS . "' AND goods_id = " . $goods_id;
    $res = $global->model->queryRow($sql);
    return $res['number'];
}

/**
 * 调用购物车商品数目
 */
function insert_cart_info_number() {
    $global = getInstance();
    $sql = 'SELECT SUM(goods_number) AS number FROM {pre}cart ' .
        " WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GENERAL_GOODS . "'";
    $res = $global->model->queryRow($sql);
    $number = $res['number'];
    return intval($number);
}

/**
 * 删除购物车中的商品
 *
 * @access public
 * @param integer $id
 * @return void
 */
function flow_drop_cart_goods($id) {
    $global = getInstance();
    /* 取得商品id */
    $sql = "SELECT * FROM {pre}cart WHERE rec_id = '$id'";
    $row = $global->model->queryRow($sql);
    if ($row) {
        // 如果是超值礼包
        if ($row ['extension_code'] == 'package_buy') {
            $sql = "DELETE FROM {pre}cart WHERE session_id = '" . SESS_ID . "' " . "AND rec_id = '$id' LIMIT 1";
        }
        // 如果是普通商品，同时删除所有赠品及其配件
        elseif ($row ['parent_id'] == 0 && $row ['is_gift'] == 0) {
            /* 检查购物车中该普通商品的不可单独销售的配件并删除 */
            $sql = "SELECT c.rec_id
				FROM {pre}cart AS c, {pre}group_goods AS gg, {pre}goods AS g
				WHERE gg.parent_id = '" . $row ['goods_id'] . "'
				AND c.goods_id = gg.goods_id
				AND c.parent_id = '" . $row ['goods_id'] . "'
				AND c.extension_code <> 'package_buy'
				AND gg.goods_id = g.goods_id
				AND g.is_alone_sale = 0";
            $res = $global->model->query($sql);
            $_del_str = $id . ',';
            foreach ($res as $id_alone_sale_goods) {
                $_del_str .= $id_alone_sale_goods ['rec_id'] . ',';
            }
            $_del_str = trim($_del_str, ',');

            $sql = "DELETE FROM {pre}cart WHERE session_id = '" . SESS_ID . "' " . "AND (rec_id IN ($_del_str) OR parent_id = '$row[goods_id]' OR is_gift <> 0)";
        }
        // 如果不是普通商品，只删除该商品即可
        else {
            $sql = "DELETE FROM {pre}cart WHERE session_id = '" . SESS_ID . "' " . "AND rec_id = '$id' LIMIT 1";
        }
        $global->model->query($sql);
    }
    //删除购物车中不能单独销售的商品
    flow_clear_cart_alone();
}

/**
 * 删除购物车中不能单独销售的商品
 *
 * @access public
 * @return void
 */
function flow_clear_cart_alone() {
    $global = getInstance();
    /* 查询：购物车中所有不可以单独销售的配件 */
    $sql = "SELECT c.rec_id, gg.parent_id
		FROM {pre}cart AS c
		LEFT JOIN {pre}group_goods AS gg ON c.goods_id = gg.goods_id
		LEFT JOIN {pre}goods AS g ON c.goods_id = g.goods_id
		WHERE c.session_id = '" . SESS_ID . "'
		AND c.extension_code <> 'package_buy'
		AND gg.parent_id > 0
		AND g.is_alone_sale = 0";
    $res = $global->model->query($sql);
    $rec_id = array();
    foreach ($res as $row) {
        $rec_id [$row ['rec_id']] [] = $row ['parent_id'];
    }
    if (empty($rec_id)) {
        return;
    }

    /* 查询：购物车中所有商品 */
    $sql = "SELECT DISTINCT goods_id
		FROM {pre}cart WHERE session_id = '" . SESS_ID . "'
		AND extension_code <> 'package_buy'";
    $res = $global->model->query($sql);
    $cart_good = array();
    foreach ($res as $row) {
        $cart_good [] = $row ['goods_id'];
    }
    if (empty($cart_good)) {
        return;
    }

    /* 如果购物车中不可以单独销售配件的基本件不存在则删除该配件 */
    $del_rec_id = '';
    foreach ($rec_id as $key => $value) {
        foreach ($value as $v) {
            if (in_array($v, $cart_good)) {
                continue 2;
            }
        }

        $del_rec_id = $key . ',';
    }
    $del_rec_id = trim($del_rec_id, ',');

    if ($del_rec_id == '') {
        return;
    }

    /* 删除 */
    $sql = "DELETE FROM {pre}cart WHERE session_id = '" . SESS_ID . "'
    AND rec_id IN ($del_rec_id)";
    $global->model->query($sql);
}


/**
 * 获得指定商品相关的商品
 *
 * @access  public
 * @param   integer $goods_id
 * @return  array
 */
function get_linked_goods($goods_id)
{
    $global = getInstance();
    $sql = "SELECT lg.link_goods_id AS goods_id, g.goods_name, lg.is_double " .
        "FROM {pre}link_goods AS lg, {pre}goods AS g " .
        "WHERE lg.goods_id = '$goods_id' " .
        "AND lg.link_goods_id = g.goods_id ";
    if ($goods_id == 0)
    {
        $sql .= " AND lg.admin_id = '$_SESSION[admin_id]'";
    }
    $row = $global->model->query($sql);

    foreach ($row AS $key => $val)
    {
        $linked_type = $val['is_double'] == 0 ? L('single') : L('double');

        $row[$key]['goods_name'] = $val['goods_name'] . " -- [$linked_type]";

        unset($row[$key]['is_double']);
    }

    return $row;
}
/**
 * 比较优惠活动的函数，用于排序（把可用的排在前面）
 *
 * @param array $a
 *        	优惠活动a
 * @param array $b
 *        	优惠活动b
 * @return int 相等返回0，小于返回-1，大于返回1
 */
 function cmp_favourable($a, $b) {
    if ($a ['available'] == $b ['available']) {
        if ($a ['sort_order'] == $b ['sort_order']) {
            return 0;
        } else {
            return $a ['sort_order'] < $b ['sort_order'] ? - 1 : 1;
        }
    } else {
        return $a ['available'] ? - 1 : 1;
    }
}

/**
 * 获取地区名称
 * @param type $id
 * @return type
 */
function get_region_name($id = 0) {
    $global = getDbInstance();
    $condition['region_id'] = $id;
   return $global->model->table('region')->field('region_name')->where($condition)->getField();
}

/**
 * 取得收货人地址列表
 * @param   int     $user_id    用户编号
 * @param   int     $id         收货地址id
 * @return  array
 */
function get_consignee_list_p($user_id, $id = 0, $num = 10, $start = 0) {
    $global = getInstance();
    if ($id) {
        $where['address_id'] = $id;
        $global->model->table = 'user_address';
        return $global->model->find($where);
    } else {
        $sql = 'select * from {pre}user_address where user_id = ' . $user_id . ' order by address_id limit ' . $start . ', ' . $num;
        return $global->model->query($sql);
    }
}

/**
 * 检查订单中商品库存
 *
 * @access  public
 * @param   array   $arr
 *
 * @return  void
 */
function flow_cart_stock($arr) {
    $global = getInstance();
    foreach ($arr AS $key => $val) {
        $val = intval(make_semiangle($val));
        if ($val <= 0 || !is_numeric($key)) {
            continue;
        }

        $sql = "SELECT `goods_id`, `goods_attr_id`, `extension_code` FROM {pre}cart WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
        $goods = $global->model->queryRow($sql);

        $sql = "SELECT g.goods_name, g.goods_number, c.product_id " .
            "FROM {pre}goods AS g, {pre}cart AS c " .
            "WHERE g.goods_id = c.goods_id AND c.rec_id = '$key'";
        $row = $global->model->queryRow($sql);

        //系统启用了库存，检查输入的商品数量是否有效
        if (intval(C('use_storage')) > 0 && $goods['extension_code'] != 'package_buy') {
            if ($row['goods_number'] < $val) {
                show_message(sprintf(L('stock_insufficiency'), $row['goods_name'], $row['goods_number'], $row['goods_number']));
                exit;
            }

            /* 是货品 */
            $row['product_id'] = trim($row['product_id']);
            if (!empty($row['product_id'])) {
                $sql = "SELECT product_number FROM {pre}products WHERE goods_id = '" . $goods['goods_id'] . "' AND product_id = '" . $row['product_id'] . "'";
                $res = $global->model->queryRow($sql);
                $product_number = $res['product_number'];
                if ($product_number < $val) {
                    show_message(sprintf(L('stock_insufficiency'), $row['goods_name'], $row['goods_number'], $row['goods_number']));
                    exit;
                }
            }
        } elseif (intval(C('use_storage')) > 0 && $goods['extension_code'] == 'package_buy') {
            if (judge_package_stock($goods['goods_id'], $val)) {
                show_message(L('package_stock_insufficiency'));
                exit;
            }
        }
    }
}


/**
 * 获得用户的可用积分
 *
 * @access private
 * @return integral
 */
function flow_available_points() {
    $global = getInstance();
    $sql = "SELECT SUM(g.integral * c.goods_number) as sum " . "FROM ".$global->ecs->table('cart')." AS c, ".$global->ecs->table('goods')." AS g " . "WHERE c.session_id = '" . SESS_ID . "' AND c.goods_id = g.goods_id AND c.is_gift = 0 AND g.integral > 0 " . "AND c.rec_type = '" . CART_GENERAL_GOODS . "'";
    
    $res = $global->db->getRow($sql);
    $val = intval($res['sum']);

    return integral_of_value($val);
}

/**
 * 过滤表字段
 * @param type $table
 * @param type $data
 * @return type
 */
function filter_field($table, $data) {
    $global = getInstance();
    $field = $global->model->table($table)->getFields();
    $res = array();
    foreach ($field as $field_name) {
        if (array_key_exists($field_name['Field'], $data) == true) {
            $res[$field_name['Field']] = $data[$field_name['Field']];
        }
    }
    return $res;
}

/**
 * 获得指定商品的关联商品
 *
 * @access public
 * @param integer $goods_id
 * @return array
 */
function get_related_goods($goods_id) {
    $global = getInstance();
    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, g.goods_img, g.shop_price AS org_price, ' . "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, " . 'g.market_price, g.promote_price, g.promote_start_date, g.promote_end_date ' . 'FROM ' . $global->ecs->table('link_goods') . ' AS lg ' . 'LEFT JOIN ' . $global->ecs->table('goods') . ' AS g ON g.goods_id = lg.link_goods_id ' . "LEFT JOIN " . $global->ecs->table('member_price') . " AS mp " . "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " . "WHERE lg.goods_id = '$goods_id' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 " . "LIMIT " . C('related_goods_number');
    $res = $global->db->getAll($sql);
    $arr = array();
    if ($res){
        foreach ($res as $row) {
            $arr [$row ['goods_id']] ['goods_id'] = $row ['goods_id'];
            $arr [$row ['goods_id']] ['goods_name'] = $row ['goods_name'];
            $arr [$row ['goods_id']] ['short_name'] = C('goods_name_length') > 0 ? sub_str($row ['goods_name'], C('goods_name_length')) : $row ['goods_name'];
            $arr [$row ['goods_id']] ['goods_thumb'] = get_image_path($row ['goods_id'], $row ['goods_thumb'], true);
            $arr [$row ['goods_id']] ['goods_img'] = get_image_path($row ['goods_id'], $row ['goods_img']);
            $arr [$row ['goods_id']] ['market_price'] = price_format($row ['market_price']);
            $arr [$row ['goods_id']] ['shop_price'] = price_format($row ['shop_price']);
            $arr [$row ['goods_id']] ['url'] = url('goods/index', array(
                'id' => $row ['goods_id']
            ));
        
            if ($row ['promote_price'] > 0) {
                $arr [$row ['goods_id']] ['promote_price'] = bargain_price($row ['promote_price'], $row ['promote_start_date'], $row ['promote_end_date']);
                $arr [$row ['goods_id']] ['formated_promote_price'] = price_format($arr [$row ['goods_id']] ['promote_price']);
            } else {
                $arr [$row ['goods_id']] ['promote_price'] = 0;
            }
        }
    }
    
    return $arr;
}

/**
 * 获取商品总的评价详情
 * @param type $id
 * @param type $type
 */
function get_comment_info($id, $type) {
    $global = getInstance();
    $sql = 'SELECT COUNT(*) as count FROM ' . $global->ecs->table('comment') .
    " WHERE id_value = '$id' AND comment_type = '$type' AND status = 1 AND parent_id = 0" .
    ' ORDER BY comment_id DESC';
    $result = $global->db->getRow($sql);
    $info['count'] = $result['count'];

    $sql = 'SELECT COUNT(*) as count FROM ' . $global->ecs->table('comment') .
    " WHERE id_value = '$id' AND comment_type = '$type' AND (comment_rank= 5 OR comment_rank = 4) AND status = 1 AND parent_id = 0" .
    ' ORDER BY comment_id DESC';
    $result = $global->db->getRow($sql);
    $favorable = $result['count'];

    $sql = 'SELECT COUNT(*) as count FROM ' . $global->ecs->table('comment') .
    " WHERE id_value = '$id' AND comment_type = '$type' AND status = 1 AND parent_id = 0 AND(comment_rank = 2 OR comment_rank = 3)" .
    ' ORDER BY comment_id DESC';
    $result = $global->db->getRow($sql);
    $medium = $result['count'];

    $sql = 'SELECT COUNT(*) as count FROM ' . $global->ecs->table('comment') .
    " WHERE id_value = '$id' AND comment_type = '$type' AND status = 1 AND parent_id = 0 AND comment_rank = 1 " .
    ' ORDER BY comment_id DESC';
    $result = $global->db->getRow($sql);
    $bad = $result['count'];

    $info['favorable_count'] = $favorable; //好评数量
    $info['medium_count'] = $medium; //中评数量
    $info['bad_count'] = $bad; //差评数量
    if ($info['count'] > 0) {
        $info['favorable'] = 0;
        if ($favorable) {
            $info['favorable'] = round(($favorable / $info['count']) * 100);  //好评率
        }
        $info['medium'] = 0;
        if ($medium) {
            $info['medium'] = round(($medium / $info['count']) * 100); //中评
        }
        $info['bad'] = 0;
        if ($bad) {
            $info['bad'] = round(($bad / $info['count']) * 100); //差评
        }
    } else {
        $info['favorable'] = 100;
        $info['medium'] = 100;
        $info['bad'] = 100;
    }
    return $info;
}

/**
 * 获得指定商品的关联文章
 *
 * @access public
 * @param integer $goods_id
 * @return void
 */
function get_linked_articles($goods_id) {
    $global = getInstance();
    $sql = 'SELECT a.article_id, a.title, a.file_url, a.open_type, a.add_time ' . 'FROM ' . $global->ecs->table('goods_article') . ' AS g, ' . $global->ecs->table('article') . ' AS a ' . "WHERE g.article_id = a.article_id AND g.goods_id = '$goods_id' AND a.is_open = 1 " . 'ORDER BY a.add_time DESC';
    $res = $global->db->getAll($sql);
    $arr = array();
    foreach ($res as $row) {
        $row ['url'] = $row ['open_type'] != 1 ? url('article/index', array('id' => $row ['article_id'])) : trim($row ['file_url']);
        $row ['add_time'] = local_date(C('date_format'), $row ['add_time']);
        $row ['short_title'] = C('article_title_length') > 0 ? sub_str($row ['title'], C('article_title_length')) : $row ['title'];
        $arr [] = $row;
    }
    return $arr;
}

/**
 * 获得指定商品的各会员等级对应的价格
 *
 * @access public
 * @param integer $goods_id
 * @return array
 */
function get_user_rank_prices($goods_id, $shop_price) {
    $global = getInstance();
    $sql = "SELECT rank_id, IFNULL(mp.user_price, r.discount * $shop_price / 100) AS price, r.rank_name, r.discount " . 'FROM ' . $global->ecs->table('user_rank') . ' AS r ' . 'LEFT JOIN ' . $global->ecs->table('member_price') . " AS mp " . "ON mp.goods_id = '$goods_id' AND mp.user_rank = r.rank_id " . "WHERE r.show_price = 1 OR r.rank_id = '$_SESSION[user_rank]'";
    $res = $global->db->getAll($sql);
    $arr = array();
    foreach ($res as $row) {
        $arr [$row ['rank_id']] = array(
            'rank_name' => htmlspecialchars($row ['rank_name']),
            'price' => price_format($row ['price'])
        );
    }
    return $arr;
}

/**
 * 取得跟商品关联的礼包列表
 *
 * @param string $goods_id
 *        	商品编号
 *
 * @return 礼包列表
 */
function get_package_goods_list($goods_id) {
    $global = getInstance();
    $now = gmtime();
    $sql = "SELECT pg.goods_id, ga.act_id, ga.act_name, ga.act_desc, ga.goods_name, ga.start_time,
					   ga.end_time, ga.is_finished, ga.ext_info
				FROM " . $global->ecs->table('goods_activity') . " AS ga, " . $global->ecs->table('package_goods') . " AS pg
				WHERE pg.package_id = ga.act_id
				AND ga.start_time <= '" . $now . "'
				AND ga.end_time >= '" . $now . "'
				AND pg.goods_id = " . $goods_id . "
				GROUP BY ga.act_id
				ORDER BY ga.act_id ";
    $res = $global->db->getAll($sql);

    foreach ($res as $tempkey => $value) {
        $subtotal = 0;
        $row = unserialize($value ['ext_info']);
        unset($value ['ext_info']);
        if ($row) {
            foreach ($row as $key => $val) {
                $res [$tempkey] [$key] = $val;
            }
        }

        $sql = "SELECT pg.package_id, pg.goods_id, pg.goods_number, pg.admin_id, p.goods_attr, g.goods_sn, g.goods_name, g.market_price, g.goods_thumb, IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS rank_price
        FROM " . $global->ecs->table('package_goods') . " AS pg
						LEFT JOIN " . $global->ecs->table('goods') . " AS g
							ON g.goods_id = pg.goods_id
						LEFT JOIN " . $global->ecs->table('products') . " AS p
							ON p.product_id = pg.product_id
						LEFT JOIN " . $global->ecs->table('member_price') . " AS mp
						ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]'
						    WHERE pg.package_id = " . $value ['act_id'] . "
					ORDER BY pg.package_id, pg.goods_id";

        $goods_res = $global->db->getAll($sql);

        foreach ($goods_res as $key => $val) {
        $goods_id_array [] = $val ['goods_id'];
            $goods_res [$key] ['goods_thumb'] = get_image_path($val ['goods_id'], $val ['goods_thumb'], true);
                $goods_res [$key] ['market_price'] = price_format($val ['market_price']);
                    $goods_res [$key] ['rank_price'] = price_format($val ['rank_price']);
                        $subtotal += $val ['rank_price'] * $val ['goods_number'];
        }

        /* 取商品属性 */
        $sql = "SELECT ga.goods_attr_id, ga.attr_value
					FROM " . $global->ecs->table('goods_attr') . " AS ga, " . $global->ecs->table('attribute') . " AS a
					    WHERE a.attr_id = ga.attr_id
					AND a.attr_type = 1
					AND " . db_create_in($goods_id_array, 'goods_id');
					    $result_goods_attr = $global->db->getAll($sql);

					    $_goods_attr = array();
					    foreach ($result_goods_attr as $value) {
					    $_goods_attr [$value ['goods_attr_id']] = $value ['attr_value'];
                        }

                        /* 处理货品 */
                        $format = '[%s]';
                        foreach ($goods_res as $key => $val) {
                        if ($val ['goods_attr'] != '') {
                        $goods_attr_array = explode('|', $val ['goods_attr']);

                        $goods_attr = array();
                        foreach ($goods_attr_array as $_attr) {
                        $goods_attr [] = $_goods_attr [$_attr];
                        }

                            $goods_res [$key] ['goods_attr_str'] = sprintf($format, implode('，', $goods_attr));
    }
    }

    $res [$tempkey] ['goods_list'] = $goods_res;
        $res [$tempkey] ['subtotal'] = price_format($subtotal);
            $res [$tempkey] ['saving'] = price_format(($subtotal - $res [$tempkey] ['package_price']));
                $res [$tempkey] ['package_price'] = price_format($res [$tempkey] ['package_price']);
    }

    return $res;
}
    
/**
 *  所有的促销活动信息
 * @access  public
 * @return  array
 */
function get_promotion_show($goods_id = '') {
    $global = getInstance();
    $group = array();
    $package = array();
    $favourable = array();
    $gmtime = gmtime();
    $sql = 'SELECT act_id, act_name, act_type, start_time, end_time FROM ' . $global->ecs->table('goods_activity') . " WHERE is_finished=0 AND start_time <= '$gmtime' AND end_time >= '$gmtime'";
    if (!empty($goods_id)) {
        $sql .= " AND goods_id = '$goods_id'";
    }
    $res = $global->db->getAll($sql);
    if (is_array($res))
        foreach ($res as $data) {
            switch ($data['act_type']) {
                case GAT_GROUP_BUY: //团购
                    $group[$data['act_id']]['type'] = 'group_buy';
                    break;
                case GAT_PACKAGE: //礼包
                    $package[$data['act_id']]['type'] = 'package';
                    break;
            }
        }

    $user_rank = ',' . $_SESSION['user_rank'] . ',';
    $favourable = array();
    $sql = 'SELECT act_id, act_range, act_type,act_range_ext, act_name, start_time, end_time FROM ' . $global->ecs->table('favourable_activity') . " WHERE start_time <= '$gmtime' AND end_time >= '$gmtime'";
    if (!empty($goods_id)) {
        $sql .= " AND CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'";
    }
    $res = $global->db->getAll($sql);

    if (empty($goods_id)) {
        foreach ($res as $rows) {
            $favourable[$rows['act_id']]['type'] = 'favourable';
        }
    } else {
        $sql = "SELECT cat_id, brand_id FROM " . $global->ecs->table('goods') . " WHERE goods_id = '$goods_id'";
        $row = $global->db->getRow($sql);
        $category_id = $row['cat_id'];
        $brand_id = $row['brand_id'];

        foreach ($res as $rows) {
            if ($rows['act_range'] == FAR_ALL) {
                $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
            } elseif ($rows['act_range'] == FAR_CATEGORY) {
                /* 找出分类id的子分类id */
                $id_list = array();
                $raw_id_list = explode(',', $rows['act_range_ext']);
                foreach ($raw_id_list as $id) {
                    $id_list = array_merge($id_list, array_keys(cat_list($id, 0, false)));
                }
                $ids = join(',', array_unique($id_list));
                if (strpos(',' . $ids . ',', ',' . $category_id . ',') !== false) {
                    $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                }
            } elseif ($rows['act_range'] == FAR_BRAND) {
                if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $brand_id . ',') !== false) {
                    $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                }
            } elseif ($rows['act_range'] == FAR_GOODS) {
                if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $goods_id . ',') !== false) {
                    $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                }
            }
        }
    }
    $sort_time = array();
    $arr = array_merge($group, $package, $favourable);
    foreach ($arr as $key => $value) {
        $sort_time[] = $value['sort'];
    }
    array_multisort($sort_time, SORT_NUMERIC, SORT_DESC, $arr);

    return array_unique($arr);
}

/**
 * 查询会员账户明细
 * @access  public
 * @param   int     $user_id    会员ID
 * @param   int     $num        每页显示数量
 * @param   int     $start      开始显示的条数
 * @return  array
 */
function get_account_detail($user_id, $num, $start) {
    $global = getInstance();
    // 获取余额记录
    $account_log = array();

    $sql = 'SELECT * FROM ' . $global->ecs->table('account_log') . " WHERE user_id = " . $user_id . ' AND user_money <> 0' .
        " ORDER BY log_id DESC limit " . $start . ',' . $num;
    $res = $global->db->getAll($sql);

    if (empty($res)) {
        return array();
        exit;
    }

    foreach ($res as $k => $v) {
        $res[$k]['change_time'] = local_date(C('date_format'), $v['change_time']);
        $res[$k]['type'] = $v['user_money'] > 0 ? L('account_inc') : L('account_dec');
        //$res[$k]['user_money'] = price_format(abs($v['user_money']), false);
        $res[$k]['user_money'] = $v['user_money'];
        $res[$k]['frozen_money'] = price_format(abs($v['frozen_money']), false);
        $res[$k]['rank_points'] = abs($v['rank_points']);
        $res[$k]['pay_points'] = abs($v['pay_points']);
        $res[$k]['short_change_desc'] = sub_str($v['change_desc'], 60);
        $res[$k]['amount'] = $v['user_money'];
    }

    return $res;
}

/**
 * 获取商品销量总数
 *
 * @access public
 * @param integer $goods_id
 * @return integer
 */
function get_sales_count($goods_id)
{
    $global = getInstance();
    /* 统计时间段 */
    $period = C('top10_time');
    $ext = '';
    if ($period == 1) {// 一年
        $ext = "AND o.add_time >'" . local_strtotime('-1 years') . "'";
    } elseif ($period == 2) {// 半年
        $ext = "AND o.add_time > '" . local_strtotime('-6 months') . "'";
    } elseif ($period == 3) {// 三个月
        $ext = " AND o.add_time > '" . local_strtotime('-3 months') . "'";
    } elseif ($period == 4) {// 一个月
        $ext = " AND o . add_time > '" . local_strtotime(' - 1 months') . "'";
    }
    /* 查询该商品销量 */
    $sql = 'SELECT IFNULL(SUM(g.goods_number), 0) as count ' .
        'FROM {pre}order_info AS o, {pre}order_goods AS g ' .
        "WHERE o.order_id = g . order_id " .
        " AND o.order_status = '" . OS_CONFIRMED . "'" .
        " AND o.shipping_status " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) .
        " AND o.pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) .
        " AND g.goods_id = '$goods_id'";
    $result = $global->model->queryRow($sql);
    return $result['count'];
}

/* 获得指定商品分类的所有分类
     * by Leah
     */

    function get_parent_id_tree($parent_id) {
        $global = getInstance();
        $three_c_arr = array();
        $sql = "SELECT count(*) as count FROM {pre}category WHERE parent_id = '$parent_id' AND is_show = 1 ";
        $res = $global->model->queryRow($sql);

        if ($res['count']) {
            $child_sql = 'SELECT cat_id, cat_name, parent_id, is_show ' .
                    "FROM {pre}category WHERE parent_id = '$parent_id' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC";
            $res = $global->model->query($child_sql);
            foreach ($res AS $row) {
                if ($row['is_show']) {
                    $three_c_arr[$row['cat_id']]['id'] = $row['cat_id'];
                    $three_c_arr[$row['cat_id']]['name'] = $row['cat_name'];
                    $three_c_arr[$row['cat_id']]['url'] = url('category/index', array('id' => $row['cat_id']));
                }
            }
        }
        return $three_c_arr;
    }
	
	/**
     * 获得分类下的小图标
     * @param  integer $cat_id 
     * @return void       
     */
    function get_cat_image($cat_id){ 
        $global = getInstance();
        $cats = $global->model->queryRow("SELECT touch_img FROM {pre}category WHERE cat_id = '$cat_id'");
        return $cats['touch_img'];
    }

/**
 * html代码输出
 * @param unknown $str
 * @return string
 */
function html_out($str) {
    if (function_exists('htmlspecialchars_decode')) {
        $str = htmlspecialchars_decode($str);
    } else {
        $str = html_entity_decode($str);
    }
    $str = stripslashes($str);
    return $str;
}

/**
 * 数据过滤函数
 * @param string|array $data 待过滤的字符串或字符串数组
 * @param boolean $force 为true时忽略get_magic_quotes_gpc
 * @return mixed
 */
function in($data, $force = false) {
    if (is_string($data)) {
        $data = trim(htmlspecialchars($data)); // 防止被挂马，跨站攻击
        if (($force == true) || (!get_magic_quotes_gpc())) {
            $data = addslashes($data); // 防止sql注入
        }
        return $data;
    } else if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = in($value, $force);
        }
        return $data;
    } else {
        return $data;
    }
}

/**
 * 获取touch新增图片地址
 * @param type $img
 * @return type
 */
function get_banner_path($img) {
    $img = empty($img) ? C('no_picture') : $img;
    return $img;
}