<?php
defined('BASEPATH') or exit ('No direct script access allowed');

class Category extends IndexController
{

    private $parameter = array();

    public function __construct()
    {
        parent::__construct();
        $this->initParameter();
    }

    public function index()
    {
        $this->redirect('all');
    }

    public function all()
    {
        $cat_id = $this->parameter['cat_id'];
        $cache_id = $this->get_cache_id();
        if (!$this->is_cached('category_all.dwt', $cache_id)) {
            $category = get_child_tree($cat_id);
            $this->assign('category', $category);
            $this->assign('page_title', L('catalog'));
        }
        if(IS_AJAX){
            $id = I('id');
            $goodsArr = $this->get_cat_two($id);
            $this->assign('goodsArr',$goodsArr);
            $info = $this->fetch('library/asynclist_category.lbi');
            die(json_encode($info));
        }
        $this->display('category_all.dwt', $cache_id);
    }
    public function info(){
        dump($_REQUEST);
    }
    public function products()
    {
        /* 获得请求的分类 ID */
        $cat_id = I('id', 0, 'intval');
        if ($cat_id) {
            $this->redirect('welcome/index');
        }
        /*页面的缓存ID*/
        $cache_id = $this->get_cache_id();
        sprintf('%x', crc32($cat_id . '-' . $this->parameter['display'] . '-' . $this->parameter['sort'] . '-' . $this->parameter['order'] . '-' . $this->parameter['page'] . '-' . $this->parameter['size'] . '-' . $_SESSION ['user_rank'] . '-' . C('lang') . '-' . $this->parameter['brand'] . '-' . $this->parameter['price_max'] . '-' . $this->parameter['price_min'] . '-' . $this->parameter['filter_attr'])
        );
        if (!$this->is_cached($cache_id)) {
            /*如果页面没有被缓存则重新获取页面的内容 */
            $children = get_children($cat_id);
            $cat = $this->get_cat_info($children);//获得分类的相关信息
            if (!empty($cat)) {
                $this->assign('keywords', htmlspecialchars($cat ['keywords']));
                $this->assign('description', htmlspecialchars($cat ['cat_desc']));
                $this->assign('cat_style', htmlspecialchars($cat ['style']));
            } else {
                /* 如果分类不存在则返回首页 */
                ecs_header("Location: ./\n");
                exit;
            }
            /* 赋值固定内容 */
            if ($this->parameter['brand'] > 0) {
                $sql = "SELECT brand_name FROM {pre}brand WHERE brand_id =" . $this->parameter['brand'];
                $brand_name = $this->load->db->getOne($sql);
            } else {
                $brand_name = '';
            }
            /* 获取价格分级 */
            if ($cat ['grade'] == 0 && $cat ['parent_id'] != 0) {
                $cat ['grade'] = get_parent_grade($cat_id); // 如果当前分类级别为空，取最近的上级分类
            }

            if ($cat ['grade'] > 1) {
                /* 需要价格分级 */

                /*
                    算法思路：
                        1、当分级大于1时，进行价格分级
                        2、取出该类下商品价格的最大值、最小值
                        3、根据商品价格的最大值来计算商品价格的分级数量级：
                                价格范围(不含最大值)    分级数量级
                                0-0.1                   0.001
                                0.1-1                   0.01
                                1-10                    0.1
                                10-100                  1
                                100-1000                10
                                1000-10000              100
                        4、计算价格跨度：
                                取整((最大值-最小值) / (价格分级数) / 数量级) * 数量级
                        5、根据价格跨度计算价格范围区间
                        6、查询数据库

                    可能存在问题：
                        1、
                        由于价格跨度是由最大值、最小值计算出来的
                        然后再通过价格跨度来确定显示时的价格范围区间
                        所以可能会存在价格分级数量不正确的问题
                        该问题没有证明
                        2、
                        当价格=最大值时，分级会多出来，已被证明存在
                */
                $sql = "SELECT min(g.shop_price) AS min, max(g.shop_price) as max " . " FROM {pre}goods AS g " . " WHERE ($children OR " . get_extension_goods($children) . ') AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1  ';
                $row = $this->load->db->getRow($sql);
                // 取得价格分级最小单位级数，比如，千元商品最小以100为级数
                $price_grade = 0.0001;
                for ($i = -2; $i <= log10($row ['max']); $i++) {
                    $price_grade *= 10;
                }
                // 跨度
                $dx = ceil(($row ['max'] - $row ['min']) / ($cat ['grade']) / $price_grade) * $price_grade;
                if ($dx == 0) {
                    $dx = $price_grade;
                }

                for ($i = 1; $row ['min'] > $dx * $i; $i++)
                    ;

                for ($j = 1; $row ['min'] > $dx * ($i - 1) + $price_grade * $j; $j++)
                    ;
                $row ['min'] = $dx * ($i - 1) + $price_grade * ($j - 1);

                for (; $row ['max'] >= $dx * $i; $i++)
                    ;
                $row ['max'] = $dx * ($i) + $price_grade * ($j - 1);

                $sql = "SELECT (FLOOR((g.shop_price - $row[min]) / $dx)) AS sn, COUNT(*) AS goods_num  " . " FROM {pre}goods AS g " . " WHERE ($children OR " . get_extension_goods($children) . ') AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 ' . " GROUP BY sn ";
                $price_grade = $this->load->db->getAll($sql);
                foreach ($price_grade as $key => $val) {
                    $temp_key = $key + 1;
                    $price_grade [$temp_key] ['goods_num'] = $val ['goods_num'];
                    $price_grade [$temp_key] ['start'] = $row ['min'] + round($dx * $val ['sn']);
                    $price_grade [$temp_key] ['end'] = $row ['min'] + round($dx * ($val ['sn'] + 1));
                    $price_grade [$temp_key] ['price_range'] = $price_grade [$temp_key] ['start'] . '&nbsp;-&nbsp;' . $price_grade [$temp_key] ['end'];
                    $price_grade [$temp_key] ['filter_attr_str'] = price_format($price_grade [$temp_key] ['start']);
                    $price_grade [$temp_key] ['formated_end'] = price_format($price_grade [$temp_key] ['end']);
                    $price_grade [$temp_key] ['url'] = build_uri('category', array(
                        'cid' => $cat_id,
                        'bid' => $this->parameter['brand'],
                        'price_min' => $price_grade [$temp_key] ['start'],
                        'price_max' => $price_grade [$temp_key] ['end'],
                        'filter_attr' => $price_grade [$temp_key]['filter_attr_str']
                    ), $cat ['cat_name']);
                    /* 判断价格区间是否被选中 */
                    if (isset ($_REQUEST ['price_min']) && $price_grade [$temp_key] ['start'] == $this->parameter['price_min'] && $price_grade [$temp_key] ['end'] == $this->parameter['price_max']) {
                        $price_grade [$temp_key] ['selected'] = 1;
                    } else {
                        $price_grade [$temp_key] ['selected'] = 0;
                    }
                }
                $price_grade [0] ['start'] = 0;
                $price_grade [0] ['end'] = 0;
                $price_grade [0] ['price_range'] = L('all_attribute');
                $price_grade [0] ['url'] = build_uri('category', array(
                    'cid' => $cat_id,
                    'bid' => $this->parameter['brand'],
                    'price_min' => 0,
                    'price_max' => 0,
                    'filter_attr' => $this->parameter['filter_attr_str']
                ), $cat ['cat_name']);
                $price_grade [0] ['selected'] = empty ($price_max) ? 1 : 0;
                $this->assign('price_grade', $price_grade);
            }
            /* 品牌筛选 */

            $sql = "SELECT b.brand_id, b.brand_name, COUNT(*) AS goods_num " . "FROM {pre}brand AS b, {pre}goods AS g LEFT JOIN {pre}goods_cat AS gc ON g.goods_id = gc.goods_id " . "WHERE g.brand_id = b.brand_id AND ($children OR " . 'gc.cat_id ' . db_create_in(array_unique(array_merge(array(
                    $cat_id
                ), array_keys(cat_list($cat_id, 0, false))))) . ") AND b.is_show = 1 " . " AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 " . "GROUP BY b.brand_id HAVING goods_num > 0 ORDER BY b.sort_order, b.brand_id ASC";

            $brands = $this->load->db->getAll($sql);
            foreach ($brands as $key => $val) {
                $temp_key = $key + 1;
                $brands [$temp_key] ['brand_name'] = $val ['brand_name'];
                $brands [$temp_key] ['url'] = build_uri('category', array(
                    'cid' => $cat_id,
                    'bid' => $val ['brand_id'],
                    'price_min' => $this->parameter['price_min'],
                    'price_max' => $this->parameter['price_max'],
                    'filter_attr' => $this->parameter['filter_attr_str']
                ), $cat ['cat_name']);

                /* 判断品牌是否被选中 */
                if ($this->parameter['brand'] == $brands [$key] ['brand_id']) {
                    $brands [$temp_key] ['selected'] = 1;
                } else {
                    $brands [$temp_key] ['selected'] = 0;
                }
            }
            $brands [0] ['brand_name'] = L('all_attribute');
            $brands [0] ['url'] = build_uri('category', array(
                'cid' => $cat_id,
                'bid' => 0,
                'price_min' => $this->parameter['price_min'],
                'price_max' => $this->parameter['price_max'],
                'filter_attr' => $this->parameter['filter_attr_str']
            ), $cat ['cat_name']);
            $brands [0] ['selected'] = empty ($brand) ? 1 : 0;

            $this->assign('brands', $brands);
            /* 属性筛选 */
            $ext = ''; // 商品查询条件扩展
            if ($cat ['filter_attr'] > 0) {
                $cat_filter_attr = explode(',', $cat ['filter_attr']); // 提取出此分类的筛选属性
                $all_attr_list = array();

                foreach ($cat_filter_attr as $key => $value) {
                    $sql = "SELECT a.attr_name FROM {pre}attribute AS a, {pre}goods_attr AS ga, {pre}goods AS g WHERE ($children OR " . get_extension_goods($children) . ") AND a.attr_id = ga.attr_id AND g.goods_id = ga.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND a.attr_id='$value'";
                    if ($temp_name = $this->load->db->getOne($sql)) {
                        $all_attr_list [$key] ['filter_attr_name'] = $temp_name;

                        $sql = "SELECT a.attr_id, MIN(a.goods_attr_id ) AS goods_id, a.attr_value AS attr_value FROM {pre}goods_attr AS a, {pre}goods AS g" . " WHERE ($children OR " . get_extension_goods($children) . ') AND g.goods_id = a.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 ' . " AND a.attr_id='$value' " . " GROUP BY a.attr_value";

                        $attr_list = $this->load->db->getAll($sql);

                        $temp_arrt_url_arr = array();

                        for ($i = 0; $i < count($cat_filter_attr); $i++)                        // 获取当前url中已选择属性的值，并保留在数组中
                        {
                            $temp_arrt_url_arr [$i] = !empty ($this->parameter['filter_attr'] [$i]) ? $this->parameter['filter_attr'] [$i] : 0;
                        }

                        $temp_arrt_url_arr [$key] = 0; // “全部”的信息生成
                        $temp_arrt_url = implode('.', $temp_arrt_url_arr);
                        $all_attr_list [$key] ['attr_list'] [0] ['attr_value'] = L('all_attribute');
                        $all_attr_list [$key] ['attr_list'] [0] ['url'] = build_uri('category', array(
                            'cid' => $cat_id,
                            'bid' => $this->parameter['brand'],
                            'price_min' => $this->parameter['price_min'],
                            'price_max' => $this->parameter['price_max'],
                            'filter_attr' => $temp_arrt_url
                        ), $cat ['cat_name']);
                        $all_attr_list [$key] ['attr_list'] [0] ['selected'] = empty ($this->parameter['filter_attr'] [$key]) ? 1 : 0;

                        foreach ($attr_list as $k => $v) {
                            $temp_key = $k + 1;
                            $temp_arrt_url_arr [$key] = $v ['goods_id']; // 为url中代表当前筛选属性的位置变量赋值,并生成以‘.’分隔的筛选属性字符串
                            $temp_arrt_url = implode('.', $temp_arrt_url_arr);

                            $all_attr_list [$key] ['attr_list'] [$temp_key] ['attr_value'] = $v ['attr_value'];
                            $all_attr_list [$key] ['attr_list'] [$temp_key] ['url'] = build_uri('category', array(
                                'cid' => $cat_id,
                                'bid' => $this->parameter['brand'],
                                'price_min' => $this->parameter['price_min'],
                                'price_max' => $this->parameter['price_max'],
                                'filter_attr' => $temp_arrt_url
                            ), $cat ['cat_name']);

                            if (!empty ($this->parameter['filter_attr'] [$key]) and $this->parameter['filter_attr'] [$key] == $v ['goods_id']) {
                                $all_attr_list [$key] ['attr_list'] [$temp_key] ['selected'] = 1;
                            } else {
                                $all_attr_list [$key] ['attr_list'] [$temp_key] ['selected'] = 0;
                            }
                        }
                    }
                }
                $this->assign('filter_attr_list', $all_attr_list);
                /* 扩展商品查询条件 */
                if (!empty ($this->parameter['filter_attr'])) {
                    $ext_sql = "SELECT DISTINCT(b.goods_id) FROM {pre}goods_attr AS a, {pre}goods_attr AS b " . "WHERE ";
                    $ext_group_goods = array();

                    foreach ($this->parameter['filter_attr'] as $k => $v)                    // 查出符合所有筛选属性条件的商品id */
                    {
                        if (is_numeric($v) && $v != 0 && isset ($cat_filter_attr [$k])) {
                            $sql = $ext_sql . "b.attr_value = a.attr_value AND b.attr_id = " . $cat_filter_attr [$k] . " AND a.goods_attr_id = " . $v;
                            $ext_group_goods = $this->load->db->getColCached($sql);
                            $ext .= ' AND ' . db_create_in($ext_group_goods, 'g.goods_id');
                        }
                    }
                }
            }
            assign_template('c', array(
                $cat_id
            ));
            $position = assign_ur_here($cat_id, $brand_name);
            $this->assign('page_title', $position ['title']); // 页面标题
            $this->assign('ur_here', $position ['ur_here']); // 当前位置

            $this->assign('categories', get_categories_tree($cat_id)); // 分类树
            $this->assign('helps', get_shop_help()); // 网店帮助
            $this->assign('top_goods', get_top10()); // 销售排行
            $this->assign('show_marketprice', C('show_marketprice'));
            $this->assign('category', $cat_id);
            $this->assign('brand_id', $this->parameter['brand']);
            $this->assign('price_max', $this->parameter['price_max']);
            $this->assign('price_min', $this->parameter['price_min']);
            $this->assign('filter_attr', $this->parameter['filter_attr_str']);
            $this->assign('feed_url', (C('rewrite') == 1) ? "feed-c$cat_id.xml" : 'feed.php?cat=' . $cat_id); // RSS URL
            if ($this->parameter['brand'] > 0) {
                $arr ['all'] = array(
                    'brand_id' => 0,
                    'brand_name' => L('all_goods'),
                    'brand_logo' => '',
                    'goods_num' => '',
                    'url' => build_uri('category', array(
                        'cid' => $cat_id
                    ), $cat ['cat_name'])
                );
            } else {
                $arr = array();
            }
            $brand_list = array_merge($arr, get_brands($cat_id, 'category'));
            //$this->assign ( 'data_dir', DATA_DIR );
            $this->assign('brand_list', $brand_list);
            $this->assign('promotion_info', get_promotion_info());
            /* 调查 */
            $vote = get_vote();
            if (!empty ($vote)) {
                $this->assign('vote_id', $vote ['id']);
                $this->assign('vote', $vote ['content']);
            }

            $this->assign('best_goods', get_category_recommend_goods('best', $children, $this->parameter['brand'], $this->parameter['price_min'], $this->parameter['price_max'], $ext));
            $this->assign('promotion_goods', get_category_recommend_goods('promote', $children, $this->parameter['brand'], $this->parameter['price_min'], $this->parameter['price_max'], $ext));
            $this->assign('hot_goods', get_category_recommend_goods('hot', $children, $this->parameter['brand'], $this->parameter['price_min'], $this->parameter['price_max'], $ext));
            $count = $this->get_cagtegory_goods_count($children, $this->parameter['brand'], $this->parameter['price_min'], $this->parameter['price_max'], $ext);
            $max_page = ($count > 0) ? ceil($count / $this->parameter['size']) : 1;
            if ($this->parameter['page'] > $max_page) {
                $page = $max_page;
            }
            $goodslist = $this->category_get_goods($children, $this->parameter['brand'], $this->parameter['price_min'], $this->parameter['price_max'], $ext, $this->parameter['size'], $this->parameter['page'], $this->parameter['sort'], $this->parameter['order']);
            if ($this->parameter['display'] == 'grid') {
                if (count($goodslist) % 2 != 0) {
                    $goodslist [] = array();
                }
            }
            $this->assign('goods_list', $goodslist);
            $this->assign('category', $cat_id);
            $this->assign('script_name', 'category');

            assign_pager('category', $cat_id, $count, $this->parameter['size'], $this->parameter['sort'], $this->parameter['order'], $this->parameter['page'], '', $this->parameter['brand'], $this->parameter['price_min'], $this->parameter['price_max'], $this->parameter['display'], $this->parameter['filter_attr_str']); // 分页
            assign_dynamic('category'); // 动态内容
        }
        $this->display('category.dwt');
    }

    private function initParameter()
    {
        // 初始化参数
        $this->parameter['cat_id'] = I('cat_id', 0);
        $this->parameter['page'] = I('page', 1);
        $this->parameter['size'] = C('page_size') > 0 ? C('page_size') : 10;
        $this->parameter['brand'] = I('brand', 0);
        $this->parameter['price_min'] = I('price_min', 0);
        $this->parameter['price_max'] = I('price_max', 0);
        $filter_attr_str = I('filter_attr', '0');
        $this->parameter['filter_attr_str'] = preg_match('/^[\d\.]+$/', $filter_attr_str) ? $filter_attr_str : '';
        $this->parameter['filter_attr'] = empty($filter_attr_str) ? '' : explode('.', $filter_attr_str);
        // 排序、显示方式以及类型
        $default_display_type = C('show_order_type') == '0' ? 'list' : (C('show_order_type') == '1' ? 'grid' : 'text');
        $default_sort_order_method = C('sort_order_method') == '0' ? 'DESC' : 'ASC';
        $default_sort_order_type = C('sort_order_type') == '0' ? 'goods_id' : (C('sort_order_type') == '1' ? 'shop_price' : 'last_update');
        $sort = I('sort');
        $order = I('order');
        $display = I('display');
        $display = in_array(strtolower($display), array('list', 'grid', 'text')) ? $display : (isset($_COOKIE['ECS']['display']) ? $_COOKIE['ECS']['display'] : $default_display_type);
        $this->parameter['sort'] = in_array(strtolower($sort), array('goods_id', 'shop_price', 'last_update')) ? $sort : $default_sort_order_type;
        $this->parameter['order'] = in_array(strtoupper($order), array('ASC', 'DESC')) ? $order : $default_sort_order_method;
        $this->parameter['display'] = in_array($display, array('list', 'grid', 'text')) ? $display : 'list';
        setcookie('ECS[display]', $this->parameter['display'], gmtime() + 86400 * 7);
    }

    /**
     * 获得分类的信息
     *
     * @param integer $cat_id
     *
     * @return void
     */
    public function get_cat_info($cat_id)
    {
        return $this->load->db->getRow('SELECT cat_name, keywords, cat_desc, style, grade, filter_attr, parent_id FROM {pre}category as g WHERE ' . $cat_id);
    }
    /**
     * 获得二级分类的信息
     *
     * @param integer $cat_id
     *
     * @return void
     */
    public function get_cat_two($cat_id)
    {
        return $this->load->db->getAll('SELECT cat_name, keywords, cat_desc, style, grade, filter_attr, parent_id FROM {pre}category as g WHERE g.parent_id =' . $cat_id);
    }



    /**
     * 获得分类下的商品总数
     *
     * @access public
     * @param string $cat_id
     * @return integer
     */
    public function get_cagtegory_goods_count($children, $brand = 0, $min = 0, $max = 0, $ext = '')
    {
        $where = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND ($children OR " . get_extension_goods($children) . ')';

        if ($brand > 0) {
            $where .= " AND g.brand_id = $brand ";
        }

        if ($min > 0) {
            $where .= " AND g.shop_price >= $min ";
        }

        if ($max > 0) {
            $where .= " AND g.shop_price <= $max ";
        }

        /* 返回商品总数 */
        return $this->load->db->getOne('SELECT COUNT(*) FROM {pre}goods' . " AS g WHERE $where $ext");
    }

    /**
     * 获得分类下的商品
     *
     * @access public
     * @param string $children
     * @return array
     */
    function category_get_goods($children, $brand, $min, $max, $ext, $size, $page, $sort, $order)
    {
        $display = $this->parameter['display'];
        $where = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND " . "g.is_delete = 0 AND ($children OR " . get_extension_goods($children) . ')';

        if ($brand > 0) {
            $where .= "AND g.brand_id=$brand ";
        }

        if ($min > 0) {
            $where .= " AND g.shop_price >= $min ";
        }

        if ($max > 0) {
            $where .= " AND g.shop_price <= $max ";
        }

        /* 获得商品列表 */
        $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, ' . "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, g.goods_type, " . 'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img ' . 'FROM {pre}goods AS g ' . 'LEFT JOIN {pre}member_price AS mp ' . "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " . "WHERE $where $ext ORDER BY $sort $order";
        $res = $this->load->db->selectLimit($sql, $size, ($page - 1) * $size);

        $arr = array();
        while ($row = $this->load->db->fetchRow($res)) {
            if ($row ['promote_price'] > 0) {
                $promote_price = bargain_price($row ['promote_price'], $row ['promote_start_date'], $row ['promote_end_date']);
            } else {
                $promote_price = 0;
            }

            /* 处理商品水印图片 */
            $watermark_img = '';

            if ($promote_price != 0) {
                $watermark_img = "watermark_promote_small";
            } elseif ($row ['is_new'] != 0) {
                $watermark_img = "watermark_new_small";
            } elseif ($row ['is_best'] != 0) {
                $watermark_img = "watermark_best_small";
            } elseif ($row ['is_hot'] != 0) {
                $watermark_img = 'watermark_hot_small';
            }

            if ($watermark_img != '') {
                $arr [$row ['goods_id']] ['watermark_img'] = $watermark_img;
            }

            $arr [$row ['goods_id']] ['goods_id'] = $row ['goods_id'];
            if ($display == 'grid') {
                $arr [$row ['goods_id']] ['goods_name'] = C('goods_name_length') > 0 ? sub_str($row ['goods_name'], C('goods_name_length')) : $row ['goods_name'];
            } else {
                $arr [$row ['goods_id']] ['goods_name'] = $row ['goods_name'];
            }
            $arr [$row ['goods_id']] ['name'] = $row ['goods_name'];
            $arr [$row ['goods_id']] ['goods_brief'] = $row ['goods_brief'];
            $arr [$row ['goods_id']] ['goods_style_name'] = add_style($row ['goods_name'], $row ['goods_name_style']);
            $arr [$row ['goods_id']] ['market_price'] = price_format($row ['market_price']);
            $arr [$row ['goods_id']] ['shop_price'] = price_format($row ['shop_price']);
            $arr [$row ['goods_id']] ['type'] = $row ['goods_type'];
            $arr [$row ['goods_id']] ['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
            $arr [$row ['goods_id']] ['goods_thumb'] = get_image_path($row ['goods_id'], $row ['goods_thumb'], true);
            $arr [$row ['goods_id']] ['goods_img'] = get_image_path($row ['goods_id'], $row ['goods_img']);
            $arr [$row ['goods_id']] ['url'] = build_uri('goods', array(
                'gid' => $row ['goods_id']
            ), $row ['goods_name']);
        }

        return $arr;
    }


}
