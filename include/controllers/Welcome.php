<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends IndexController
{

    public function index()
    {
        /* 缓存编号 */
        $cache_id = $this->get_cache_id();
        if (!$this->is_cached('index.dwt', $cache_id)) {
            assign_template();

            $position = assign_ur_here();
            $this->assign('page_title', $position['title']);    // 页面标题
            $this->assign('ur_here', $position['ur_here']);  // 当前位置

            /* meta information */
            $this->assign('keywords', htmlspecialchars(C('shop_keywords')));
            $this->assign('description', htmlspecialchars(C('shop_desc')));

            $this->assign('categories', get_categories_tree()); // 分类树
            $this->assign('helps', get_shop_help());       // 网店帮助
            $this->assign('top_goods', get_top10());           // 销售排行

            $this->assign('best_goods', get_recommend_goods('best'));    // 推荐商品
            $this->assign('new_goods', get_recommend_goods('new'));     // 最新商品
            $this->assign('hot_goods', get_recommend_goods('hot'));     // 热点文章
            $this->assign('promotion_goods', get_promote_goods()); // 特价商品
            $this->assign('brand_list', get_brands());
            $this->assign('promotion_info', get_promotion_info()); // 增加一个动态显示所有促销信息的标签栏

            $this->assign('new_articles', $this->getNewArticles());   // 最新文章
            $this->assign('group_buy_goods', $this->getGroupBuy());      // 团购商品
            $this->assign('auction_list', $this->getAuction());        // 拍卖活动
            $this->assign('shop_notice', C('shop_notice'));       // 商店公告

            /* 首页主广告设置 */
            $this->assign('index_ad', C('index_ad'));
            if (C('index_ad') == 'cus') {
                $sql = 'SELECT ad_type, content, url FROM {pre}ad_custom WHERE ad_status = 1';
                $ad = $this->load->db->getRow($sql, true);
                $this->assign('ad', $ad);
            }

            /* 首页推荐分类 */
            $cat_recommend_res = $this->load->db->getAll("SELECT c.cat_id, c.cat_name, cr.recommend_type FROM {pre}cat_recommend AS cr INNER JOIN {pre}category AS c ON cr.cat_id=c.cat_id");
            if (!empty($cat_recommend_res)) {
                $cat_rec = array();
                foreach ($cat_recommend_res as $cat_recommend_data) {
                    $cat_rec[$cat_recommend_data['recommend_type']][] = array('cat_id' => $cat_recommend_data['cat_id'], 'cat_name' => $cat_recommend_data['cat_name']);
                }
                $this->assign('cat_rec', $cat_rec);
            }

            /* 页面中的动态内容 */
            assign_dynamic('index');
        }

        $this->display('index.dwt', $cache_id);
    }

    /**
     * 获得最新的文章列表。
     *
     * @access  private
     * @return  array
     */
    private function getNewArticles()
    {
        $sql = 'SELECT a.article_id, a.title, ac.cat_name, a.add_time, a.file_url, a.open_type, ac.cat_id, ac.cat_name ' .
            ' FROM {pre}article AS a, {pre}article_cat AS ac' .
            ' WHERE a.is_open = 1 AND a.cat_id = ac.cat_id AND ac.cat_type = 1' .
            ' ORDER BY a.article_type DESC, a.add_time DESC LIMIT ' . C('article_number');
        $res = $this->load->db->getAll($sql);

        $arr = array();
        foreach ($res AS $idx => $row) {
            $arr[$idx]['id'] = $row['article_id'];
            $arr[$idx]['title'] = $row['title'];
            $arr[$idx]['short_title'] = C('article_title_length') > 0 ? sub_str($row['title'], C('article_title_length')) : $row['title'];
            $arr[$idx]['cat_name'] = $row['cat_name'];
            $arr[$idx]['add_time'] = local_date(C('date_format'), $row['add_time']);
            $arr[$idx]['url'] = $row['open_type'] != 1 ? build_uri('article', array('aid' => $row['article_id']), $row['title']) : trim($row['file_url']);
            $arr[$idx]['cat_url'] = build_uri('article_cat', array('acid' => $row['cat_id']), $row['cat_name']);
        }

        return $arr;
    }

    /**
     * 获得最新的团购活动
     *
     * @access  private
     * @return  array
     */
    private function getGroupBuy()
    {
        $time = gmtime();
        $limit = get_library_number('group_buy', 'index');

        $group_buy_list = array();
        if ($limit > 0) {
            $sql = 'SELECT gb.act_id AS group_buy_id, gb.goods_id, gb.ext_info, gb.goods_name, g.goods_thumb, g.goods_img ' .
                'FROM {pre}goods_activity AS gb, {pre}goods AS g ' .
                "WHERE gb.act_type = '" . GAT_GROUP_BUY . "' " .
                "AND g.goods_id = gb.goods_id " .
                "AND gb.start_time <= '" . $time . "' " .
                "AND gb.end_time >= '" . $time . "' " .
                "AND g.is_delete = 0 " .
                "ORDER BY gb.act_id DESC " .
                "LIMIT $limit";
            $res = $this->load->db->query($sql);

            while ($row = $this->load->db->fetchRow($res)) {
                /* 如果缩略图为空，使用默认图片 */
                $row['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
                $row['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);

                /* 根据价格阶梯，计算最低价 */
                $ext_info = unserialize($row['ext_info']);
                $price_ladder = $ext_info['price_ladder'];
                if (!is_array($price_ladder) || empty($price_ladder)) {
                    $row['last_price'] = price_format(0);
                } else {
                    foreach ($price_ladder AS $amount_price) {
                        $price_ladder[$amount_price['amount']] = $amount_price['price'];
                    }
                }
                ksort($price_ladder);
                $row['last_price'] = price_format(end($price_ladder));
                $row['url'] = build_uri('group_buy', array('gbid' => $row['group_buy_id']));
                $row['short_name'] = C('goods_name_length') > 0 ? sub_str($row['goods_name'], C('goods_name_length')) : $row['goods_name'];
                $row['short_style_name'] = add_style($row['short_name'], '');
                $group_buy_list[] = $row;
            }
        }

        return $group_buy_list;
    }

    /**
     * 取得拍卖活动列表
     * @return  array
     */
    private function getAuction()
    {
        $now = gmtime();
        $limit = get_library_number('auction', 'index');
        $sql = "SELECT a.act_id, a.goods_id, a.goods_name, a.ext_info, g.goods_thumb " .
            "FROM {pre}goods_activity AS a, {pre}goods AS g" .
            " WHERE a.goods_id = g.goods_id" .
            " AND a.act_type = '" . GAT_AUCTION . "'" .
            " AND a.is_finished = 0" .
            " AND a.start_time <= '$now'" .
            " AND a.end_time >= '$now'" .
            " AND g.is_delete = 0" .
            " ORDER BY a.start_time DESC" .
            " LIMIT $limit";
        $res = $this->load->db->query($sql);

        $list = array();
        while ($row = $this->load->db->fetchRow($res)) {
            $ext_info = unserialize($row['ext_info']);
            $arr = array_merge($row, $ext_info);
            $arr['formated_start_price'] = price_format($arr['start_price']);
            $arr['formated_end_price'] = price_format($arr['end_price']);
            $arr['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
            $arr['url'] = build_uri('auction', array('auid' => $arr['act_id']));
            $arr['short_name'] = C('goods_name_length') > 0 ? sub_str($arr['goods_name'], C('goods_name_length')) : $arr['goods_name'];
            $arr['short_style_name'] = add_style($arr['short_name'], '');
            $list[] = $arr;
        }

        return $list;
    }
}
