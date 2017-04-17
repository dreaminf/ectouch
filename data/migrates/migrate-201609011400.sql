-- ------------------------------------------------------------
-- date:20160901
-- description:新增首页广告位
-- ------------------------------------------------------------

--
-- 转存表中的数据 `{pre}ad_position`
--

INSERT INTO `{pre}ad_position` (`position_id`, `position_name`, `ad_width`, `ad_height`, `position_desc`, `position_style`) VALUES
(265, '手机端首页推荐', 630, 168, '', '{foreach from=$ads item=ad}\r\n<div class="swiper-slide swiper-slide-active">\r\n{$ad}\r\n</div>\r\n{/foreach}'),
(266, '手机端首页微筹', 360, 168, '', '{foreach from=$ads item=ad}\r\n<div class="box-flex activity-list" >{$ad}</div> \r\n{/foreach}\r\n'),
(267, '手机端首页促销团购', 360, 168, '', '{foreach from=$ads item=ad}\r\n<div class="box-flex">{$ad}</div>\r\n{/foreach}\r\n'),
(268, '手机端首页专享闪购', 360, 168, '', '{foreach from=$ads item=ad}\r\n<div class="box-flex activity-list" >{$ad}</div>\r\n{/foreach}');

--
-- 转存表中的数据 `{pre}ad`
--


INSERT INTO `{pre}ad` (`ad_id`, `position_id`, `media_type`, `ad_name`, `ad_link`, `ad_code`, `start_time`, `end_time`, `link_man`, `link_email`, `link_phone`, `click_count`, `enabled`) VALUES
(26, 265, 0, '推荐01', '/index.php?m=default&c=crowdfunding&a=goods_info&id=1', '1472669671185573205.jpg', 1472544000, 1475222400, '', '', '', 2, 1),
(27, 265, 0, '推荐02', '/index.php?m=default&c=crowdfunding&a=goods_info&id=1', '1472669714569500751.jpg', 1472544000, 1475222400, '', '', '', 0, 1),
(28, 266, 0, '微筹01', '/index.php?c=crowdfunding', '1472670655098285533.png', 1472544000, 1475222400, '', '', '', 3, 1),
(29, 268, 0, '专享', '/index.php?c=activity', '1472673072039746907.png', 1472544000, 1475222400, '', '', '', 0, 1),
(30, 268, 0, '闪购', '', '1472673165004085601.png', 1472544000, 1475222400, '', '', '', 0, 1),
(31, 267, 0, '促销', '/index.php?c=activity', '1472673189566180665.png', 1472544000, 1475222400, '', '', '', 1, 1),
(32, 267, 0, '团购', '/index.php?c=groupbuy', '1472673216499170502.png', 1472544000, 1475222400, '', '', '', 1, 1);