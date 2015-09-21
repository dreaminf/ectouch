<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：BrandModel.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTOUCH 品牌模型
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class BrandModel extends BaseModel {

    /**
     * 获得品牌下的商品
     *
     * @access private
     * @param integer $brand_id 
     * @return array
     */
    function brand_get_goods($brand_id, $cate, $sort, $order, $size, $page) {
        $cate_where = ($cate > 0) ? 'AND ' . get_children($cate) : '';

        $start = ($page - 1) * $size;
        /* 获得商品列表 */
        $sort = $sort == 'sales_volume' ? 'xl.sales_volume' : $sort;
        $sql = 'SELECT g.goods_id, g.goods_name,g.goods_number, g.market_price, g.shop_price AS org_price, ' . "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, " . 'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img ' . 'FROM ' . $this->pre . 'goods AS g ' . 'LEFT JOIN ' . $this->pre . 'touch_goods AS xl ' . "ON g.goods_id=xl.goods_id " . 'LEFT JOIN ' . $this->pre . 'member_price AS mp ' . "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " . "WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.brand_id = '$brand_id' $cate_where" . "ORDER BY $sort $order LIMIT $start , $size";
        $res = $this->query($sql);
        $arr = array();
        foreach ($res as $row) {
            if ($row['promote_price'] > 0) {
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            } else {
                $promote_price = 0;
            }

            $arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
            if ($GLOBALS['display'] == 'grid') {
                $arr[$row['goods_id']]['goods_name'] = C('goods_name_length') > 0 ? sub_str($row['goods_name'], C('goods_name_length')) : $row['goods_name'];
            } else {
                $arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
            }
            $arr[$row['goods_id']]['discount'] = $row['market_price'] > 0 ? (round((($promote_price > 0 ? $promote_price : $row['shop_price']) / $row['market_price']) * 10)) : 0;
            $arr[$row['goods_id']]['goods_number'] = $row['goods_number'];
            $arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
            $arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
            $arr[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
            $arr[$row['goods_id']]['goods_brief'] = $row['goods_brief'];
            $arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
            $arr[$row['goods_id']]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
            $arr[$row['goods_id']]['url'] = url('goods/index', array('id' => $row['goods_id']));
            $arr[$row['goods_id']]['sales_count'] = model('GoodsBase')->get_sales_count($row['goods_id']);
            $arr[$row['goods_id']]['sc'] = model('GoodsBase')->get_goods_collect($row['goods_id']);
            $arr[$row['goods_id']]['promotion'] = model('GoodsBase')->get_promotion_show($row['goods_id']);
            $arr[$row['goods_id']]['comment_count'] = model('Comment')->get_goods_comment($row['goods_id'], 0);  //商品总评论数量
            $arr[$row['goods_id']]['favorable_count'] = model('Comment')->favorable_comment($row['goods_id'], 0);  //获得商品好评数量
            $arr[$row['goods_id']]['mysc'] = 0;
            // 检查是否已经存在于用户的收藏夹
            if ($_SESSION['user_id']) {
                unset($where);
                // 用户自己有没有收藏过
                $where['goods_id'] = $row['goods_id'];
                $where['user_id'] = $_SESSION['user_id'];
                $rs = $this->model->table('collect_goods')
                        ->where($where)
                        ->count();
                $arr[$row['goods_id']]['mysc'] = $rs;
            }
        }

        return $arr;
    }

    /**
     * 获得品牌列表
     *
     * @global type $page_libs
     * @staticvar null $static_page_libs
     * @param type $cat 
     * @param type $app 
     * @param type $size 
     * @param type $page 
     * @return type
     */
    function get_brands($app = 'brand', $size, $page) {
        $start = ($page - 1) * $size;
        $sql = "SELECT brand_id, brand_name, brand_logo, brand_desc,brand_banner FROM " . $this->pre . "brand WHERE is_show = 1 GROUP BY brand_id , sort_order order by CONVERT (brand_name USING gbk) COLLATE gbk_chinese_ci ASC LIMIT $start , $size";
        $res = $this->query($sql);
        $arr = array();
        foreach ($res as $row) {
            $brand['brand_id'] = $row['brand_id'];
            $brand['brand_name'] = $row['brand_name'];
            $brand['url'] = url('brand/goods_list', array('id' => $row['brand_id']));
            $brand['brand_logo'] = __ROOT__ . '/data/attached/brandlogo/' .get_banner_path($row['brand_logo']);
            $brand['brand_banner'] = __ROOT__ . '/data/attached/brandbanner/' .get_banner_path($row['brand_banner']);
            $brand['goods_num'] = model('Brand')->goods_count_by_brand($row['brand_id']);
            $brand['brand_desc'] = htmlspecialchars($row['brand_desc'], ENT_QUOTES);
            $first = $this->getLetter($row['brand_name']);
            $arr[$first]['info'] = $first;
            $arr[$first]['list'][] = $brand;
        }
        ksort($arr);
        $arr[]= array();
        return $arr;
    }


    function getLetter($str){
        $str= iconv("UTF-8","gb2312", $str);
        $i=0;
        while($i<strlen($str) ) {
            $tmp=bin2hex(substr($str,$i,1));
            if($tmp>='B0'){ //汉字的开始
                return $this->chineseFirst($str);
                $i+=2;
            }
            else{
                return strtoupper(substr($str,$i,1));
                $i++;
            }
        }
    }
    function chineseFirst($str)
    {
        //判断字符串是否全都是中文
        if (preg_match("/^[\x7f-\xff]/", $str))
        {
            $fchar=ord($str{0});
            if($fchar>=ord("A") and $fchar<=ord("z") )return strtoupper($str{0});
            $a = $str;
            $val=ord($a{0})*256+ord($a{1})-65536;
            if($val>=-20319 and $val<=-20284)return "A";
            if($val>=-20283 and $val<=-19776)return "B";
            if($val>=-19775 and $val<=-19219)return "C";
            if($val>=-19218 and $val<=-18711)return "D";
            if($val>=-18710 and $val<=-18527)return "E";
            if($val>=-18526 and $val<=-18240)return "F";
            if($val>=-18239 and $val<=-17923)return "G";
            if($val>=-17922 and $val<=-17418)return "H";
            if($val>=-17417 and $val<=-16475)return "J";
            if($val>=-16474 and $val<=-16213)return "K";
            if($val>=-16212 and $val<=-15641)return "L";
            if($val>=-15640 and $val<=-15166)return "M";
            if($val>=-15165 and $val<=-14923)return "N";
            if($val>=-14922 and $val<=-14915)return "O";
            if($val>=-14914 and $val<=-14631)return "P";
            if($val>=-14630 and $val<=-14150)return "Q";
            if($val>=-14149 and $val<=-14091)return "R";
            if($val>=-14090 and $val<=-13319)return "S";
            if($val>=-13318 and $val<=-12839)return "T";
            if($val>=-12838 and $val<=-12557)return "W";
            if($val>=-12556 and $val<=-11848)return "X";
            if($val>=-11847 and $val<=-11056)return "Y";
            if($val>=-11055 and $val<=-10247)return "Z";
        } else
        {
            return false;
        }

    }
    /**
     * 获得品牌列表
     *
     * @global type $page_libs
     * @staticvar null $static_page_libs
     * @param type $cat
     * @param type $app
     * @param type $size
     * @param type $page
     * @return type
     */
    function get_brands_hj() {
        $sql = "SELECT brand_id, brand_name, brand_logo, brand_desc,brand_banner FROM " . $this->pre . "brand WHERE is_show = 1 GROUP BY brand_id , sort_order order by sort_order ASC";
        $res = $this->query($sql);
        $arr = array();
        foreach ($res as $key=>$row) {
            if($key == 0 ){
                $arr['top'][$row['brand_id']]['brand_id']   =   $row['brand_id'];
                $arr['top'][$row['brand_id']]['brand_name'] =   $row['brand_name'];
                $arr['top'][$row['brand_id']]['url']    =   url('brand/goods_list', array('id' => $row['brand_id']));
                $arr['top'][$row['brand_id']]['brand_logo'] =   __ROOT__ . '/data/attached/brandlogo/' .get_banner_path($row['brand_logo']);
                $arr['top'][$row['brand_id']]['brand_banner']   =   __ROOT__ . '/data/attached/brandbanner/' .get_banner_path($row['brand_banner']);
                $arr['top'][$row['brand_id']]['goods_num']  =   model('Brand')->goods_count_by_brand($row['brand_id']);
                $arr['top'][$row['brand_id']]['brand_desc'] =   htmlspecialchars($row['brand_desc'], ENT_QUOTES);
            }elseif($key == 1 ){
                $arr['center'][$row['brand_id']]['brand_id']   =   $row['brand_id'];
                $arr['center'][$row['brand_id']]['brand_name'] =   $row['brand_name'];
                $arr['center'][$row['brand_id']]['url']    =   url('brand/goods_list', array('id' => $row['brand_id']));
                $arr['center'][$row['brand_id']]['brand_logo'] =   __ROOT__ . '/data/attached/brandlogo/' .get_banner_path($row['brand_logo']);
                $arr['center'][$row['brand_id']]['brand_banner']   =   __ROOT__ . '/data/attached/brandbanner/' .get_banner_path($row['brand_banner']);
                $arr['center'][$row['brand_id']]['goods_num']  =   model('Brand')->goods_count_by_brand($row['brand_id']);
                $arr['center'][$row['brand_id']]['brand_desc'] =   htmlspecialchars($row['brand_desc'], ENT_QUOTES);
            }elseif($key > 1 && $key < 6){
                $arr['list1'][$row['brand_id']]['brand_id']   =   $row['brand_id'];
                $arr['list1'][$row['brand_id']]['brand_name'] =   $row['brand_name'];
                $arr['list1'][$row['brand_id']]['url']    =   url('brand/goods_list', array('id' => $row['brand_id']));
                $arr['list1'][$row['brand_id']]['brand_logo'] =   __ROOT__ . '/data/attached/brandlogo/' .get_banner_path($row['brand_logo']);
                $arr['list1'][$row['brand_id']]['brand_banner']   =   __ROOT__ . '/data/attached/brandbanner/' .get_banner_path($row['brand_banner']);
                $arr['list1'][$row['brand_id']]['goods_num']  =   model('Brand')->goods_count_by_brand($row['brand_id']);
                $arr['list1'][$row['brand_id']]['brand_desc'] =   htmlspecialchars($row['brand_desc'], ENT_QUOTES);
            }else{
                $arr['list2'][$row['brand_id']]['brand_id']   =   $row['brand_id'];
                $arr['list2'][$row['brand_id']]['brand_name'] =   $row['brand_name'];
                $arr['list2'][$row['brand_id']]['url']    =   url('brand/goods_list', array('id' => $row['brand_id']));
                $arr['list2'][$row['brand_id']]['brand_logo'] =   __ROOT__ . '/data/attached/brandlogo/' .get_banner_path($row['brand_logo']);
                $arr['list2'][$row['brand_id']]['brand_banner']   =   __ROOT__ . '/data/attached/brandbanner/' .get_banner_path($row['brand_banner']);
                $arr['list2'][$row['brand_id']]['goods_num']  =   model('Brand')->goods_count_by_brand($row['brand_id']);
                $arr['list2'][$row['brand_id']]['brand_desc'] =   htmlspecialchars($row['brand_desc'], ENT_QUOTES);
            }

        }
        return $arr;
    }

    /**
     * 获得指定的品牌下的商品总数
     *
     * @access  private
     * @param   integer     $brand_id
     * @param   integer     $cate
     * @return  integer
     */
    function goods_count_by_brand($brand_id, $cate = 0) {
        $sql = 'SELECT COUNT(*) as count FROM ' . $this->pre . 'goods AS g ' .
                "WHERE brand_id = '$brand_id' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ";

        if ($cate > 0) {
            $sql .= " AND " . get_children($cate);
        }
        $res = $this->row($sql);
        return $res['count'];
    }
    
    /**
     * 获得品牌数量
     *
     */
    function get_brands_count() {
        $sql = "SELECT count(*) as num FROM " . $this->pre . "brand WHERE is_show = 1 ";
        $res = $this->row($sql);
        $sales_count = $res['num'] ? $res['num'] : 0;
        return $sales_count;
    }

    /**
     * 获得品牌下的商品
     *
     * @access private
     * @param integer $brand_id
     * @return array
     */
    function brand_get_goods_img($brand_id, $cate, $sort, $order, $size, $page) {
        $start = ($page - 1) * $size;
        /* 获得商品列表 */
        $sort = $sort == 'sales_volume' ? 'xl.sales_volume' : $sort;
        $sql = 'SELECT goods_id,goods_img FROM ' . $this->pre . "goods  WHERE is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 AND brand_id = '$brand_id' " . "ORDER BY $sort $order LIMIT $start , $size";
        $res = $this->query($sql);
        $arr = array();
        foreach ($res as $key=>$row) {
            $arr[$key]['goods_id'] = $row['goods_id'];
            $arr[$key]['url'] = url('goods/index', array('id' => $row['goods_id']));
            $arr[$key]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_img'], true);

        }

        return $arr;
    }

}
