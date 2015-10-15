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
        $this->display('category_all.dwt', $cache_id);
    }

    public function products()
    {
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
        $filter_attr_str = preg_match('/^[\d\.]+$/',$filter_attr_str) ? $filter_attr_str : '';
        $this->parameter['filter_attr'] = empty($filter_attr_str) ? '' : explode('.', $filter_attr_str);
        // 排序、显示方式以及类型
        $default_display_type = C('show_order_type') == '0' ? 'list' : (C('show_order_type') == '1' ? 'grid' : 'text');
        $default_sort_order_method = C('sort_order_method') == '0' ? 'DESC' : 'ASC';
        $default_sort_order_type   = C('sort_order_type') == '0' ? 'goods_id' : (C('sort_order_type') == '1' ? 'shop_price' : 'last_update');
        $sort  = I('sort');
        $order = I('order');
        $display = I('display');
        $display = in_array(strtolower($display), array('list', 'grid', 'text')) ? $display : (isset($_COOKIE['ECS']['display']) ? $_COOKIE['ECS']['display'] : $default_display_type);
        $this->parameter['sort'] = in_array(strtolower($sort), array('goods_id', 'shop_price', 'last_update')) ? $sort : $default_sort_order_type;
        $this->parameter['order'] = in_array(strtoupper($order), array('ASC', 'DESC')) ? $order : $default_sort_order_method;
        $this->parameter['display'] = in_array($display, array('list', 'grid', 'text')) ? $display : 'list';
        setcookie('ECS[display]', $this->parameter['display'], gmtime() + 86400 * 7);
    }

}
