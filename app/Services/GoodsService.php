<?php

namespace App\Services;

use App\Repositorys\ArticleRepository;

class GoodsService
{
    private $article;

    public function __construct(ArticleRepository $article)
    {
        $this->article = $article;
    }

    /**
     * @param $condition
     * @return mixed
     */
    public function all($condition = [])
    {
        return $this->article->all($condition);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function create($data)
    {

    }

    /**
     * @param $id
     * @return mixed
     */
    public function detail($id)
    {
        return $this->article->show($id);

    }

    /**
     * @param $data
     * @return mixed
     */
    public function update($data)
    {

    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {

    }


    /**
     * 取得商品优惠价格列表
     *
     * @param   string $goods_id 商品编号
     * @param   string $price_type 价格类别(0为全店优惠比率，1为商品优惠价格，2为分类优惠比率)
     *
     * @return  优惠价格列表
     */
    function get_volume_price_list($goods_id, $price_type = '1')
    {
        $volume_price = [];
        $temp_index = '0';

        $sql = "SELECT `volume_number` , `volume_price`" .
            " FROM " . $GLOBALS['ecs']->table('volume_price') . "" .
            " WHERE `goods_id` = '" . $goods_id . "' AND `price_type` = '" . $price_type . "'" .
            " ORDER BY `volume_number`";

        $res = $GLOBALS['db']->getAll($sql);

        foreach ($res as $k => $v) {
            $volume_price[$temp_index] = [];
            $volume_price[$temp_index]['number'] = $v['volume_number'];
            $volume_price[$temp_index]['price'] = $v['volume_price'];
            $volume_price[$temp_index]['format_price'] = price_format($v['volume_price']);
            $temp_index++;
        }
        return $volume_price;
    }

    /**
     * 取得商品最终使用价格
     *
     * @param   string $goods_id 商品编号
     * @param   string $goods_num 购买数量
     * @param   boolean $is_spec_price 是否加入规格价格
     * @param   mix $spec 规格ID的数组或者逗号分隔的字符串
     *
     * @return  商品最终购买价格
     */
    function get_final_price($goods_id, $goods_num = '1', $is_spec_price = false, $spec = [])
    {
        $final_price = '0'; //商品最终购买价格
        $volume_price = '0'; //商品优惠价格
        $promote_price = '0'; //商品促销价格
        $user_price = '0'; //商品会员价格

        //取得商品优惠价格列表
        $price_list = get_volume_price_list($goods_id, '1');

        if (!empty($price_list)) {
            foreach ($price_list as $value) {
                if ($goods_num >= $value['number']) {
                    $volume_price = $value['price'];
                }
            }
        }

        //取得商品促销价格列表
        // 取得商品信息
        $sql = "SELECT g.promote_price, g.promote_start_date, g.promote_end_date, " .
            "IFNULL(mp.user_price, g.shop_price * '" . session('discount') . "') AS shop_price " .
            " FROM " . $GLOBALS['ecs']->table('goods') . " AS g " .
            " LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '" . session('user_rank') . "' " .
            " WHERE g.goods_id = '" . $goods_id . "'" .
            " AND g.is_delete = 0";
        $goods = $GLOBALS['db']->getRow($sql);

        // 计算商品的促销价格
        if ($goods['promote_price'] > 0) {
            $promote_price = bargain_price($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
        } else {
            $promote_price = 0;
        }

        //取得商品会员价格列表
        $user_price = $goods['shop_price'];

        //比较商品的促销价格，会员价格，优惠价格
        if (empty($volume_price) && empty($promote_price)) {
            //如果优惠价格，促销价格都为空则取会员价格
            $final_price = $user_price;
        } elseif (!empty($volume_price) && empty($promote_price)) {
            //如果优惠价格为空时不参加这个比较。
            $final_price = min($volume_price, $user_price);
        } elseif (empty($volume_price) && !empty($promote_price)) {
            //如果促销价格为空时不参加这个比较。
            $final_price = min($promote_price, $user_price);
        } elseif (!empty($volume_price) && !empty($promote_price)) {
            //取促销价格，会员价格，优惠价格最小值
            $final_price = min($volume_price, $promote_price, $user_price);
        } else {
            $final_price = $user_price;
        }

        //如果需要加入规格价格
        if ($is_spec_price) {
            if (!empty($spec)) {
                $spec_price = spec_price($spec);
                $final_price += $spec_price;
            }
        }

        //返回商品最终购买价格
        return $final_price;
    }

    /**
     * 将 goods_attr_id 的序列按照 attr_id 重新排序
     *
     * 注意：非规格属性的id会被排除
     *
     * @access      public
     * @param       array $goods_attr_id_array 一维数组
     * @param       string $sort 序号：asc|desc，默认为：asc
     *
     * @return      string
     */
    function sort_goods_attr_id_array($goods_attr_id_array, $sort = 'asc')
    {
        if (empty($goods_attr_id_array)) {
            return $goods_attr_id_array;
        }

        //重新排序
        $sql = "SELECT a.attr_type, v.attr_value, v.goods_attr_id
            FROM " . $GLOBALS['ecs']->table('attribute') . " AS a
            LEFT JOIN " . $GLOBALS['ecs']->table('goods_attr') . " AS v
                ON v.attr_id = a.attr_id
                AND a.attr_type = 1
            WHERE v.goods_attr_id " . db_create_in($goods_attr_id_array) . "
            ORDER BY a.attr_id $sort";
        $row = $GLOBALS['db']->getAll($sql);

        $return_arr = [];
        foreach ($row as $value) {
            $return_arr['sort'][] = $value['goods_attr_id'];

            $return_arr['row'][$value['goods_attr_id']] = $value;
        }

        return $return_arr;
    }

    /**
     *
     * 是否存在规格
     *
     * @access      public
     * @param       array $goods_attr_id_array 一维数组
     *
     * @return      string
     */
    function is_spec($goods_attr_id_array, $sort = 'asc')
    {
        if (empty($goods_attr_id_array)) {
            return $goods_attr_id_array;
        }

        //重新排序
        $sql = "SELECT a.attr_type, v.attr_value, v.goods_attr_id
            FROM " . $GLOBALS['ecs']->table('attribute') . " AS a
            LEFT JOIN " . $GLOBALS['ecs']->table('goods_attr') . " AS v
                ON v.attr_id = a.attr_id
                AND a.attr_type = 1
            WHERE v.goods_attr_id " . db_create_in($goods_attr_id_array) . "
            ORDER BY a.attr_id $sort";
        $row = $GLOBALS['db']->getAll($sql);

        $return_arr = [];
        foreach ($row as $value) {
            $return_arr['sort'][] = $value['goods_attr_id'];

            $return_arr['row'][$value['goods_attr_id']] = $value;
        }

        if (!empty($return_arr)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 调用当前分类的销售排行榜
     *
     * @access  public
     * @param   string $cats 查询的分类
     * @return  array
     */
    function get_top10($cats = '')
    {
        $cats = get_children($cats);
        $where = !empty($cats) ? "AND ($cats OR " . get_extension_goods($cats) . ") " : '';

        // 排行统计的时间
        switch ($GLOBALS['_CFG']['top10_time']) {
            case 1: // 一年
                $top10_time = "AND o.order_sn >= '" . date('Ymd', gmtime() - 365 * 86400) . "'";
                break;
            case 2: // 半年
                $top10_time = "AND o.order_sn >= '" . date('Ymd', gmtime() - 180 * 86400) . "'";
                break;
            case 3: // 三个月
                $top10_time = "AND o.order_sn >= '" . date('Ymd', gmtime() - 90 * 86400) . "'";
                break;
            case 4: // 一个月
                $top10_time = "AND o.order_sn >= '" . date('Ymd', gmtime() - 30 * 86400) . "'";
                break;
            default:
                $top10_time = '';
        }

        $sql = 'SELECT g.goods_id, g.goods_name, g.shop_price, g.goods_thumb, SUM(og.goods_number) as goods_number ' .
            'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g, ' .
            $GLOBALS['ecs']->table('order_info') . ' AS o, ' .
            $GLOBALS['ecs']->table('order_goods') . ' AS og ' .
            "WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 $where $top10_time ";
        //判断是否启用库存，库存数量是否大于0
        if ($GLOBALS['_CFG']['use_storage'] == 1) {
            $sql .= " AND g.goods_number > 0 ";
        }
        $sql .= ' AND og.order_id = o.order_id AND og.goods_id = g.goods_id ' .
            "AND (o.order_status = '" . OS_CONFIRMED . "' OR o.order_status = '" . OS_SPLITED . "') " .
            "AND (o.pay_status = '" . PS_PAYED . "' OR o.pay_status = '" . PS_PAYING . "') " .
            "AND (o.shipping_status = '" . SS_SHIPPED . "' OR o.shipping_status = '" . SS_RECEIVED . "') " .
            'GROUP BY g.goods_id ORDER BY goods_number DESC, g.goods_id DESC LIMIT ' . $GLOBALS['_CFG']['top_number'];

        $arr = $GLOBALS['db']->getAll($sql);

        for ($i = 0, $count = count($arr); $i < $count; $i++) {
            $arr[$i]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                sub_str($arr[$i]['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $arr[$i]['goods_name'];
            $arr[$i]['url'] = build_uri('goods', ['gid' => $arr[$i]['goods_id']], $arr[$i]['goods_name']);
            $arr[$i]['thumb'] = get_image_path($arr[$i]['goods_thumb']);
            $arr[$i]['price'] = price_format($arr[$i]['shop_price']);
        }

        return $arr;
    }

    /**
     * 获得推荐商品
     *
     * @access  public
     * @param   string $type 推荐类型，可以是 best, new, hot
     * @return  array
     */
    function get_recommend_goods($type = '', $cats = '')
    {
        if (!in_array($type, ['best', 'new', 'hot'])) {
            return [];
        }

        //取不同推荐对应的商品
        static $type_goods = [];
        if (empty($type_goods[$type])) {
            //初始化数据
            $type_goods['best'] = [];
            $type_goods['new'] = [];
            $type_goods['hot'] = [];
            $data = read_static_cache('recommend_goods');
            if ($data === false) {
                $sql = 'SELECT g.goods_id, g.is_best, g.is_new, g.is_hot, g.is_promote, b.brand_name,g.sort_order ' .
                    ' FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
                    ' LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' .
                    ' WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND (g.is_best = 1 OR g.is_new =1 OR g.is_hot = 1)' .
                    ' ORDER BY g.sort_order, g.last_update DESC';
                $goods_res = $GLOBALS['db']->getAll($sql);
                //定义推荐,最新，热门，促销商品
                $goods_data['best'] = [];
                $goods_data['new'] = [];
                $goods_data['hot'] = [];
                $goods_data['brand'] = [];
                if (!empty($goods_res)) {
                    foreach ($goods_res as $data) {
                        if ($data['is_best'] == 1) {
                            $goods_data['best'][] = ['goods_id' => $data['goods_id'], 'sort_order' => $data['sort_order']];
                        }
                        if ($data['is_new'] == 1) {
                            $goods_data['new'][] = ['goods_id' => $data['goods_id'], 'sort_order' => $data['sort_order']];
                        }
                        if ($data['is_hot'] == 1) {
                            $goods_data['hot'][] = ['goods_id' => $data['goods_id'], 'sort_order' => $data['sort_order']];
                        }
                        if ($data['brand_name'] != '') {
                            $goods_data['brand'][$data['goods_id']] = $data['brand_name'];
                        }
                    }
                }
                write_static_cache('recommend_goods', $goods_data);
            } else {
                $goods_data = $data;
            }

            $time = gmtime();
            $order_type = $GLOBALS['_CFG']['recommend_order'];

            //按推荐数量及排序取每一项推荐显示的商品 order_type可以根据后台设定进行各种条件显示
            static $type_array = [];
            $type2lib = ['best' => 'recommend_best', 'new' => 'recommend_new', 'hot' => 'recommend_hot'];
            if (empty($type_array)) {
                foreach ($type2lib as $key => $data) {
                    if (!empty($goods_data[$key])) {
                        $num = get_library_number($data);
                        $data_count = count($goods_data[$key]);
                        $num = $data_count > $num ? $num : $data_count;
                        if ($order_type == 0) {
                            //usort($goods_data[$key], 'goods_sort');
                            $rand_key = array_slice($goods_data[$key], 0, $num);
                            foreach ($rand_key as $key_data) {
                                $type_array[$key][] = $key_data['goods_id'];
                            }
                        } else {
                            $rand_key = array_rand($goods_data[$key], $num);
                            if ($num == 1) {
                                $type_array[$key][] = $goods_data[$key][$rand_key]['goods_id'];
                            } else {
                                foreach ($rand_key as $key_data) {
                                    $type_array[$key][] = $goods_data[$key][$key_data]['goods_id'];
                                }
                            }
                        }
                    } else {
                        $type_array[$key] = [];
                    }
                }
            }

            //取出所有符合条件的商品数据，并将结果存入对应的推荐类型数组中
            $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.shop_price AS org_price, g.promote_price, ' .
                "IFNULL(mp.user_price, g.shop_price * '". session('discount') ."') AS shop_price, " .
                "promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img, RAND() AS rnd " .
                'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
                "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '". session('user_rank') ."' ";
            $type_merge = array_merge($type_array['new'], $type_array['best'], $type_array['hot']);
            $type_merge = array_unique($type_merge);
            $sql .= ' WHERE g.goods_id ' . db_create_in($type_merge);
            $sql .= ' ORDER BY g.sort_order, g.last_update DESC';

            $result = $GLOBALS['db']->getAll($sql);
            foreach ($result as $idx => $row) {
                if ($row['promote_price'] > 0) {
                    $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                    $goods[$idx]['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
                } else {
                    $goods[$idx]['promote_price'] = '';
                }

                $goods[$idx]['id'] = $row['goods_id'];
                $goods[$idx]['name'] = $row['goods_name'];
                $goods[$idx]['brief'] = $row['goods_brief'];
                $goods[$idx]['brand_name'] = isset($goods_data['brand'][$row['goods_id']]) ? $goods_data['brand'][$row['goods_id']] : '';
                $goods[$idx]['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);

                $goods[$idx]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                    sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
                $goods[$idx]['short_style_name'] = add_style($goods[$idx]['short_name'], $row['goods_name_style']);
                $goods[$idx]['market_price'] = price_format($row['market_price']);
                $goods[$idx]['shop_price'] = price_format($row['shop_price']);
                $goods[$idx]['thumb'] = get_image_path($row['goods_thumb']);
                $goods[$idx]['goods_img'] = get_image_path($row['goods_img']);
                $goods[$idx]['url'] = build_uri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
                if (in_array($row['goods_id'], $type_array['best'])) {
                    $type_goods['best'][] = $goods[$idx];
                }
                if (in_array($row['goods_id'], $type_array['new'])) {
                    $type_goods['new'][] = $goods[$idx];
                }
                if (in_array($row['goods_id'], $type_array['hot'])) {
                    $type_goods['hot'][] = $goods[$idx];
                }
            }
        }
        return $type_goods[$type];
    }

    /**
     * 获得促销商品
     *
     * @access  public
     * @return  array
     */
    function get_promote_goods($cats = '')
    {
        $time = gmtime();
        $order_type = $GLOBALS['_CFG']['recommend_order'];

        // 取得促销lbi的数量限制
        $num = get_library_number("recommend_promotion");
        $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.shop_price AS org_price, g.promote_price, ' .
            "IFNULL(mp.user_price, g.shop_price * '". session('discount') ."') AS shop_price, " .
            "promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, goods_img, b.brand_name, " .
            "g.is_best, g.is_new, g.is_hot, g.is_promote, RAND() AS rnd " .
            'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' .
            "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '". session('user_rank') ."' " .
            'WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' .
            " AND g.is_promote = 1 AND promote_start_date <= '$time' AND promote_end_date >= '$time' ";
        $sql .= $order_type == 0 ? ' ORDER BY g.sort_order, g.last_update DESC' : ' ORDER BY rnd';
        $sql .= " LIMIT $num ";
        $result = $GLOBALS['db']->getAll($sql);

        $goods = [];
        foreach ($result as $idx => $row) {
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
            $goods[$idx]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $goods[$idx]['short_style_name'] = add_style($goods[$idx]['short_name'], $row['goods_name_style']);
            $goods[$idx]['market_price'] = price_format($row['market_price']);
            $goods[$idx]['shop_price'] = price_format($row['shop_price']);
            $goods[$idx]['thumb'] = get_image_path($row['goods_thumb']);
            $goods[$idx]['goods_img'] = get_image_path($row['goods_img']);
            $goods[$idx]['url'] = build_uri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
        }

        return $goods;
    }

    /**
     * 判断某个商品是否正在特价促销期
     *
     * @access  public
     * @param   float $price 促销价格
     * @param   string $start 促销开始日期
     * @param   string $end 促销结束日期
     * @return  float   如果还在促销期则返回促销价，否则返回0
     */
    function bargain_price($price, $start, $end)
    {
        if ($price == 0) {
            return 0;
        } else {
            $time = gmtime();
            if ($time >= $start && $time <= $end) {
                return $price;
            } else {
                return 0;
            }
        }
    }

    /**
     * 获得指定的规格的价格
     *
     * @access  public
     * @param   mix $spec 规格ID的数组或者逗号分隔的字符串
     * @return  void
     */
    function spec_price($spec)
    {
        if (!empty($spec)) {
            if (is_array($spec)) {
                foreach ($spec as $key => $val) {
                    $spec[$key] = addslashes($val);
                }
            } else {
                $spec = addslashes($spec);
            }

            $where = db_create_in($spec, 'goods_attr_id');

            $sql = 'SELECT SUM(attr_price) AS attr_price FROM ' . $GLOBALS['ecs']->table('goods_attr') . " WHERE $where";
            $price = floatval($GLOBALS['db']->getOne($sql));
        } else {
            $price = 0;
        }

        return $price;
    }


    /**
     * 取得商品信息
     * @param   int $goods_id 商品id
     * @return  array
     */
    function goods_info($goods_id)
    {
        $sql = "SELECT g.*, b.brand_name " .
            "FROM " . $GLOBALS['ecs']->table('goods') . " AS g " .
            "LEFT JOIN " . $GLOBALS['ecs']->table('brand') . " AS b ON g.brand_id = b.brand_id " .
            "WHERE g.goods_id = '$goods_id'";
        $row = $GLOBALS['db']->getRow($sql);
        if (!empty($row)) {
            // 修正重量显示
            $row['goods_weight'] = (intval($row['goods_weight']) > 0) ?
                $row['goods_weight'] . $GLOBALS['_LANG']['kilogram'] :
                ($row['goods_weight'] * 1000) . $GLOBALS['_LANG']['gram'];

            // 修正图片
            $row['goods_img'] = get_image_path($row['goods_img']);
        }

        return $row;
    }

    /**
     * 取得商品属性
     * @param   int $goods_id 商品id
     * @return  array
     */
    function get_goods_attr($goods_id)
    {
        $attr_list = [];
        $sql = "SELECT a.attr_id, a.attr_name " .
            "FROM " . $GLOBALS['ecs']->table('goods') . " AS g, " . $GLOBALS['ecs']->table('attribute') . " AS a " .
            "WHERE g.goods_id = '$goods_id' " .
            "AND g.goods_type = a.cat_id " .
            "AND a.attr_type = 1";
        $attr_id_list = $GLOBALS['db']->getCol($sql);
        $res = $GLOBALS['db']->query($sql);
        foreach ($res as $attr) {
            if (defined('ECS_ADMIN')) {
                $attr['goods_attr_list'] = [0 => $GLOBALS['_LANG']['select_please']];
            } else {
                $attr['goods_attr_list'] = [];
            }
            $attr_list[$attr['attr_id']] = $attr;
        }

        $sql = "SELECT attr_id, goods_attr_id, attr_value " .
            "FROM " . $GLOBALS['ecs']->table('goods_attr') .
            " WHERE goods_id = '$goods_id' " .
            "AND attr_id " . db_create_in($attr_id_list);
        $res = $GLOBALS['db']->query($sql);
        foreach ($res as $goods_attr) {
            $attr_list[$goods_attr['attr_id']]['goods_attr_list'][$goods_attr['goods_attr_id']] = $goods_attr['attr_value'];
        }

        return $attr_list;
    }

    /**
     * 获得购物车中商品的配件
     *
     * @access  public
     * @param   array $goods_list
     * @return  array
     */
    function get_goods_fittings($goods_list = [])
    {
        $temp_index = 0;
        $arr = [];

        $sql = 'SELECT gg.parent_id, ggg.goods_name AS parent_name, gg.goods_id, gg.goods_price, g.goods_name, g.goods_thumb, g.goods_img, g.shop_price AS org_price, ' .
            "IFNULL(mp.user_price, g.shop_price * '". session('discount') ."') AS shop_price " .
            'FROM ' . $GLOBALS['ecs']->table('group_goods') . ' AS gg ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . 'AS g ON g.goods_id = gg.goods_id ' .
            "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
            "ON mp.goods_id = gg.goods_id AND mp.user_rank = '". session('user_rank') ."' " .
            "LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS ggg ON ggg.goods_id = gg.parent_id " .
            "WHERE gg.parent_id " . db_create_in($goods_list) . " AND g.is_delete = 0 AND g.is_on_sale = 1 " .
            "ORDER BY gg.parent_id, gg.goods_id";

        $res = $GLOBALS['db']->query($sql);

        foreach ($res as $row) {
            $arr[$temp_index]['parent_id'] = $row['parent_id'];//配件的基本件ID
            $arr[$temp_index]['parent_name'] = $row['parent_name'];//配件的基本件的名称
            $arr[$temp_index]['parent_short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                sub_str($row['parent_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['parent_name'];//配件的基本件显示的名称
            $arr[$temp_index]['goods_id'] = $row['goods_id'];//配件的商品ID
            $arr[$temp_index]['goods_name'] = $row['goods_name'];//配件的名称
            $arr[$temp_index]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];//配件显示的名称
            $arr[$temp_index]['fittings_price'] = price_format($row['goods_price']);//配件价格
            $arr[$temp_index]['shop_price'] = price_format($row['shop_price']);//配件原价格
            $arr[$temp_index]['goods_thumb'] = get_image_path($row['goods_thumb']);
            $arr[$temp_index]['goods_img'] = get_image_path($row['goods_img']);
            $arr[$temp_index]['url'] = build_uri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
            $temp_index++;
        }

        return $arr;
    }

    /**
     * 取指定规格的货品信息
     *
     * @access      public
     * @param       string $goods_id
     * @param       array $spec_goods_attr_id
     * @return      array
     */
    function get_products_info($goods_id, $spec_goods_attr_id)
    {
        $return_array = [];

        if (empty($spec_goods_attr_id) || !is_array($spec_goods_attr_id) || empty($goods_id)) {
            return $return_array;
        }

        $goods_attr_array = sort_goods_attr_id_array($spec_goods_attr_id);

        if (isset($goods_attr_array['sort'])) {
            $goods_attr = implode('|', $goods_attr_array['sort']);

            $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('products') . " WHERE goods_id = '$goods_id' AND goods_attr = '$goods_attr' LIMIT 0, 1";
            $return_array = $GLOBALS['db']->getRow($sql);
        }
        return $return_array;
    }



    /**
     * 获得商品的详细信息
     *
     * @access  public
     * @param   integer $goods_id
     * @return  void
     */
    function get_goods_info($goods_id)
    {
        $time = gmtime();
        $sql = 'SELECT g.*, c.measure_unit, b.brand_id, b.brand_name AS goods_brand, m.type_money AS bonus_money, ' .
            'IFNULL(AVG(r.comment_rank), 0) AS comment_rank, ' .
            "IFNULL(mp.user_price, g.shop_price * '". session('discount') ."') AS rank_price " .
            'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('category') . ' AS c ON g.cat_id = c.cat_id ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON g.brand_id = b.brand_id ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('comment') . ' AS r ' .
            'ON r.id_value = g.goods_id AND comment_type = 0 AND r.parent_id = 0 AND r.status = 1 ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('bonus_type') . ' AS m ' .
            "ON g.bonus_type_id = m.type_id AND m.send_start_date <= '$time' AND m.send_end_date >= '$time'" .
            " LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '". session('user_rank') ."' " .
            "WHERE g.goods_id = '$goods_id' AND g.is_delete = 0 " .
            "GROUP BY g.goods_id";
        $row = $GLOBALS['db']->getRow($sql);

        if ($row !== false) {
            // 用户评论级别取整
            $row['comment_rank'] = ceil($row['comment_rank']) == 0 ? 5 : ceil($row['comment_rank']);

            // 获得商品的销售价格
            $row['market_price'] = price_format($row['market_price']);
            $row['shop_price_formated'] = price_format($row['shop_price']);

            // 修正促销价格
            if ($row['promote_price'] > 0) {
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            } else {
                $promote_price = 0;
            }

            // 处理商品水印图片
            $watermark_img = '';

            if ($promote_price != 0) {
                $watermark_img = "watermark_promote";
            } elseif ($row['is_new'] != 0) {
                $watermark_img = "watermark_new";
            } elseif ($row['is_best'] != 0) {
                $watermark_img = "watermark_best";
            } elseif ($row['is_hot'] != 0) {
                $watermark_img = 'watermark_hot';
            }

            if ($watermark_img != '') {
                $row['watermark_img'] = $watermark_img;
            }

            $row['promote_price_org'] = $promote_price;
            $row['promote_price'] = price_format($promote_price);

            // 修正重量显示
            $row['goods_weight'] = (intval($row['goods_weight']) > 0) ?
                $row['goods_weight'] . $GLOBALS['_LANG']['kilogram'] :
                ($row['goods_weight'] * 1000) . $GLOBALS['_LANG']['gram'];

            // 修正上架时间显示
            $row['add_time'] = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);

            // 促销时间倒计时
            $time = gmtime();
            if ($time >= $row['promote_start_date'] && $time <= $row['promote_end_date']) {
                $row['gmt_end_time'] = $row['promote_end_date'];
            } else {
                $row['gmt_end_time'] = 0;
            }

            // 是否显示商品库存数量
            $row['goods_number'] = ($GLOBALS['_CFG']['use_storage'] == 1) ? $row['goods_number'] : '';

            // 修正积分：转换为可使用多少积分（原来是可以使用多少钱的积分）
            $row['integral'] = $GLOBALS['_CFG']['integral_scale'] ? round($row['integral'] * 100 / $GLOBALS['_CFG']['integral_scale']) : 0;

            // 修正优惠券
            $row['bonus_money'] = ($row['bonus_money'] == 0) ? 0 : price_format($row['bonus_money'], false);

            // 修正商品图片
            $row['goods_img'] = get_image_path($row['goods_img']);
            $row['goods_thumb'] = get_image_path($row['goods_thumb']);

            return $row;
        } else {
            return false;
        }
    }

    /**
     * 获得商品的属性和规格
     *
     * @access  public
     * @param   integer $goods_id
     * @return  array
     */
    function get_goods_properties($goods_id)
    {
        // 对属性进行重新排序和分组
        $sql = "SELECT attr_group " .
            "FROM " . $GLOBALS['ecs']->table('goods_type') . " AS gt, " . $GLOBALS['ecs']->table('goods') . " AS g " .
            "WHERE g.goods_id='$goods_id' AND gt.cat_id=g.goods_type";
        $grp = $GLOBALS['db']->getOne($sql);

        if (!empty($grp)) {
            $groups = explode("\n", strtr($grp, "\r", ''));
        }

        // 获得商品的规格
        $sql = "SELECT a.attr_id, a.attr_name, a.attr_group, a.is_linked, a.attr_type, " .
            "g.goods_attr_id, g.attr_value, g.attr_price " .
            'FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('attribute') . ' AS a ON a.attr_id = g.attr_id ' .
            "WHERE g.goods_id = '$goods_id' " .
            'ORDER BY a.sort_order, g.attr_price, g.goods_attr_id';
        $res = $GLOBALS['db']->getAll($sql);

        $arr['pro'] = [];     // 属性
        $arr['spe'] = [];     // 规格
        $arr['lnk'] = [];     // 关联的属性

        foreach ($res as $row) {
            $row['attr_value'] = str_replace("\n", '<br />', $row['attr_value']);

            if ($row['attr_type'] == 0) {
                $group = (isset($groups[$row['attr_group']])) ? $groups[$row['attr_group']] : $GLOBALS['_LANG']['goods_attr'];

                $arr['pro'][$group][$row['attr_id']]['name'] = $row['attr_name'];
                $arr['pro'][$group][$row['attr_id']]['value'] = $row['attr_value'];
            } else {
                $arr['spe'][$row['attr_id']]['attr_type'] = $row['attr_type'];
                $arr['spe'][$row['attr_id']]['name'] = $row['attr_name'];
                $arr['spe'][$row['attr_id']]['values'][] = [
                    'label' => $row['attr_value'],
                    'price' => $row['attr_price'],
                    'format_price' => price_format(abs($row['attr_price']), false),
                    'id' => $row['goods_attr_id']];
            }

            if ($row['is_linked'] == 1) {
                // 如果该属性需要关联，先保存下来
                $arr['lnk'][$row['attr_id']]['name'] = $row['attr_name'];
                $arr['lnk'][$row['attr_id']]['value'] = $row['attr_value'];
            }
        }

        return $arr;
    }

    /**
     * 获得属性相同的商品
     *
     * @access  public
     * @param   array $attr // 包含了属性名称,ID的数组
     * @return  array
     */
    function get_same_attribute_goods($attr)
    {
        $lnk = [];

        if (!empty($attr)) {
            foreach ($attr['lnk'] as $key => $val) {
                $lnk[$key]['title'] = sprintf($GLOBALS['_LANG']['same_attrbiute_goods'], $val['name'], $val['value']);

                // 查找符合条件的商品
                $sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, g.goods_img, g.shop_price AS org_price, ' .
                    "IFNULL(mp.user_price, g.shop_price * '". session('discount') ."') AS shop_price, " .
                    'g.market_price, g.promote_price, g.promote_start_date, g.promote_end_date ' .
                    'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
                    'LEFT JOIN ' . $GLOBALS['ecs']->table('goods_attr') . ' as a ON g.goods_id = a.goods_id ' .
                    "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
                    "ON mp.goods_id = g.goods_id AND mp.user_rank = '". session('user_rank') ."' " .
                    "WHERE a.attr_id = '$key' AND g.is_on_sale=1 AND a.attr_value = '$val[value]' AND g.goods_id <> '$_REQUEST[id]' " .
                    'LIMIT ' . $GLOBALS['_CFG']['attr_related_number'];
                $res = $GLOBALS['db']->getAll($sql);

                foreach ($res as $row) {
                    $lnk[$key]['goods'][$row['goods_id']]['goods_id'] = $row['goods_id'];
                    $lnk[$key]['goods'][$row['goods_id']]['goods_name'] = $row['goods_name'];
                    $lnk[$key]['goods'][$row['goods_id']]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                        sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
                    $lnk[$key]['goods'][$row['goods_id']]['goods_thumb'] = (empty($row['goods_thumb'])) ? $GLOBALS['_CFG']['no_picture'] : $row['goods_thumb'];
                    $lnk[$key]['goods'][$row['goods_id']]['market_price'] = price_format($row['market_price']);
                    $lnk[$key]['goods'][$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
                    $lnk[$key]['goods'][$row['goods_id']]['promote_price'] = bargain_price($row['promote_price'],
                        $row['promote_start_date'], $row['promote_end_date']);
                    $lnk[$key]['goods'][$row['goods_id']]['url'] = build_uri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
                }
            }
        }

        return $lnk;
    }

    /**
     * 获得指定商品的相册
     *
     * @access  public
     * @param   integer $goods_id
     * @return  array
     */
    function get_goods_gallery($goods_id)
    {
        $sql = 'SELECT img_id, img_url, thumb_url, img_desc' .
            ' FROM ' . $GLOBALS['ecs']->table('goods_gallery') .
            " WHERE goods_id = '$goods_id' LIMIT " . $GLOBALS['_CFG']['goods_gallery_number'];
        $row = $GLOBALS['db']->getAll($sql);
        // 格式化相册图片路径
        foreach ($row as $key => $gallery_img) {
            $row[$key]['img_url'] = get_image_path($gallery_img['img_url']);
            $row[$key]['thumb_url'] = get_image_path($gallery_img['thumb_url']);
        }
        return $row;
    }


    /**
     * 获得所有扩展分类属于指定分类的所有商品ID
     *
     * @access  public
     * @param   string $cat_id 分类查询字符串
     * @return  string
     */
    function get_extension_goods($cats)
    {
        $extension_goods_array = '';
        $sql = 'SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods_cat') . " AS g WHERE $cats";
        $extension_goods_array = $GLOBALS['db']->getCol($sql);
        return db_create_in($extension_goods_array, 'g.goods_id');
    }


    /**
     * 添加商品名样式
     * @param   string $goods_name 商品名称
     * @param   string $style 样式参数
     * @return  string
     */
    function add_style($goods_name, $style)
    {
        $goods_style_name = $goods_name;

        $arr = explode('+', $style);

        $font_color = !empty($arr[0]) ? $arr[0] : '';
        $font_style = !empty($arr[1]) ? $arr[1] : '';

        if ($font_color != '') {
            $goods_style_name = '<span color=' . $font_color . '>' . $goods_style_name . '</span>';
        }
        if ($font_style != '') {
            $goods_style_name = '<' . $font_style . '>' . $goods_style_name . '</' . $font_style . '>';
        }
        return $goods_style_name;
    }
    /**
     * 商品推荐usort用自定义排序行数
     */
    function goods_sort($goods_a, $goods_b)
    {
        if ($goods_a['sort_order'] == $goods_b['sort_order']) {
            return 0;
        }
        return ($goods_a['sort_order'] < $goods_b['sort_order']) ? -1 : 1;
    }

    /**
     *  所有的促销活动信息
     *
     * @access  public
     * @return  array
     */
    function get_promotion_info($goods_id = '')
    {
        $snatch = [];
        $group = [];
        $auction = [];
        $package = [];
        $favourable = [];

        $gmtime = gmtime();
        $sql = 'SELECT act_id, act_name, act_type, start_time, end_time FROM ' . $GLOBALS['ecs']->table('goods_activity') . " WHERE is_finished=0 AND start_time <= '$gmtime' AND end_time >= '$gmtime'";
        if (!empty($goods_id)) {
            $sql .= " AND goods_id = '$goods_id'";
        }
        $res = $GLOBALS['db']->getAll($sql);
        foreach ($res as $data) {
            switch ($data['act_type']) {
                case GAT_SNATCH: //夺宝奇兵
                    $snatch[$data['act_id']]['act_name'] = $data['act_name'];
                    $snatch[$data['act_id']]['url'] = build_uri('snatch', ['sid' => $data['act_id']]);
                    $snatch[$data['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $data['start_time']), local_date('Y-m-d', $data['end_time']));
                    $snatch[$data['act_id']]['sort'] = $data['start_time'];
                    $snatch[$data['act_id']]['type'] = 'snatch';
                    break;

                case GAT_GROUP_BUY: //团购
                    $group[$data['act_id']]['act_name'] = $data['act_name'];
                    $group[$data['act_id']]['url'] = build_uri('group_buy', ['gbid' => $data['act_id']]);
                    $group[$data['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $data['start_time']), local_date('Y-m-d', $data['end_time']));
                    $group[$data['act_id']]['sort'] = $data['start_time'];
                    $group[$data['act_id']]['type'] = 'group_buy';
                    break;

                case GAT_AUCTION: //拍卖
                    $auction[$data['act_id']]['act_name'] = $data['act_name'];
                    $auction[$data['act_id']]['url'] = build_uri('auction', ['auid' => $data['act_id']]);
                    $auction[$data['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $data['start_time']), local_date('Y-m-d', $data['end_time']));
                    $auction[$data['act_id']]['sort'] = $data['start_time'];
                    $auction[$data['act_id']]['type'] = 'auction';
                    break;

                case GAT_PACKAGE: //礼包
                    $package[$data['act_id']]['act_name'] = $data['act_name'];
                    $package[$data['act_id']]['url'] = 'package.php#' . $data['act_id'];
                    $package[$data['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $data['start_time']), local_date('Y-m-d', $data['end_time']));
                    $package[$data['act_id']]['sort'] = $data['start_time'];
                    $package[$data['act_id']]['type'] = 'package';
                    break;
            }
        }

        $user_rank = ',' . session('user_rank') . ',';
        $favourable = [];
        $sql = 'SELECT act_id, act_range, act_range_ext, act_name, start_time, end_time FROM ' . $GLOBALS['ecs']->table('favourable_activity') . " WHERE start_time <= '$gmtime' AND end_time >= '$gmtime'";
        if (!empty($goods_id)) {
            $sql .= " AND CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'";
        }
        $res = $GLOBALS['db']->getAll($sql);

        if (empty($goods_id)) {
            foreach ($res as $rows) {
                $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                $favourable[$rows['act_id']]['url'] = 'activity.php';
                $favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
                $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                $favourable[$rows['act_id']]['type'] = 'favourable';
            }
        } else {
            $sql = "SELECT cat_id, brand_id FROM " . $GLOBALS['ecs']->table('goods') .
                "WHERE goods_id = '$goods_id'";
            $row = $GLOBALS['db']->getRow($sql);
            $category_id = $row['cat_id'];
            $brand_id = $row['brand_id'];

            foreach ($res as $rows) {
                if ($rows['act_range'] == FAR_ALL) {
                    $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                    $favourable[$rows['act_id']]['url'] = 'activity.php';
                    $favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
                    $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                    $favourable[$rows['act_id']]['type'] = 'favourable';
                } elseif ($rows['act_range'] == FAR_CATEGORY) {
                    // 找出分类id的子分类id
                    $id_list = [];
                    $raw_id_list = explode(',', $rows['act_range_ext']);
                    foreach ($raw_id_list as $id) {
                        $id_list = array_merge($id_list, array_keys(cat_list($id, 0, false)));
                    }
                    $ids = join(',', array_unique($id_list));

                    if (strpos(',' . $ids . ',', ',' . $category_id . ',') !== false) {
                        $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                        $favourable[$rows['act_id']]['url'] = 'activity.php';
                        $favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
                        $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                        $favourable[$rows['act_id']]['type'] = 'favourable';
                    }
                } elseif ($rows['act_range'] == FAR_BRAND) {
                    if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $brand_id . ',') !== false) {
                        $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                        $favourable[$rows['act_id']]['url'] = 'activity.php';
                        $favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
                        $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                        $favourable[$rows['act_id']]['type'] = 'favourable';
                    }
                } elseif ($rows['act_range'] == FAR_GOODS) {
                    if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $goods_id . ',') !== false) {
                        $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                        $favourable[$rows['act_id']]['url'] = 'activity.php';
                        $favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
                        $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                        $favourable[$rows['act_id']]['type'] = 'favourable';
                    }
                }
            }
        }

//    if(!empty($goods_id))
//    {
//        return array('snatch'=>$snatch, 'group_buy'=>$group, 'auction'=>$auction, 'favourable'=>$favourable);
//    }

        $sort_time = [];
        $arr = array_merge($snatch, $group, $auction, $package, $favourable);
        foreach ($arr as $key => $value) {
            $sort_time[] = $value['sort'];
        }
        array_multisort($sort_time, SORT_NUMERIC, SORT_DESC, $arr);

        return $arr;
    }


    /**
     * 取商品的货品列表
     *
     * @param       mixed $goods_id 单个商品id；多个商品id数组；以逗号分隔商品id字符串
     * @param       string $conditions sql条件
     *
     * @return  array
     */
    function get_good_products($goods_id, $conditions = '')
    {
        if (empty($goods_id)) {
            return [];
        }

        switch (gettype($goods_id)) {
            case 'integer':

                $_goods_id = "goods_id = '" . intval($goods_id) . "'";

                break;

            case 'string':
            case 'array':

                $_goods_id = db_create_in($goods_id, 'goods_id');

                break;
        }

        // 取货品
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('products') . " WHERE $_goods_id $conditions";
        $result_products = $GLOBALS['db']->getAll($sql);

        // 取商品属性
        $sql = "SELECT goods_attr_id, attr_value FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE $_goods_id";
        $result_goods_attr = $GLOBALS['db']->getAll($sql);

        $_goods_attr = [];
        foreach ($result_goods_attr as $value) {
            $_goods_attr[$value['goods_attr_id']] = $value['attr_value'];
        }

        // 过滤货品
        foreach ($result_products as $key => $value) {
            $goods_attr_array = explode('|', $value['goods_attr']);
            if (is_array($goods_attr_array)) {
                $goods_attr = [];
                foreach ($goods_attr_array as $_attr) {
                    $goods_attr[] = $_goods_attr[$_attr];
                }

                $goods_attr_str = implode('，', $goods_attr);
            }

            $result_products[$key]['goods_attr_str'] = $goods_attr_str;
        }

        return $result_products;
    }

    /**
     * 取商品的下拉框Select列表
     *
     * @param       int $goods_id 商品id
     *
     * @return  array
     */
    function get_good_products_select($goods_id)
    {
        $return_array = [];
        $products = get_good_products($goods_id);

        if (empty($products)) {
            return $return_array;
        }

        foreach ($products as $value) {
            $return_array[$value['product_id']] = $value['goods_attr_str'];
        }

        return $return_array;
    }

    /**
     * 取商品的规格列表
     *
     * @param       int $goods_id 商品id
     * @param       string $conditions sql条件
     *
     * @return  array
     */
    function get_specifications_list($goods_id, $conditions = '')
    {
        // 取商品属性
        $sql = "SELECT ga.goods_attr_id, ga.attr_id, ga.attr_value, a.attr_name
            FROM " . $GLOBALS['ecs']->table('goods_attr') . " AS ga, " . $GLOBALS['ecs']->table('attribute') . " AS a
            WHERE ga.attr_id = a.attr_id
            AND ga.goods_id = '$goods_id'
            $conditions";
        $result = $GLOBALS['db']->getAll($sql);

        $return_array = [];
        foreach ($result as $value) {
            $return_array[$value['goods_attr_id']] = $value;
        }

        return $return_array;
    }


    /**
     * 获得指定的商品属性
     *
     * @access      public
     * @param       array $arr 规格、属性ID数组
     * @param       type $type 设置返回结果类型：pice，显示价格，默认；no，不显示价格
     *
     * @return      string
     */
    function get_goods_attr_info($arr, $type = 'pice')
    {
        $attr = '';

        if (!empty($arr)) {
            $fmt = "%s:%s[%s] \n";

            $sql = "SELECT a.attr_name, ga.attr_value, ga.attr_price " .
                "FROM " . $GLOBALS['ecs']->table('goods_attr') . " AS ga, " .
                $GLOBALS['ecs']->table('attribute') . " AS a " .
                "WHERE " . db_create_in($arr, 'ga.goods_attr_id') . " AND a.attr_id = ga.attr_id";
            $res = $GLOBALS['db']->query($sql);

            foreach ($res as $row) {
                $attr_price = round(floatval($row['attr_price']), 2);
                $attr .= sprintf($fmt, $row['attr_name'], $row['attr_value'], $attr_price);
            }

            $attr = str_replace('[0]', '', $attr);
        }

        return $attr;
    }


}