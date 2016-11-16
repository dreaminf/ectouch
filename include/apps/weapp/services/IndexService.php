<?php
namespace apps\weapp\services;

class IndexService {

    private $commonService;
    public function __construct()
    {
        $this->commonService = new CommonService();
    }

    /**
     * 获取banner
     */
    function get_top_banner($id, $num){

        $time = gmtime();

        $sql = 'SELECT a.ad_id, a.position_id, a.media_type, a.ad_link, a.ad_code, a.ad_name, p.ad_width, ' .
            'p.ad_height, p.position_style, RAND() AS rnd ' .
            'FROM ' . $GLOBALS['ecs']->table('touch_ad') . ' AS a ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('touch_ad_position') . ' AS p ON a.position_id = p.position_id ' .
            "WHERE enabled = 1 AND start_time <= '" . $time . "' AND end_time >= '" . $time . "' " .
            "AND a.position_id = '" . $id . "' " .
            'ORDER BY rnd LIMIT ' . $num;
        $res = $GLOBALS['db']->GetAll($sql);
        foreach ($res as $key=>$val){
            $res[$key]['picurl'] = $this->commonService->get_full_path(get_ad_path($val['ad_code'], 'data/afficheimg/'));
        }

        return $res;
    }

    /**
     * 获取品牌列表
     */
    function get_part_brand(){
        $sql = "SELECT brand_id,brand_name,brand_logo FROM {pre}brand WHERE is_show =1 LIMIT 20";

        $brand_list = $GLOBALS['db']->getAll($sql);

        //修改图片路径
        foreach($brand_list as $key => $val){
            $brand_list[$key]['id'] = $val['brand_id'];
            $brand_list[$key]['smallpic'] = $this->commonService->get_full_path(__ROOT__."../data/brandlogo/".$val['brand_logo']);
        }
        return $brand_list;
    }

    /**
     * 获取热门商品列表
     */
    function get_hot_goods_list(){
        //拼接sql
        $page = 0;
        $size = 10;
        $where = " g.is_on_sale = 1 AND g.is_alone_sale = 1 AND " . "g.is_delete = 0 AND g.review_status>2 ";

        $where .= " AND g.is_hot = '1'";

        $leftJoin = " left join " .$GLOBALS['ecs']->table('warehouse_goods'). " as wg on g.goods_id = wg.goods_id and wg.region_id = '$warehouse_id' ";
        $leftJoin .= " left join " .$GLOBALS['ecs']->table('warehouse_area_goods'). " as wag on g.goods_id = wag.goods_id and wag.region_id = '$area_id' ";
        if($GLOBALS['_CFG']['open_area_goods'] == 1){
            $leftJoin .= " left join " .$GLOBALS['ecs']->table('link_area_goods'). " as lag on g.goods_id = lag.goods_id ";
            $where .= " and lag.region_id = '$area_id' ";
        }

        $sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb ' .
            'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
            "WHERE $where ORDER BY g.sort_order LIMIT $page , $size";
        $goods_list = $GLOBALS['db']->getAll($sql);
        //修改图片路径
        foreach($goods_list as $key => $val){
            $goods_list[$key]['goods_img'] = $this->commonService->get_full_path(__ROOT__.get_image_path($val['goods_img']));
            $goods_list[$key]['goods_thumb'] = $this->commonService->get_full_path(__ROOT__.get_image_path($val['goods_thumb']));
            $goods_list[$key]['url'] = build_uri('goods', array('gid' => $val['goods_id'],'u'=>$_SESSION['user_id']));
        }

        return $goods_list;
    }
}