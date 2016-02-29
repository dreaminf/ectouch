<?php

class CategoryController extends CommonController
{
    private $cat_id; // 分类id
    private $children = '';
    private $brand; // 品牌
    private $type = ''; //商品类型
    private $price_min; // 最低价格
    private $price_max; // 最大价格
    private $ext = '';
    private $sort = 'last_update';
    private $order = 'ASC'; // 排序方式
    private $filter_attr_str;
    private $keywords;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->cat_id = I('request.id');
    }

    /**
     * 获取分类信息
     * 获取顶级分类
     */
    public function top_all()
    {
        /* 页面的缓存ID */
        $cache_id = sprintf('%X', crc32($_SERVER['REQUEST_URI'] . C('lang') . time()));
        if (!ECTouch::view()->is_cached('category_top_all.dwt', $cache_id)) {
            $category = model('CategoryBase')->get_categories_tree();
            $this->assign('category', $category);
            /* 页面标题 */
            $this->assign('page_title', L('catalog'));
        }
        $this->display('category_top_all.dwt', $cache_id);
    }

    /**
     * 分类产品信息列表
     */
    public function index()
    {
        $this->parameter();
        $this->assign('id', $this->cat_id);
        $this->assign('show_marketprice', C('show_marketprice'));
        $this->display('category.dwt');

    }

    /**
     * ajax获取子分类
     */
    public function async_list()
    {
        if (IS_AJAX) {
            $size = I('size');
            $page = I('page');
            $this->parameter();
            $goodslist = $this->category_get_goods($this->cat_id, $this->brand, $this->price_min, $this->price_max, $size, $page, $this->sort, $this->order, $this->keywords);
            $count = $this->get_goods_count($this->cat_id, $this->brand, $this->price_min, $this->price_max);
            $count = ceil($count / $size);
            if ($this->price_max > 0) {
                $count = $count - 1;
            }
            die(json_encode(array('list' => $goodslist, 'totalPage' => $count)));
        }
    }

    /**
     * 处理关键词
     */
    public function keywords()
    {
        $keyword = I('request.keywords');
        if ($keyword != '') {
            $this->keywords = 'AND (';
            $goods_ids = array();
            $val = mysql_like_quote(trim($keyword));
            $this->keywords .= "(goods_name LIKE '%$val%' OR goods_sn LIKE '%$val%' OR keywords LIKE '%$val%' )";
            $sql = 'SELECT DISTINCT goods_id FROM ' . $this->model->pre . "tag WHERE tag_words LIKE '%$val%' ";
            $row = $this->model->query($sql);
            foreach ($row as $vo) {
                $goods_ids[] = $vo['goods_id'];
            }
            /**
             * 处理关键字查询次数
             */
            $sql = 'INSERT INTO ' . $this->model->pre . "keywords (date , searchengine,keyword ,count) VALUES ('" . local_date('Y-m-d') . "', '" . ECTouch . "', '" . addslashes(str_replace('%', '', $val)) . "', '1')";
            $condition = 'keyword = "' . addslashes(str_replace('%', '', $val)) . '"';
            $set = $this->model->table('keywords')
                ->where($condition)
                ->find();

            if (!empty($set)) {
                $sql .= ' ON DUPLICATE KEY UPDATE count = count+1';
            }
            $this->model->query($sql);
            $this->keywords .= ')';
            $goods_ids = array_unique($goods_ids);
            // 拼接商品id
            $tag_id = implode(',', $goods_ids);
            if (!empty($tag_id)) {
                $this->keywords .= 'OR g.goods_id ' . db_create_in($tag_id);
            }
            $this->assign('keywords', $keyword);
            /*记录搜索历史记录*/
            if (!empty($_COOKIE['ECS']['keywords'])) {
                $history = explode(',', $_COOKIE['ECS']['keywords']);
                array_unshift($history, $keyword); //在数组开头插入一个或多个元素
                $history = array_unique($history);  //移除数组中的重复的值，并返回结果数组。
                setcookie('ECS[keywords]', implode(',', $history), gmtime() + 3600 * 24 * 30);
            } else {
                setcookie('ECS[keywords]', $keyword, gmtime() + 3600 * 24 * 30);
            }
        }
    }

    /**
     * 处理参数便于搜索商品信息
     */
    private function parameter()
    {
        // 如果分类ID为0，则返回总分类页
        if (empty($this->cat_id)) {
            $this->cat_id = 0;
        }
        // 获得分类的相关信息
        $cat = model('Category')->get_cat_info($this->cat_id);
        $this->assign('show_asynclist', C('show_asynclist'));
        $this->keywords();
        $this->cat_id = I('request.id', 0, 'intval');
        $this->brand = I('brand');
        if($this->brand == ""){
            $this->brand = I('request.brand');
        }
        $this->price_max = trim(I('price_max'));
        $this->price_min = trim(I('price_min'));
        $filter_attr = I('request.filter_attr');
        $this->type = I('request.type');
        // $this->filter_attr_str = $filter_attr > 0 ? $filter_attr : '0';
        $this->filter_attr_str = !empty($filter_attr) ? $filter_attr : 0; //by hnllyrp
        $this->filter_attr_str = trim(urldecode($this->filter_attr_str));
        $this->filter_attr_str = preg_match('/^[\d\.]+$/', $this->filter_attr_str) ? $this->filter_attr_str : '';
        $filter_attr = empty($this->filter_attr_str) ? '' : explode('.', $this->filter_attr_str);
        /* 排序、显示方式以及类型 */
        $default_display_type = C('show_order_type') == '0' ? 'list' : (C('show_order_type') == '1' ? 'grid' : 'album');
        $default_sort_order_method = C('sort_order_method') == '0' ? 'DESC' : 'ASC';
        $default_sort_order_type = C('sort_order_type') == '0' ? 'sort_order' : (C('sort_order_type') == '1' ? 'shop_price' : 'last_update');
        $this->type = (isset($_REQUEST['type']) && in_array(trim(strtolower($_REQUEST['type'])), array('best', 'hot', 'new', 'promotion'))) ? trim(strtolower($_REQUEST['type'])) : '';
        $this->sort = (isset($_REQUEST['sort']) && in_array(trim(strtolower($_REQUEST['sort'])), array(
                'sort_order',
                'shop_price',
                'last_update',
                'click_count',
                'sales_volume'
            ))) ? trim($_REQUEST['sort']) : $default_sort_order_type; // 增加按人气、按销量排序 by wang
        $this->order = (isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array(
                'ASC',
                'DESC'
            ))) ? trim($_REQUEST['order']) : $default_sort_order_method;
        $display = (isset($_REQUEST['display']) && in_array(trim(strtolower($_REQUEST['display'])), array(
                'list',
                'grid',
                'album'
            ))) ? trim($_REQUEST['display']) : (isset($_COOKIE['ECS']['display']) ? $_COOKIE['ECS']['display'] : $default_display_type);
        $this->assign('display', $display);
        $this->assign('sort', $this->sort);
        $this->assign('type', $this->type);
        $this->assign('order', $this->order);
        $this->assign('brand', $this->brand);
        $this->assign('price_min', $this->price_min);
        $this->assign('filter_attr', $this->filter_attr_str);
        $this->assign('price_max', $this->price_max);
        setcookie('ECS[display]', $display, gmtime() + 86400 * 7);
        $this->children = get_children($this->cat_id);
        // 获得当前分类下商品价格的最大值、最小值
        $sql = "SELECT max(g.shop_price) as max " . " FROM " . $this->model->pre . 'goods' . " AS g " . " WHERE ($this->children OR " . model('Goods')->get_extension_goods($this->children) . ') AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1  ';
        $row = $this->model->getRow($sql);
        //计算出最大价格的值
        $max_price = ceil($row['max'] / 100) * 100;
        $this->assign('max_price', $max_price);
        /* 赋值固定内容 */
        if ($this->brand > 0) {
            $brand_name = model('Base')->model->table('brand')->field('brand_name')->where("brand_id = '$this->brand'")->getOne();
        } else {
            $brand_name = '';
        }

        /* 获取价格分级 */
        if ($cat['grade'] == 0 && $cat['parent_id'] != 0) {
            $cat['grade'] = model('Category')->get_parent_grade($this->cat_id); // 如果当前分类级别为空，取最近的上级分类
        }
        if ($cat['grade'] > 1) {
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

            $sql = "SELECT min(g.shop_price) AS min, max(g.shop_price) as max " . " FROM " . $this->model->pre . 'goods' . " AS g " . " WHERE ($this->children OR " . model('Goods')->get_extension_goods($this->children) . ') AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1  ';
            // 获得当前分类下商品价格的最大值、最小值

            $row = M()->getRow($sql);
            // 取得价格分级最小单位级数，比如，千元商品最小以100为级数
            $price_grade = 0.0001;
            for ($i = -2; $i <= log10($row['max']); $i++) {
                $price_grade *= 10;
            }

            // 跨度
            $dx = ceil(($row['max'] - $row['min']) / ($cat['grade']) / $price_grade) * $price_grade;
            if ($dx == 0) {
                $dx = $price_grade;
            }

            for ($i = 1; $row['min'] > $dx * $i; $i++)
                ;

            for ($j = 1; $row['min'] > $dx * ($i - 1) + $price_grade * $j; $j++)
                ;
            $row['min'] = $dx * ($i - 1) + $price_grade * ($j - 1);

            for (; $row['max'] >= $dx * $i; $i++)
                ;
            $row['max'] = $dx * ($i) + $price_grade * ($j - 1);

            $sql = "SELECT (FLOOR((g.shop_price - $row[min]) / $dx)) AS sn, COUNT(*) AS goods_num  " . " FROM " . $this->model->pre . 'goods' . " AS g " . " WHERE ($this->children OR " . model('Goods')->get_extension_goods($this->children) . ') AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 ' . " GROUP BY sn ";

            $price_grade = $this->model->query($sql);

            foreach ($price_grade as $key => $val) {
                $temp_key = $key + 1;
                $price_grade[$temp_key]['goods_num'] = $val['goods_num'];
                $price_grade[$temp_key]['start'] = $row['min'] + round($dx * $val['sn']);
                $price_grade[$temp_key]['end'] = $row['min'] + round($dx * ($val['sn'] + 1));
                $price_grade[$temp_key]['price_range'] = $price_grade[$temp_key]['start'] . '&nbsp;-&nbsp;' . $price_grade[$temp_key]['end'];
                $price_grade[$temp_key]['formated_start'] = price_format($price_grade[$temp_key]['start']);
                $price_grade[$temp_key]['formated_end'] = price_format($price_grade[$temp_key]['end']);
                $price_grade[$temp_key]['url'] = url('category', array(
                    'cid' => $this->cat_id,
                    'bid' => $this->brand,
                    'price_min' => $price_grade[$temp_key]['start'],
                    'price_max' => $price_grade[$temp_key]['end'],
                    'filter_attr' => $filter_attr
                ));

                /* 判断价格区间是否被选中 */
                if (isset($_REQUEST['price_min']) && $price_grade[$temp_key]['start'] == $price_min && $price_grade[$temp_key]['end'] == $price_max) {
                    $price_grade[$temp_key]['selected'] = 1;
                } else {
                    $price_grade[$temp_key]['selected'] = 0;
                }
            }

            $price_grade[0]['start'] = 0;
            $price_grade[0]['end'] = 0;
            $price_grade[0]['price_range'] = L('all_attribute');
            $price_grade[0]['url'] = url('category/index', array(
                'cid' => $this->cat_id,
                'bid' => $this->brand,
                'price_min' => $this->price_min,
                'price_max' => $this->price_max,
                'filter_attr' => $this->filter_attr
            ));
            $price_grade[0]['selected'] = empty($price_max) ? 1 : 0;
            $this->assign('price_grade', $price_grade);
        }
        /* 品牌筛选 */
        $sql = "SELECT b.brand_id, b.brand_name, COUNT(*) AS goods_num " .
            "FROM " . $this->model->pre . 'brand' . " AS b, " . $this->model->pre . 'goods' .
            " AS g LEFT JOIN " . $this->model->pre . 'goods_cat' . " AS gc ON g.goods_id = gc.goods_id " .
            "WHERE g.brand_id = b.brand_id AND ($this->children OR " .
            'gc.cat_id ' . db_create_in(array_unique(array_merge(array(
                $this->cat_id
            ), array_keys(cat_list($this->cat_id, 0, false))))) . ") AND b.is_show = 1 " .
            " AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 " .
            "GROUP BY b.brand_id HAVING goods_num > 0 ORDER BY b.sort_order, b.brand_id ASC";
        $brands = $this->model->query($sql);
        foreach ($brands as $key => $val) {
            $temp_key = $key + 1;
            $brands[$temp_key]['brand_id'] = $val['brand_id']; // 同步绑定品牌名称和品牌ID
            $brands[$temp_key]['brand_name'] = $val['brand_name'];
            $brands[$temp_key]['url'] = url('category/index', array(
                'id' => $this->cat_id,
                'bid' => $val['brand_id'],
                'price_min' => $this->price_min,
                'price_max' => $this->price_max,
                'filter_attr' => $this->filter_attr
            ));

            /* 判断品牌是否被选中 */
            if ($this->brand == $val['brand_id']) {             // 修正当前品牌的ID
                $brands[$temp_key]['selected'] = 1;
            } else {
                $brands[$temp_key]['selected'] = 0;
            }
        }

        unset($brands[0]); // 清空索引为0的项目
        $brands[0]['brand_id'] = 0; // 新增默认值
        $brands[0]['brand_name'] = L('all_attribute');
        $brands[0]['url'] = url('category', array(
            'cid' => $this->cat_id,
            'bid' => 0,
            'price_min' => $price_min,
            'price_max' => $price_max,
            'filter_attr' => $filter_attr
        ));
        $brands[0]['selected'] = empty($brand) ? 1 : 0;
        ksort($brands);
        $this->assign('brands', $brands);
        /* 属性筛选 */
        $this->ext = ''; // 商品查询条件扩展
        if ($cat['filter_attr'] > 0) {
            $cat_filter_attr = explode(',', $cat['filter_attr']); // 提取出此分类的筛选属性
            $all_attr_list = array();
            foreach ($cat_filter_attr as $key => $value) {
                $sql = "SELECT a.attr_name FROM " . $this->model->pre . "attribute AS a, " . $this->model->pre . "goods_attr AS ga, " . $this->model->pre . "goods AS g WHERE ($this->children OR " . model('Goods')->get_extension_goods($this->children) . ") AND a.attr_id = ga.attr_id AND g.goods_id = ga.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND a.attr_id='$value'";
                $res = $this->model->query($sql);
                if ($temp_name = $res[0]['attr_name']) {
                    $all_attr_list[$key]['filter_attr_id'] = $value; // 新增属性标识 by wang
                    $all_attr_list[$key]['filter_attr_name'] = $temp_name;

                    $sql = "SELECT a.attr_id, MIN(a.goods_attr_id ) AS goods_id, a.attr_value AS attr_value FROM " . $this->model->pre . "goods_attr AS a, " . $this->model->pre . "goods AS g" . " WHERE ($this->children OR " . model('Goods')->get_extension_goods($this->children) . ') AND g.goods_id = a.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 ' . " AND a.attr_id='$value' " . " GROUP BY a.attr_value";

                    $attr_list = $this->model->query($sql);

                    $temp_arrt_url_arr = array();

                    for ($i = 0; $i < count($cat_filter_attr); $i++) { // 获取当前url中已选择属性的值，并保留在数组中
                        $temp_arrt_url_arr[$i] = !empty($filter_attr[$i]) ? $filter_attr[$i] : 0;
                    }
                    // “全部”的信息生成
                    $temp_arrt_url_arr[$key] = 0;
                    $temp_arrt_url = implode('.', $temp_arrt_url_arr);
                    // 默认数值
                    $all_attr_list[$key]['attr_list'][0]['attr_id'] = 0;
                    $all_attr_list[$key]['attr_list'][0]['attr_value'] = L('all_attribute');
                    $all_attr_list[$key]['attr_list'][0]['url'] = url('category/index', array(
                        'id' => $this->cat_id,
                        'bid' => $this->brand,
                        'price_min' => $this->price_min,
                        'price_max' => $this->price_max,
                        'filter_attr' => $temp_arrt_url
                    ));
                    $all_attr_list[$key]['attr_list'][0]['selected'] = empty($filter_attr[$key]) ? 1 : 0;

                    foreach ($attr_list as $k => $v) {
                        $temp_key = $k + 1;
                        // 为url中代表当前筛选属性的位置变量赋值,并生成以‘.’分隔的筛选属性字符串
                        $temp_arrt_url_arr[$key] = $v['goods_id'];
                        $temp_arrt_url = implode('.', $temp_arrt_url_arr);

                        $all_attr_list[$key]['attr_list'][$temp_key]['attr_id'] = $v['goods_id']; // 新增属性参数
                        $all_attr_list[$key]['attr_list'][$temp_key]['attr_value'] = $v['attr_value'];
                        $all_attr_list[$key]['attr_list'][$temp_key]['url'] = url('category/index', array(
                            'id' => $this->cat_id,
                            'bid' => $this->brand,
                            'price_min' => $this->price_min,
                            'price_max' => $this->price_max,
                            'filter_attr' => $temp_arrt_url
                        ));

                        if (!empty($filter_attr[$key]) and $filter_attr[$key] == $v['goods_id']) {
                            $all_attr_list[$key]['attr_list'][$temp_key]['selected'] = 1;
                        } else {
                            $all_attr_list[$key]['attr_list'][$temp_key]['selected'] = 0;
                        }
                    }
                }
            }
            $this->assign('filter_attr_list', $all_attr_list);
            // 扩展商品查询条件
            if (!empty($filter_attr)) {
                $ext_sql = "SELECT DISTINCT(b.goods_id) as dis FROM " . $this->model->pre . "goods_attr AS a, " . $this->model->pre . "goods_attr AS b " . "WHERE ";
                $ext_group_goods = array();
                // 查出符合所有筛选属性条件的商品id
                foreach ($filter_attr as $k => $v) {
                    unset($ext_group_goods);
                    if (is_numeric($v) && $v != 0 && isset($cat_filter_attr[$k])) {
                        $sql = $ext_sql . "b.attr_value = a.attr_value AND b.attr_id = " . $cat_filter_attr[$k] . " AND a.goods_attr_id = " . $v;
                        $res = $this->model->query($sql);
                        foreach ($res as $value) {
                            $ext_group_goods[] = $value['dis'];
                        }
                        $this->ext .= ' AND ' . db_create_in($ext_group_goods, 'g.goods_id');
                    }
                }
            }
        }
    }

    //获取商品
    private function category_get_goods($cat_id, $brand, $price_min, $price_max, $size, $page, $sort, $order, $keywords)
    {
        $where = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND " . "g.is_delete = 0 ";
        if ($cat_id !== 0) {
            $where .= "AND(g.cat_id = $cat_id OR " .model('Goods')->get_extension_goods($cat_id) .")";
        }
        if (isset($price_min) && !empty($price_max)) {
            $where .= " AND g.shop_price >= '" . $price_min . "' AND g.shop_price <= '" . $price_max . "'";
            $page = 0;
            $size = 20;
        }
        if (!empty($brand) && $brand !== 0) {
            $where .= " AND g.brand_id = '" . $brand . "' ";
        }
        if (!empty($keywords)) {
            $where .= $keywords;
        }
        $page = $page > 1 ? ($page - 1) * 10 : 0;
        /* 获得商品列表 */
        $sql = 'SELECT g.goods_id, g.goods_name,g.market_price, g.goods_name_style, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, ' . "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, g.goods_type, g.goods_number, " .
            'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img, xl.sales_volume ' . 'FROM ' . $this->model->pre . 'goods AS g ' . ' LEFT JOIN ' . $this->model->pre . 'touch_goods AS xl ' . ' ON g.goods_id=xl.goods_id ' .
            ' LEFT JOIN ' . $this->model->pre . 'member_price AS mp ' . "ON mp.goods_id = g.goods_id " . "WHERE $where GROUP BY g.goods_id ORDER BY $sort $order LIMIT $page , $size";
        $res = $this->model->query($sql);
        foreach ($res as $key => $val) {
            $res[$key]['url'] = url('goods/index', array('id' => $val['goods_id']));
			$res[$key]['goods_img'] = get_image_path($val['goods_id'], $val['goods_img']);
			$res[$key]['goods_thumb'] = get_image_path($val['goods_id'], $val['goods_thumb']);
        }
        return $res;
    }

    //获取商品总条数
    private function get_goods_count($cat_id, $brand, $price_min, $price_max)
    {
        $where = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND " . "g.is_delete = 0 ";
        if ($cat_id !== 0) {
            $where .= "AND(g.cat_id = $cat_id OR " .model('Goods')->get_extension_goods($cat_id) .")";
        }
        if (!empty($brand) && $brand !== 0) {
            $where .= " AND g.brand_id = '" . $brand . "' ";
        }
        if (isset($price_min) && !empty($price_max)) {
            $where .= " AND g.shop_price >= '" . $price_min . "' AND g.shop_price <= '" . $price_max . "'";
        }
        $sql = 'SELECT COUNT(g.goods_id) AS count FROM ' . $this->model->pre . 'goods AS g ' . ' LEFT JOIN ' . $this->model->pre . 'touch_goods AS xl ' . ' ON g.goods_id=xl.goods_id ' .
            "WHERE $where";
        $count = $this->model->getRow($sql);
        return $count['count'];
    }


}
