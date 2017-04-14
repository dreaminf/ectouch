--
-- 转存表中的数据 `ecs_ad_position 广告位置`
--

INSERT INTO `ecs_ad_position` (`position_id`, `position_name`, `ad_width`, `ad_height`, `position_desc`, `position_style`, `tc_id`, `tc_type`) VALUES
(255, '手机端首页Banner广告位', 360, 168, '', '{foreach from=$ads item=ad}<div class="swiper-slide">{$ad}</div>{/foreach}', 0, ''),
(256, '手机端首页主题精选广告位', 360, 168, '', '{foreach from=$ads item=ad name=ads}\r\n<div class="swiper-slide swiper-slide-active">\r\n{$ad}\r\n</div>\r\n{/foreach}', 0, ''),
(257, '手机端首页推荐广告位1', 360, 168, '', '{foreach from=$ads item=ad name=ads}{$ad}{/foreach}', 0, ''),
(266, '服装首页banner', 360, 168, '', '{foreach from=$ads item=ad}<div class="swiper-slide">{$ad}</div>{/foreach}', 2, 'banner'),
(265, '手机端首页推荐广告位2', 360, 168, '', '{foreach from=$ads item=ad}\r\n{$ad}\r\n{/foreach}\r\n', 0, ''),
(267, '美妆首页banner', 360, 168, '', '{foreach from=$ads item=ad}<div class="swiper-slide">{$ad}</div>{/foreach}', 3, 'banner'),
(268, '服装首页bottom', 360, 168, '', '{foreach from=$ads item=ad}\r\n{$ad}\r\n{/foreach}\r\n', 2, 'bottom'),
(269, '服装首页right', 360, 168, '', '{foreach from=$ads item=ad name=ads}\r\n{$ad}\r\n{/foreach}', 2, 'right'),
(270, '服装首页left', 360, 168, '', '{foreach from=$ads item=ad}\r\n{$ad}\r\n{/foreach}\r\n', 2, 'left'),
(271, '美妆首页bottom', 360, 168, '', '{foreach from=$ads item=ad}\r\n{$ad}\r\n{/foreach}\r\n', 3, 'bottom'),
(272, '美妆首页right', 360, 168, '', '{foreach from=$ads item=ad name=ads}\r\n{$ad}\r\n{/foreach}', 3, 'right'),
(273, '美妆首页left', 360, 168, '', '{foreach from=$ads item=ad}\r\n{$ad}\r\n{/foreach}\r\n', 3, 'left'),
(274, '母婴首页banner', 360, 168, '', '{foreach from=$ads item=ad}<div class="swiper-slide">{$ad}</div>{/foreach}', 4, 'banner'),
(275, '母婴首页left', 360, 168, '', '{foreach from=$ads item=ad}\r\n{$ad}\r\n{/foreach}\r\n', 4, 'left'),
(276, '母婴首页right', 360, 168, '', '{foreach from=$ads item=ad name=ads}\r\n{$ad}\r\n{/foreach}', 4, 'right'),
(277, '母婴首页bottom', 360, 168, '', '{foreach from=$ads item=ad}\r\n{$ad}\r\n{/foreach}\r\n', 4, 'bottom'),
(278, '数码首页banner', 360, 168, '', '{foreach from=$ads item=ad}<div class="swiper-slide">{$ad}</div>{/foreach}', 5, 'banner'),
(279, '数码首页left', 360, 168, '', '{foreach from=$ads item=ad}\r\n{$ad}\r\n{/foreach}\r\n', 5, 'left'),
(280, '数码首页right', 360, 168, '', '{foreach from=$ads item=ad name=ads}\r\n{$ad}\r\n{/foreach}', 5, 'right'),
(281, '数码首页bottom', 360, 168, '', '{foreach from=$ads item=ad}\r\n{$ad}\r\n{/foreach}\r\n', 5, 'bottom'),
(282, '电器首页banner', 360, 168, '', '{foreach from=$ads item=ad}<div class="swiper-slide">{$ad}</div>{/foreach}', 6, 'banner'),
(283, '电器首页left', 360, 168, '', '{foreach from=$ads item=ad}\r\n{$ad}\r\n{/foreach}\r\n', 6, 'left'),
(284, '电器首页right', 360, 168, '', '{foreach from=$ads item=ad name=ads}\r\n{$ad}\r\n{/foreach}', 6, 'right'),
(285, '电器首页bottom', 360, 168, '', '{foreach from=$ads item=ad}\r\n{$ad}\r\n{/foreach}\r\n', 6, 'bottom'),
(286, '生鲜首页banner', 360, 168, '', '{foreach from=$ads item=ad}<div class="swiper-slide">{$ad}</div>{/foreach}', 1, 'banner'),
(287, '生鲜首页left', 360, 168, '', '{foreach from=$ads item=ad}\r\n{$ad}\r\n{/foreach}\r\n', 1, 'left'),
(288, '生鲜首页right', 360, 168, '', '{foreach from=$ads item=ad name=ads}\r\n{$ad}\r\n{/foreach}', 1, 'right'),
(289, '生鲜首页bottom', 360, 168, '', '{foreach from=$ads item=ad}\r\n{$ad}\r\n{/foreach}\r\n', 1, 'bottom');

--
-- 转存表中的数据 `ecs_ad`
--

INSERT INTO `ecs_ad` (`ad_id`, `position_id`, `media_type`, `ad_name`, `ad_link`, `ad_code`, `start_time`, `end_time`, `link_man`, `link_email`, `link_phone`, `click_count`, `enabled`) VALUES
(1, 255, 0, '首页Banner广告位1', '', 'index_banner_1.jpg', 1396339200, 1525161600, '', '', '', 1, 1),
(2, 255, 0, '首页Banner广告位2', '', 'index_banner_2.jpg', 1396339200, 1525161600, '', '', '', 1, 1),
(3, 255, 0, '首页Banner广告位3', '', 'index_banner_3.jpg', 1396339200, 1525161600, '', '', '', 0, 1),
(4, 256, 0, '手机端首页主题精选广告位1', '', 'index_ads_23.png', 1396339200, 1525161600, '', '', '', 1, 1),
(5, 256, 0, '手机端首页主题精选广告位2', '', 'index_ads_23.png', 1396339200, 1525161600, '', '', '', 0, 1),
(29, 266, 0, '服装banner01', '', 'team_banner_01.jpg', 1474185600, 1508918400, '', '', '', 0, 1),
(28, 265, 0, '推荐02', '', 'index_ads_22.png', 1442476800, 1508227200, '', '', '', 2, 1),
(27, 265, 0, '推荐01', '', 'index_ads_21.png', 1442476800, 1508227200, '', '', '', 2, 1),
(43, 275, 0, '母婴left', '', 'muying-left.jpg', 1474185600, 1508918400, '', '', '', 0, 1),
(14, 257, 0, '推荐广告位1', '', 'index_ads_20.png', 1396339200, 1525161600, '', '', '', 3, 1),
(46, 277, 0, '母婴bottom01', '', 'muying-bottom.jpg', 1474185600, 1508918400, '', '', '', 2, 1),
(45, 276, 0, '母婴right02', '', 'muying-right-2.jpg', 1474185600, 1508918400, '', '', '', 0, 1),
(44, 276, 0, '母婴right01', '', 'muying-right-1.jpg', 1474185600, 1508918400, '', '', '', 0, 1),
(41, 274, 0, '母婴banner01', '', 'index_banner_2.jpg', 1474185600, 1508918400, '', '', '', 0, 1),
(30, 266, 0, '服装banner02', '', 'team_banner_02.jpg', 1474185600, 1508918400, '', '', '', 1, 1),
(31, 267, 0, '美妆banner01', '', 'index_banner_1.jpg', 1474185600, 1508918400, '', '', '', 2, 1),
(32, 267, 0, '美妆banner02', '', 'index_banner_1.jpg', 1474185600, 1508918400, '', '', '', 0, 1),
(33, 268, 0, '服装bottom01', '', 'team_bottom.png', 1474185600, 1508918400, '', '', '', 1, 1),
(34, 270, 0, '服装left', '', 'index_ads_20.png', 1474185600, 1508918400, '', '', '', 0, 1),
(42, 274, 0, '母婴banner02', '', 'index_banner_2.jpg', 1474185600, 1508918400, '', '', '', 0, 1),
(35, 269, 0, '服装right01', '', 'index_ads_21.png', 1474185600, 1508918400, '', '', '', 0, 1),
(36, 269, 0, '服装right02', '', 'index_ads_22.png', 1474185600, 1508918400, '', '', '', 1, 1),
(37, 271, 0, '美妆bottom01', '', 'meizhuang-bottom.jpg', 1474185600, 1508918400, '', '', '', 2, 1),
(38, 273, 0, '美妆left', '', 'meizhuang-left.jpg', 1474185600, 1508918400, '', '', '', 0, 1),
(39, 272, 0, '美妆right01', '', 'meizhuang-right-1.jpg', 1474185600, 1508918400, '', '', '', 0, 1),
(40, 272, 0, '美妆right02', '', 'meizhuang-right-2.jpg', 1474185600, 1508918400, '', '', '', 0, 1),
(47, 278, 0, '数码banner01', '', 'team_banner_01.jpg', 1474185600, 1508918400, '', '', '', 1, 1),
(48, 278, 0, '数码banner02', '', 'team_banner_01.jpg', 1474185600, 1508918400, '', '', '', 0, 1),
(49, 279, 0, '数码left', '', 'index_ads_20.png', 1474185600, 1508918400, '', '', '', 0, 1),
(50, 280, 0, '数码right01', '', 'index_ads_21.png', 1474185600, 1508918400, '', '', '', 0, 1),
(51, 280, 0, '数码right02', '', 'index_ads_22.png', 1474185600, 1508918400, '', '', '', 0, 1),
(52, 281, 0, '数码bottom01', '', 'team_bottom.png', 1474185600, 1508918400, '', '', '', 2, 1),
(53, 282, 0, '电器banner01', '', 'team_banner_01.jpg', 1474185600, 1508918400, '', '', '', 0, 1),
(54, 282, 0, '电器banner02', '', 'team_banner_01.jpg', 1474185600, 1508918400, '', '', '', 0, 1),
(55, 283, 0, '电器left', '', 'index_ads_20.png', 1474185600, 1508918400, '', '', '', 0, 1),
(56, 284, 0, '电器right01', '', 'index_ads_21.png', 1474185600, 1508918400, '', '', '', 0, 1),
(57, 284, 0, '电器right02', '', 'index_ads_22.png', 1474185600, 1508918400, '', '', '', 0, 1),
(58, 285, 0, '电器bottom01', '', 'team_bottom.png', 1474185600, 1508918400, '', '', '', 2, 1),
(59, 286, 0, '生鲜banner01', '', 'index_banner_3.jpg', 1474185600, 1508918400, '', '', '', 1, 1),
(60, 286, 0, '生鲜banner02', '', 'index_banner_3.jpg', 1474185600, 1508918400, '', '', '', 1, 1),
(61, 287, 0, '生鲜left', '', 'shengxian-left.jpg', 1474185600, 1508918400, '', '', '', 0, 1),
(62, 288, 0, '生鲜right01', '', 'shengxian-right-1.jpg', 1474185600, 1508918400, '', '', '', 0, 1),
(63, 288, 0, '生鲜right02', '', 'shengxian-right-2.jpg', 1474185600, 1508918400, '', '', '', 0, 1),
(64, 289, 0, '生鲜bottom01', '', 'shengxian-bottom.jpg', 1474185600, 1508918400, '', '', '', 2, 1);

--
-- 转存表中的数据 `ecs_brand`
--

INSERT INTO `ecs_brand` (`brand_id`, `brand_name`, `brand_logo`, `brand_desc`, `site_url`, `sort_order`, `is_show`, `brand_banner`) VALUES
(1, '雷朋索飞诺', '1.jpg', '雷朋索飞诺公司是一家全球领先企业,为全球众多男士、女士提供舒适创新型鞋类产品。', 'http://', 50, 1, '1-1.jpg'),
(2, '欧时力', '2.jpg', '', 'http://', 50, 1, '2-1.jpg'),
(24, 'ECCO', '24.jpg', 'ECCO爱步公司是一家全球领先企业,为全球众多男士、女士提供舒适创新型鞋类产品。', 'http://', 50, 1, '24-1.jpg'),
(23, 'Bally', '23.jpg', '', 'http://', 50, 1, '23-1.jpg'),
(21, '魅族', '21.jpg', '', 'http://', 50, 1, '21-1.jpg'),
(22, 'Only', '22.jpg', '', 'http://', 50, 1, '22-1.jpg'),
(20, 'Gucci', '20.jpg', '', 'http://', 50, 1, '20-1.jpg'),
(18, 'Jack J', '18.jpg', '', 'http://', 50, 1, '18-1.jpg'),
(16, '三星', '16.jpg', '', 'http://', 50, 1, '16-1.jpg'),
(14, '哥伦比亚', '14.jpg', '', 'http://', 50, 1, '14-1.jpg'),
(12, '小米', '12.jpg', '', 'http://', 50, 1, '12-1.jpg'),
(10, '阿玛尼', '10.jpg', '', 'http://', 50, 1, '10-1.jpg'),
(8, '华为荣耀', '8.jpg', '', 'http://', 50, 1, '8-1.jpg'),
(4, 'Apple', '4.jpg', '', 'http://', 50, 1, '4-1.jpg'),
(5, 'nike', '5.jpg', '', 'http://', 50, 1, '5-1.jpg'),
(6, 'ca', '6.jpg', '', 'http://', 50, 1, '6-1.jpg'),
(19, 'Dior', '19.jpg', '', 'http://', 50, 1, '19-1.jpg'),
(15, '路易斯威登', '15.jpg', '', 'http://', 50, 1, '15-1.jpg'),
(13, 'Jack w', '13.jpg', '', 'http://', 50, 1, '13-1.jpg'),
(11, 'Puma', '11.jpg', '', 'http://', 50, 1, '11-1.jpg'),
(9, '阿迪达斯', '9.jpg', '', 'http://', 50, 1, '9-1.jpg'),
(7, 'Prada', '7.jpg', '', 'http://', 50, 1, '7-1.jpg'),
(3, 'nb', '3.jpg', '', 'http://', 50, 1, '3-1.jpg'),
(17, '锐步', '17.jpg', '', 'http://', 50, 1, '17-1.jpg'),
(25, '努比亚', '25.jpg', '', 'http://', 50, 1, '25-1.jpg');

--
-- 转存表中的数据 `ecs_category`
--

INSERT INTO `ecs_category` (`cat_id`, `cat_name`, `keywords`, `cat_desc`, `parent_id`, `sort_order`, `template_file`, `measure_unit`, `show_in_nav`, `style`, `is_show`, `grade`, `filter_attr`) VALUES
(1, '生鲜水果', '', '', 0, 50, '', '', 0, '', 1, 0, ''),
(2, '精品服装', '', '', 0, 50, '', '', 0, '', 1, 0, ''),
(3, '美妆护肤', '', '', 0, 50, '', '', 0, '', 1, 0, ''),
(4, '家居电器', '', '', 0, 50, '', '', 0, '', 1, 0, ''),
(5, '母婴用品', '', '', 0, 50, '', '', 0, '', 1, 0, ''),
(6, '数码智能', '', '', 0, 50, '', '', 0, '', 1, 0, ''),
(7, '新鲜水果', '', '', 1, 50, '', '', 0, '', 1, 0, ''),
(8, '海鲜水产', '', '', 1, 50, '', '', 0, '', 1, 0, ''),
(9, '新鲜蔬菜', '', '', 1, 50, '', '', 0, '', 1, 0, ''),
(10, '禽肉蛋品', '', '', 1, 50, '', '', 0, '', 1, 0, ''),
(11, '半身裙', '', '', 2, 50, '', '', 0, '', 1, 0, ''),
(12, '蕾丝衫', '', '', 2, 50, '', '', 0, '', 1, 0, ''),
(13, '牛仔裤', '', '', 2, 50, '', '', 0, '', 1, 0, ''),
(14, '休闲套装', '', '', 2, 50, '', '', 0, '', 1, 0, ''),
(15, '孕妇装', '', '', 5, 50, '', '', 0, '', 1, 0, ''),
(16, '孕妇洗护', '', '', 5, 50, '', '', 0, '', 1, 0, ''),
(17, '纸尿裤', '', '', 5, 50, '', '', 0, '', 1, 0, ''),
(18, '婴儿床', '', '', 5, 50, '', '', 0, '', 1, 0, ''),
(19, '笔记本', '', '', 6, 50, '', '', 0, '', 1, 0, ''),
(20, '路由器', '', '', 6, 50, '', '', 0, '', 1, 0, ''),
(21, '数码配件', '', '', 6, 50, '', '', 0, '', 1, 0, ''),
(22, '自拍神器', '', '', 6, 50, '', '', 0, '', 1, 0, ''),
(23, '面膜', '', '', 3, 50, '', '', 0, '', 1, 0, ''),
(24, '唇膏口红', '', '', 3, 50, '', '', 0, '', 1, 0, ''),
(25, '乳液面霜', '', '', 3, 50, '', '', 0, '', 1, 0, ''),
(26, '香水', '', '', 3, 50, '', '', 0, '', 1, 0, ''),
(27, '厨具', '', '', 4, 50, '', '', 0, '', 1, 0, ''),
(28, '灯具', '', '', 4, 50, '', '', 0, '', 1, 0, ''),
(29, '卫浴', '', '', 4, 50, '', '', 0, '', 1, 0, ''),
(30, '影音', '', '', 4, 50, '', '', 0, '', 1, 0, '');

--
-- 转存表中的数据 `ecs_goods`
--

INSERT INTO `ecs_goods` (`goods_id`, `cat_id`, `goods_sn`, `goods_name`, `goods_name_style`, `click_count`, `brand_id`, `provider_name`, `goods_number`, `goods_weight`, `market_price`, `virtual_sales`, `shop_price`, `promote_price`, `promote_start_date`, `promote_end_date`, `warn_number`, `keywords`, `goods_brief`, `goods_desc`, `goods_thumb`, `goods_img`, `original_img`, `is_real`, `extension_code`, `is_on_sale`, `is_alone_sale`, `is_shipping`, `integral`, `add_time`, `sort_order`, `is_delete`, `is_best`, `is_new`, `is_hot`, `is_promote`, `bonus_type_id`, `last_update`, `goods_type`, `seller_note`, `give_integral`, `rank_integral`, `suppliers_id`, `is_check`, `team_price`, `team_num`, `validity_time`, `limit_num`, `astrict_num`, `tc_id`, `is_team`) VALUES
(1, 9, 'ECS000000', '新鲜小胡萝卜有机蔬菜同城配送农产品非转基因手指胡萝卜满包邮.webp', '+', 0, 0, '', 100, '0.000', '72.00', '0', '60.00', '0.00', 0, 0, 1, '', '', '', 'data/attached/images/201610/thumb_img/1_thumb_G_1477355859880.jpg', 'data/attached/images/201610/goods_img/1_G_1477355859930.jpg', 'data/attached/images/201610/source_img/1_G_1477355859243.jpg', 1, '', 1, 1, 0, 0, 1477355859, 100, 0, 1, 0, 0, 0, 0, 1477355921, 0, '', -1, -1, 0, NULL, '29.00', 6, 24, 100, 6, 9, 1),
(2, 10, 'ECS000002', '洪门 有机土鸡蛋30枚新鲜农家自养散养草鸡蛋柴鸡蛋笨鸡蛋包邮.webp', '+', 0, 0, '', 100, '0.000', '79.20', '0', '66.00', '0.00', 0, 0, 1, '', '', '', 'data/attached/images/201610/thumb_img/2_thumb_G_1477355996640.jpg', 'data/attached/images/201610/goods_img/2_G_1477355996565.jpg', 'data/attached/images/201610/source_img/2_G_1477355996080.jpg', 1, '', 1, 1, 0, 0, 1477355996, 100, 0, 1, 0, 0, 0, 0, 1477356141, 0, '', -1, -1, 0, NULL, '18.00', 3, 24, 99, 6, 10, 1),
(3, 7, 'ECS000003', '30粒蒲江特产奇异果中小果应季猕猴桃礼盒装 新鲜水果', '+', 0, 0, '', 100, '0.000', '55.19', '0', '46.00', '0.00', 0, 0, 1, '', '', '', 'data/attached/images/201610/thumb_img/3_thumb_G_1477356070156.jpg', 'data/attached/images/201610/goods_img/3_G_1477356070944.jpg', 'data/attached/images/201610/source_img/3_G_1477356070406.jpg', 1, '', 1, 1, 0, 0, 1477356070, 100, 0, 1, 0, 1, 0, 0, 1477357199, 0, '', -1, -1, 0, NULL, '16.00', 5, 24, 98, 6, 7, 1),
(4, 8, 'ECS000004', '苏州好得睐私房菜清炒河虾仁250g新鲜冷冻虾仁方便菜冷冻半成品菜.webp', '+', 0, 0, '', 100, '0.000', '118.80', '0', '99.00', '0.00', 0, 0, 1, '', '', '', 'data/attached/images/201610/thumb_img/4_thumb_G_1477356137207.jpg', 'data/attached/images/201610/goods_img/4_G_1477356137615.jpg', 'data/attached/images/201610/source_img/4_G_1477356137112.jpg', 1, '', 1, 1, 0, 0, 1477356137, 100, 0, 1, 0, 1, 0, 0, 1477357197, 0, '', -1, -1, 0, NULL, '10.00', 24, 24, 20, 6, 8, 1),
(5, 18, 'ECS000005', '韩国玉兔高档实木婴儿床白色欧式多功能储物柜环保bb床超大宝宝床', '+', 2, 0, '', 100, '0.000', '312.00', '0', '260.00', '0.00', 0, 0, 1, '', '', '', 'data/attached/images/201610/thumb_img/5_thumb_G_1477356232657.jpg', 'data/attached/images/201610/goods_img/5_G_1477356232654.jpg', 'data/attached/images/201610/source_img/5_G_1477356232730.jpg', 1, '', 1, 1, 0, 2, 1477356232, 100, 0, 1, 1, 1, 0, 0, 1477357200, 0, '', -1, -1, 0, NULL, '160.00', 3, 24, 66, 6, 19, 1),
(6, 15, 'ECS000006', '孕妇装秋冬装韩版宽松大码孕妇秋季上衣长袖假两件孕妇连衣裙冬装', '+', 0, 0, '', 100, '0.000', '199.20', '0', '166.00', '0.00', 0, 0, 1, '', '', '', 'data/attached/images/201610/thumb_img/6_thumb_G_1477356307327.jpg', 'data/attached/images/201610/goods_img/6_G_1477356307984.jpg', 'data/attached/images/201610/source_img/6_G_1477356307228.jpg', 1, '', 1, 1, 0, 1, 1477356307, 100, 0, 1, 1, 1, 0, 0, 1477357198, 0, '', -1, -1, 0, NULL, '99.00', 3, 24, 89, 6, 20, 1),
(7, 16, 'ECS000007', '十月天使孕妇洗护套装天然洗护用品孕期护肤专用无硅油洗发套装', '+', 0, 0, '', 100, '0.000', '118.80', '0', '99.00', '0.00', 0, 0, 1, '', '', '', 'data/attached/images/201610/thumb_img/7_thumb_G_1477356690777.jpg', 'data/attached/images/201610/goods_img/7_G_1477356690659.jpg', 'data/attached/images/201610/source_img/7_G_1477356690740.jpg', 1, '', 1, 1, 0, 0, 1477356440, 100, 0, 1, 1, 1, 0, 0, 1477357196, 0, '', -1, -1, 0, NULL, '66.00', 3, 24, 66, 6, 20, 1),
(8, 17, 'ECS000008', '菲比 秒吸舒爽加大号纸尿裤XL20片 通用尿不湿', '+', 0, 0, '', 100, '0.000', '106.80', '0', '89.00', '0.00', 0, 0, 1, '', '', '', 'data/attached/images/201610/thumb_img/8_thumb_G_1477356625503.jpg', 'data/attached/images/201610/goods_img/8_G_1477356625443.jpg', 'data/attached/images/201610/source_img/8_G_1477356625111.jpg', 1, '', 1, 1, 0, 0, 1477356625, 100, 0, 1, 1, 1, 0, 0, 1477357196, 0, '', -1, -1, 0, NULL, '46.00', 2, 24, 26, 6, 21, 1),
(9, 12, 'ECS000009', '网纱打底衫女长袖t恤女士秋装上衣新款2016潮女装蕾丝衫大码小衫', '+', 0, 0, '', 100, '0.000', '238.79', '0', '199.00', '0.00', 0, 0, 1, '', '', '', 'data/attached/images/201610/thumb_img/9_thumb_G_1477356810781.jpg', 'data/attached/images/201610/goods_img/9_G_1477356810701.jpg', 'data/attached/images/201610/source_img/9_G_1477356810524.jpg', 1, '', 1, 1, 0, 1, 1477356810, 100, 0, 1, 1, 1, 0, 0, 1477357195, 0, '', -1, -1, 0, NULL, '126.00', 3, 24, 24, 6, 12, 1),
(10, 13, 'ECS000010', '2016新品韩版弹力气质款浅蓝色修身显瘦提臀小脚牛仔长裤女', '+', 0, 0, '', 100, '0.000', '118.80', '0', '99.00', '0.00', 0, 0, 1, '', '', '', 'data/attached/images/201610/thumb_img/10_thumb_G_1477356871334.jpg', 'data/attached/images/201610/goods_img/10_G_1477356871078.jpg', 'data/attached/images/201610/source_img/10_G_1477356871722.jpg', 1, '', 1, 1, 0, 0, 1477356871, 100, 0, 1, 1, 1, 0, 0, 1477357195, 0, '', -1, -1, 0, NULL, '66.00', 3, 24, 54, 6, 13, 1),
(11, 14, 'ECS000011', '2016新款秋装运动套装女大码长袖纯棉休闲服韩版时尚卫衣两件套潮', '+', 0, 0, '', 100, '0.000', '146.40', '0', '122.00', '0.00', 0, 0, 1, '', '', '', 'data/attached/images/201610/thumb_img/11_thumb_G_1477356926009.jpg', 'data/attached/images/201610/goods_img/11_G_1477356926973.jpg', 'data/attached/images/201610/source_img/11_G_1477356926961.jpg', 1, '', 1, 1, 0, 1, 1477356926, 100, 0, 1, 1, 1, 0, 0, 1477357194, 0, '', -1, -1, 0, NULL, '50.00', 4, 24, 66, 6, 14, 1),
(12, 11, 'ECS000012', '2016秋冬季新款高腰半身裙秋中长款包臀裙鱼尾裙短裙包裙子一步裙', '+', 0, 0, '', 100, '0.000', '120.00', '0', '100.00', '0.00', 0, 0, 1, '', '', '', 'data/attached/images/201610/thumb_img/12_thumb_G_1477356969906.jpg', 'data/attached/images/201610/goods_img/12_G_1477356969965.jpg', 'data/attached/images/201610/source_img/12_G_1477356969906.jpg', 1, '', 1, 1, 0, 1, 1477356969, 100, 0, 1, 1, 1, 0, 0, 1477357194, 0, '', -1, -1, 0, NULL, '66.00', 3, 24, 100, 6, 11, 1),
(13, 11, 'ECS000013', '★妃语欧洲站2016秋装欧货潮百搭复古印花半身裙a字裙短裙蓬蓬裙', '+', 1, 0, '', 100, '0.000', '199.20', '0', '166.00', '0.00', 0, 0, 1, '', '', '', 'data/attached/images/201610/thumb_img/13_thumb_G_1477357028188.jpg', 'data/attached/images/201610/goods_img/13_G_1477357028540.jpg', 'data/attached/images/201610/source_img/13_G_1477357028279.jpg', 1, '', 1, 1, 0, 1, 1477357028, 100, 0, 1, 1, 1, 0, 0, 1477357193, 0, '', -1, -1, 0, NULL, '0.00', 0, 0, 0, 0, 0, 0),
(14, 14, 'ECS000014', '初秋套装女时尚潮2016显瘦名媛韩版甜美欧美长袖休闲针织两件套裙', '+', 0, 0, '', 100, '0.000', '238.79', '0', '199.00', '0.00', 0, 0, 1, '', '', '', 'data/attached/images/201610/thumb_img/14_thumb_G_1477357131857.jpg', 'data/attached/images/201610/goods_img/14_G_1477357131888.jpg', 'data/attached/images/201610/source_img/14_G_1477357131570.jpg', 1, '', 1, 1, 0, 1, 1477357131, 100, 0, 1, 1, 1, 0, 0, 1477357192, 0, '', -1, -1, 0, NULL, '120.00', 5, 24, 66, 6, 14, 1);

--
-- 转存表中的数据 `ecs_goods_gallery`
--

INSERT INTO `ecs_goods_gallery` (`img_id`, `goods_id`, `img_url`, `img_desc`, `thumb_url`, `img_original`) VALUES
(1, 1, 'data/attached/images/201610/goods_img/1_P_1477355859511.jpg', '', 'data/attached/images/201610/thumb_img/1_thumb_P_1477355859339.jpg', 'data/attached/images/201610/source_img/1_P_1477355859242.jpg'),
(2, 2, 'data/attached/images/201610/goods_img/2_P_1477355996427.jpg', '', 'data/attached/images/201610/thumb_img/2_thumb_P_1477355996911.jpg', 'data/attached/images/201610/source_img/2_P_1477355996639.jpg'),
(3, 3, 'data/attached/images/201610/goods_img/3_P_1477356070968.jpg', '', 'data/attached/images/201610/thumb_img/3_thumb_P_1477356070625.jpg', 'data/attached/images/201610/source_img/3_P_1477356070814.jpg'),
(4, 4, 'data/attached/images/201610/goods_img/4_P_1477356137704.jpg', '', 'data/attached/images/201610/thumb_img/4_thumb_P_1477356137655.jpg', 'data/attached/images/201610/source_img/4_P_1477356137818.jpg'),
(5, 5, 'data/attached/images/201610/goods_img/5_P_1477356232841.jpg', '', 'data/attached/images/201610/thumb_img/5_thumb_P_1477356232129.jpg', 'data/attached/images/201610/source_img/5_P_1477356232775.jpg'),
(6, 6, 'data/attached/images/201610/goods_img/6_P_1477356307756.jpg', '', 'data/attached/images/201610/thumb_img/6_thumb_P_1477356307517.jpg', 'data/attached/images/201610/source_img/6_P_1477356307087.jpg'),
(7, 8, 'data/attached/images/201610/goods_img/8_P_1477356625615.jpg', '', 'data/attached/images/201610/thumb_img/8_thumb_P_1477356625247.jpg', 'data/attached/images/201610/source_img/8_P_1477356625652.jpg'),
(8, 7, 'data/attached/images/201610/goods_img/7_P_1477356690013.jpg', '', 'data/attached/images/201610/thumb_img/7_thumb_P_1477356690347.jpg', 'data/attached/images/201610/source_img/7_P_1477356690717.jpg'),
(9, 9, 'data/attached/images/201610/goods_img/9_P_1477356810237.jpg', '', 'data/attached/images/201610/thumb_img/9_thumb_P_1477356810058.jpg', 'data/attached/images/201610/source_img/9_P_1477356810243.jpg'),
(10, 10, 'data/attached/images/201610/goods_img/10_P_1477356871649.jpg', '', 'data/attached/images/201610/thumb_img/10_thumb_P_1477356871066.jpg', 'data/attached/images/201610/source_img/10_P_1477356871259.jpg'),
(11, 11, 'data/attached/images/201610/goods_img/11_P_1477356926627.jpg', '', 'data/attached/images/201610/thumb_img/11_thumb_P_1477356926417.jpg', 'data/attached/images/201610/source_img/11_P_1477356926357.jpg'),
(12, 12, 'data/attached/images/201610/goods_img/12_P_1477356969494.jpg', '', 'data/attached/images/201610/thumb_img/12_thumb_P_1477356969081.jpg', 'data/attached/images/201610/source_img/12_P_1477356969591.jpg'),
(13, 13, 'data/attached/images/201610/goods_img/13_P_1477357028836.jpg', '', 'data/attached/images/201610/thumb_img/13_thumb_P_1477357028775.jpg', 'data/attached/images/201610/source_img/13_P_1477357028252.jpg'),
(14, 14, 'data/attached/images/201610/goods_img/14_P_1477357131143.jpg', '', 'data/attached/images/201610/thumb_img/14_thumb_P_1477357131931.jpg', 'data/attached/images/201610/source_img/14_P_1477357131470.jpg');
