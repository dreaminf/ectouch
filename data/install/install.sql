--
-- 创建表结构
--
DROP TABLE IF EXISTS `ecs_touch_nav`;
CREATE TABLE IF NOT EXISTS `ecs_touch_nav` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `ctype` varchar(10) DEFAULT NULL,
  `cid` smallint(5) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `ifshow` tinyint(1) NOT NULL,
  `vieworder` tinyint(1) NOT NULL,
  `opennew` tinyint(1) NOT NULL,
  `url` varchar(255) NOT NULL,
  `pic` varchar(255) NOT NULL,
  `type` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `ifshow` (`ifshow`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ecs_touch_auth`;
CREATE TABLE IF NOT EXISTS `ecs_touch_auth` (
  `id` tinyint(2) NOT NULL AUTO_INCREMENT,
  `auth_config` varchar(255) NOT NULL,
  `from` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ecs_touch_users`;
CREATE TABLE IF NOT EXISTS `ecs_touch_users` (
  `user_id` int(10) NOT NULL,
  `openid` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 更新表结构
--
ALTER TABLE `ecs_ad_position` MODIFY COLUMN `position_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `ecs_category` ADD COLUMN `touch_img` varchar(255) NOT NULL;
ALTER TABLE `ecs_brand` ADD COLUMN `touch_img` varchar(255) NOT NULL, ADD COLUMN `touch_content` text NOT NULL;
ALTER TABLE `ecs_goods` ADD COLUMN `touch_sales` int(10) NOT NULL, ADD COLUMN `touch_content` text NOT NULL;
ALTER TABLE `ecs_goods_activity` ADD COLUMN `touch_img` varchar(255) NOT NULL;
ALTER TABLE `ecs_article` ADD COLUMN `touch_content` text NOT NULL;
ALTER TABLE `ecs_topic` ADD COLUMN `touch_img` varchar(255) NOT NULL, ADD COLUMN `touch_intro` text NOT NULL;
ALTER TABLE `ecs_feedback` ADD COLUMN `mark_read` int(1) NOT NULL;
ALTER TABLE `ecs_order_info` ADD COLUMN `touch_order` int(1) UNSIGNED NOT NULL DEFAULT 0;

--
-- 更新数据
--
UPDATE `ecs_goods_activity` SET `touch_img`='http://d8.yihaodianimg.com/N00/M08/9A/E0/CgMBmVPPNHqAXRx1AACfU7I8J8857100.jpg';

--
-- 插入数据
--
INSERT INTO `ecs_ad_position` (`position_id`, `position_name`, `ad_width`, `ad_height`, `position_desc`, `position_style`) VALUES
(256, 'ECTouch首页广告位', 360, 168, '', '<ul>\r\n{foreach from=$ads item=ad}\r\n  <li>{$ad}</li>\r\n{/foreach}\r\n</ul>\r\n');

INSERT INTO `ecs_ad` (`position_id`, `media_type`, `ad_name`, `ad_link`, `ad_code`, `start_time`, `end_time`, `link_man`, `link_email`, `link_phone`, `click_count`, `enabled`) VALUES
(256, 0, '1', '', 'http://www.ectouch.cn/data/assets/images/ectouch_ad1.jpg', 1396339200, 1525161600, '', '', '', 0, 1),
(256, 0, '2', '', 'http://www.ectouch.cn/data/assets/images/ectouch_ad2.jpg', 1396339200, 1525161600, '', '', '', 0, 1),
(256, 0, '3', '', 'http://www.ectouch.cn/data/assets/images/ectouch_ad3.jpg', 1396339200, 1525161600, '', '', '', 0, 1);

INSERT INTO `ecs_touch_nav` (`id`, `ctype`, `cid`, `name`, `ifshow`, `vieworder`, `opennew`, `url`, `pic`, `type`) VALUES
(1, '', 0, '全部分类', 1, 0, 0, 'index.php?c=category&amp;a=top_all', 'themes/default/images/nav/nav_0.png', 'middle'),
(2, '', 0, '我的订单', 1, 0, 0, 'index.php?c=user&amp;a=order_list', 'themes/default/images/nav/nav_1.png', 'middle'),
(3, '', 0, '最新团购', 1, 0, 0, 'index.php?c=groupbuy', 'themes/default/images/nav/nav_2.png', 'middle'),
(4, '', 0, '促销活动', 1, 0, 0, 'index.php?c=activity', 'themes/default/images/nav/nav_3.png', 'middle'),
(5, '', 0, '热门搜索', 1, 0, 0, 'javascript:openSearch();', 'themes/default/images/nav/nav_4.png', 'middle'),
(6, '', 0, '品牌街', 1, 0, 0, 'index.php?c=brand', 'themes/default/images/nav/nav_5.png', 'middle'),
(7, '', 0, '个人中心', 1, 0, 0, 'index.php?c=user', 'themes/default/images/nav/nav_6.png', 'middle'),
(8, '', 0, '购物车', 1, 0, 0, 'index.php?c=flow&amp;a=cart', 'themes/default/images/nav/nav_7.png', 'middle');

DELETE FROM ecs_shop_config where code = 'sms_ecmoban_user';
DELETE FROM ecs_shop_config where code = 'sms_ecmoban_password';
DELETE FROM ecs_shop_config where code = 'sms_signin';
INSERT INTO `ecs_shop_config` (parent_id, code, type, store_range, store_dir, value, sort_order)VALUES (8, 'sms_ecmoban_user', 'text', '', '', '', 0);
INSERT INTO `ecs_shop_config` (parent_id, code, type, store_range, store_dir, value, sort_order)VALUES (8, 'sms_ecmoban_password', 'password', '', '', '', 0);
INSERT INTO `ecs_shop_config` (parent_id, code, type, store_range, store_dir, value, sort_order)VALUES (8, 'sms_signin', 'select', '1,0', '', '0', 1);

INSERT INTO `ecs_shop_config` (parent_id, code, type, store_range, store_dir, value, sort_order)VALUES (9, 'shop_url', 'text', '', '', '', 1);
INSERT INTO `ecs_shop_config` (parent_id, code, type, store_range, store_dir, value, sort_order)VALUES (9, 'touch_template', 'hidden', '', '', 'default', 1);
INSERT INTO `ecs_shop_config` (parent_id, code, type, store_range, store_dir, value, sort_order)VALUES (9, 'touch_stylename', 'hidden', '', '', '', 1);
INSERT INTO `ecs_shop_config` (parent_id, code, type, store_range, store_dir, value, sort_order)VALUES (9, 'touch_logo', 'text', '', '', './themes/{$template}/images/', 1);
