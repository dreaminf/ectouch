<?php
namespace apps\weapp\services;

use Model;
use apps\weapp\services\CommonService;

class ClassifyService extends Model{
    protected $common;

    public function __construct()
    {
        parent::__construct();
        $this->common = new CommonService();
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
        return $this->row('SELECT cat_name, keywords, cat_desc, style, grade, filter_attr, parent_id, measure_unit FROM ' . $this->pre . "category WHERE cat_id = '$cat_id'");
    }


    /**
     * 获得分类下的商品
     *
     * @access public
     * @param string $children
     * @return array
     */
    public function category_get_goods()
    {
        $where = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND " . "g.is_delete = 0 ";
        if ($this->keywords != '') {
            $where .= " AND (( 1 " . $this->keywords . " ) ) ";
        } else {
            $where .= " AND ($this->children OR " . $this->common->model('Goods')->get_extension_goods($this->children) . ') ';
        }
        if ($this->type) {
            switch ($this->type) {
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
        if ($this->brand > 0) {
            $where .= "AND g.brand_id=$this->brand ";
        }
        if ($this->price_min > 0) {
            $where .= " AND g.shop_price >= $this->price_min ";
        }
        if ($this->price_max > 0) {
            $where .= " AND g.shop_price <= $this->price_max ";
        }

        $start = ($this->page - 1) * $this->size;
        $sort = $this->sort == 'sales_volume' ? 'xl.sales_volume' : $this->sort;
        /* 获得商品列表 */
        $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, g.last_update,' . "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, g.goods_type, g.goods_number, " .
            'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img, xl.sales_volume ' . 'FROM ' . $this->model->pre . 'goods AS g ' . ' LEFT JOIN ' . $this->model->pre . 'touch_goods AS xl ' . ' ON g.goods_id=xl.goods_id ' . ' LEFT JOIN ' . $this->model->pre . 'member_price AS mp ' . "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " . "WHERE $where $this->ext ORDER BY $sort $this->order LIMIT $start , $this->size";
        $res = $this->model->query($sql);
        $arr = array();
        foreach ($res as $key=>$row) {
            // 销量统计
            $sales_volume = (int)$row['sales_volume'];
            if (mt_rand(0, 3) == 3) {
                $sales_volume = $this->common->model('GoodsBase')->get_sales_count($row['goods_id']);
                $sql = 'REPLACE INTO ' . $this->model->pre . 'touch_goods(`goods_id`, `sales_volume`) VALUES(' . $row['goods_id'] . ', ' . $sales_volume . ')';
                $this->model->query($sql);
            }
            if ($row['promote_price'] > 0) {
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            } else {
                $promote_price = 0;
            }
            /* 处理商品水印图片 */
            $watermark_img = '';

            if ($promote_price != 0) {
                $watermark_img = "watermark_promote_small";
            } elseif ($row['is_new'] != 0) {
                $watermark_img = "watermark_new_small";
            } elseif ($row['is_best'] != 0) {
                $watermark_img = "watermark_best_small";
            } elseif ($row['is_hot'] != 0) {
                $watermark_img = 'watermark_hot_small';
            }

            if ($watermark_img != '') {
                $arr[$key]['watermark_img'] = $watermark_img;
            }

            $arr[$key]['goods_id'] = $row['goods_id'];
            if ($display == 'grid') {
                $arr[$key]['goods_name'] = C('goods_name_length') > 0 ? sub_str($row['goods_name'], C('goods_name_length')) : $row['goods_name'];
            } else {
                $arr[$key]['goods_name'] = $row['goods_name'];
            }
            $arr[$key]['name'] = $row['goods_name'];
            $arr[$key]['goods_brief'] = $row['goods_brief'];
            $arr[$key]['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
            $arr[$key]['market_price'] = price_format($row['market_price']);
            $arr[$key]['type'] = $row['goods_type'];
            if ($promote_price > 0) {
                $arr[$key]['shop_price'] = price_format($promote_price);
            } else {
                $arr[$key]['shop_price'] = price_format($row['shop_price']);
            }
            $arr[$key]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
            $arr[$key]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
            $arr[$key]['url'] = url('goods/index', array(
                'id' => $row['goods_id']
            ));
            //$arr[$key]['sales_count'] = $sales_volume;
            $arr[$key]['sc'] = model('GoodsBase')->get_goods_collect($row['goods_id']);
            $arr[$key]['mysc'] = 0;
            // 检查是否已经存在于用户的收藏夹
            if ($_SESSION['user_id']) {
                unset($where);
                // 用户自己有没有收藏过
                $where['goods_id'] = $row['goods_id'];
                $where['user_id'] = $_SESSION['user_id'];
                $rs = $this->model->table('collect_goods')
                    ->where($where)
                    ->count();
                $arr[$key]['mysc'] = $rs;
            }

            $arr[$key]['sales_count'] = model('GoodsBase')->get_sales_count($row['goods_id']);
            $arr[$key]['goods_number'] = $row['goods_number'];
            $arr[$key]['promotion'] = model('GoodsBase')->get_promotion_show($row['goods_id']);
            $arr[$key]['comment_count'] = model('Comment')->get_goods_comment($row['goods_id'], 0);  //商品总评论数量
            $arr[$key]['favorable_count'] = model('Comment')->favorable_comment($row['goods_id'], 0);  //获得商品好评百分比
        }
        return $arr;
    }
}



