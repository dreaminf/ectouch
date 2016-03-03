<?php
namespace search\Controller;
use common\Controller\FrontendController;
class IndexController extends FrontendController {
    public function indexAction(){
    	/* 缓存编号 */
		$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));

		if (!$this->is_cached('index.dwt', $cache_id))
		{
		    assign_template();

		    $position = assign_ur_here();
		    $this->assign('page_title',      $position['title']);    // 页面标题
		    $this->assign('ur_here',         $position['ur_here']);  // 当前位置

		    /* meta information */
		    $this->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
		    $this->assign('description',     htmlspecialchars($_CFG['shop_desc']));
		    $this->assign('flash_theme',     $_CFG['flash_theme']);  // Flash轮播图片模板

		    $this->assign('feed_url',        ($_CFG['rewrite'] == 1) ? 'feed.xml' : U('feed/index/index')); // RSS URL

		    $this->assign('categories',      get_categories_tree()); // 分类树
		    $this->assign('helps',           get_shop_help());       // 网店帮助
		    $this->assign('top_goods',       get_top10());           // 销售排行

		    $this->assign('best_goods',      get_recommend_goods('best'));    // 推荐商品
		    $this->assign('new_goods',       get_recommend_goods('new'));     // 最新商品
		    $this->assign('hot_goods',       get_recommend_goods('hot'));     // 热点文章
		    $this->assign('promotion_goods', get_promote_goods()); // 特价商品
		    $this->assign('brand_list',      get_brands());
		    $this->assign('promotion_info',  get_promotion_info()); // 增加一个动态显示所有促销信息的标签栏

		    $this->assign('invoice_list',    index_get_invoice_query());  // 发货查询
		    $this->assign('new_articles',    index_get_new_articles());   // 最新文章
		    $this->assign('group_buy_goods', index_get_group_buy());      // 团购商品
		    $this->assign('auction_list',    index_get_auction());        // 拍卖活动
		    $this->assign('shop_notice',     $_CFG['shop_notice']);       // 商店公告

		    /* 首页主广告设置 */
		    $this->assign('index_ad',     $_CFG['index_ad']);
		    if ($_CFG['index_ad'] == 'cus')
		    {
		        $sql = 'SELECT ad_type, content, url FROM ' . $ecs->table("ad_custom") . ' WHERE ad_status = 1';
		        $ad = $db->getRow($sql, true);
		        $this->assign('ad', $ad);
		    }

		    /* links */
		    $links = index_get_links();
		    $this->assign('img_links',       $links['img']);
		    $this->assign('txt_links',       $links['txt']);
		    $this->assign('data_dir',        DATA_DIR);       // 数据目录

		    /* 首页推荐分类 */
		    $cat_recommend_res = $this->db->getAll("SELECT c.cat_id, c.cat_name, cr.recommend_type FROM " . $this->ecs->table("cat_recommend") . " AS cr INNER JOIN " . $this->ecs->table("category") . " AS c ON cr.cat_id=c.cat_id");
		    if (!empty($cat_recommend_res))
		    {
		        $cat_rec_array = array();
		        foreach($cat_recommend_res as $cat_recommend_data)
		        {
		            $cat_rec[$cat_recommend_data['recommend_type']][] = array('cat_id' => $cat_recommend_data['cat_id'], 'cat_name' => $cat_recommend_data['cat_name']);
		        }
		        $this->assign('cat_rec', $cat_rec);
		    }

		    /* 页面中的动态内容 */
		    assign_dynamic('index');
		}

		$this->display('index.dwt', $cache_id);
    }
}