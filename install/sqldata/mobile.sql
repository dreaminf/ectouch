--
-- 后台权限控制
--

INSERT INTO `ecs_admin_action` VALUES ('200', '0', 'ext_fenxiao', '');
INSERT INTO `ecs_admin_action` VALUES ('201', '0', 'ext_wechat', '');
INSERT INTO `ecs_admin_action` VALUES ('138', '200', 'drp_config', '');
INSERT INTO `ecs_admin_action` VALUES ('139', '200', 'drp_audit', '');
INSERT INTO `ecs_admin_action` VALUES ('140', '200', 'drp_users', '');
INSERT INTO `ecs_admin_action` VALUES ('141', '200', 'drp_yongjin', '');
INSERT INTO `ecs_admin_action` VALUES ('142', '200', 'drp_order_list', '');
INSERT INTO `ecs_admin_action` VALUES ('143', '200', 'drp_affiliate', '');
INSERT INTO `ecs_admin_action` VALUES ('144', '200', 'drp_affiliate_ck', '');
INSERT INTO `ecs_admin_action` VALUES ('145', '200', 'drp_ranking', '');
INSERT INTO `ecs_admin_action` VALUES ('146', '200', 'drp_log', '');
INSERT INTO `ecs_admin_action` VALUES ('147', '201', 'wechat_config', '');
INSERT INTO `ecs_admin_action` VALUES ('148', '201', 'wechat_masssend', '');
INSERT INTO `ecs_admin_action` VALUES ('149', '201', 'wechat_autoreply', '');
INSERT INTO `ecs_admin_action` VALUES ('150', '201', 'wechat_selfmenu', '');
INSERT INTO `ecs_admin_action` VALUES ('151', '201', 'wechat_tmplmsg', '');
INSERT INTO `ecs_admin_action` VALUES ('152', '201', 'wechat_contactmanage', '');
INSERT INTO `ecs_admin_action` VALUES ('153', '201', 'wechat_appmsg', '');
INSERT INTO `ecs_admin_action` VALUES ('154', '201', 'wechat_qrcode', '');
INSERT INTO `ecs_admin_action` VALUES ('155', '201', 'wechat_extends', '');
INSERT INTO `ecs_admin_action` VALUES ('156', '201', 'wechat_remind', '');
INSERT INTO `ecs_admin_action` VALUES ('157', '201', 'wechat_customer', '');

--
-- 表的结构 `ecs_touch_activity`
--

CREATE TABLE IF NOT EXISTS `ecs_touch_activity` (
  `act_id` int(10) NOT NULL,
  `act_banner` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 表的结构 `ecs_touch_topic`
--

CREATE TABLE IF NOT EXISTS `ecs_touch_topic` (
  `topic_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `intro` text NOT NULL,
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(10) NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  `template` varchar(255) NOT NULL DEFAULT '',
  `css` text NOT NULL,
  `topic_img` varchar(255) DEFAULT NULL,
  `title_pic` varchar(255) DEFAULT NULL,
  `base_style` char(6) DEFAULT NULL,
  `htmls` mediumtext,
  `keywords` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `ecs_touch_ad`
--

INSERT INTO `ecs_ad` (`position_id`, `media_type`, `ad_name`, `ad_link`, `ad_code`, `start_time`, `end_time`, `link_man`, `link_email`, `link_phone`, `click_count`, `enabled`) VALUES
(255, 0, '1', '', 'index_banner_1.png', 1396339200, 1525161600, '', '', '', 0, 1),
(255, 0, '2', '', 'index_banner_2.png', 1396339200, 1525161600, '', '', '', 0, 1),
(255, 0, '3', '', 'index_banner_3.png', 1396339200, 1525161600, '', '', '', 0, 1);

--
-- 转存表中的数据 `ecs_touch_ad_position`
--
ALTER TABLE `ecs_ad_position` MODIFY COLUMN `position_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT FIRST;
DELETE FROM `ecs_ad_position` WHERE `position_id` = 255;
INSERT INTO `ecs_ad_position` (`position_id`, `position_name`, `ad_width`, `ad_height`, `position_desc`, `position_style`) VALUES
(255, '手机端首页Banner广告位', 360, 168, '', '{foreach from=$ads item=ad}<div class="swiper-slide">{$ad}</div>{/foreach}');

--
-- 表的结构 `ecs_touch_category`
--

CREATE TABLE IF NOT EXISTS `ecs_touch_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` int(10) unsigned DEFAULT NULL COMMENT '外键',
  `cat_image` varchar(255) DEFAULT NULL COMMENT '分类ICO图标',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- 表的结构 `ecs_touch_feedback`
--

CREATE TABLE IF NOT EXISTS `ecs_touch_feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `msg_id` mediumint(8) unsigned NOT NULL,
  `msg_read` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- 表的结构 `ecs_touch_goods`
--

CREATE TABLE IF NOT EXISTS `ecs_touch_goods` (
  `goods_id` int(10) unsigned default NULL COMMENT '外键',
  `sales_volume` int(10) unsigned default NULL COMMENT '销量统计',
  UNIQUE KEY `goods_id` (`goods_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 表的结构 `ecs_touch_goods_activity`
--

CREATE TABLE IF NOT EXISTS `ecs_touch_goods_activity` (
  `act_id` int(10) DEFAULT NULL,
  `act_banner` varchar(255) DEFAULT NULL,
  `sales_count` int(10) DEFAULT NULL,
  `click_num` int(10) NOT NULL DEFAULT '0',
  `cur_price` decimal(10,2) NOT NULL,
  UNIQUE KEY `act_id` (`act_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 表的结构 `ecs_touch_nav`
--

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `ecs_touch_nav`
--

INSERT INTO `ecs_touch_nav` (`id`, `ctype`, `cid`, `name`, `ifshow`, `vieworder`, `opennew`, `url`, `pic`, `type`) VALUES
(1, '', 0, '全部分类', 1, 0, 0, 'index.php?c=category&amp;a=top_all', 'themes/default/images/nav/nav_0.png', 'middle'),
(2, '', 0, '我的订单', 1, 0, 0, 'index.php?c=user&amp;a=order_list', 'themes/default/images/nav/nav_1.png', 'middle'),
(3, '', 0, '最新团购', 1, 0, 0, 'index.php?c=groupbuy', 'themes/default/images/nav/nav_4.png', 'middle'),
(4, '', 0, '促销活动', 1, 0, 0, 'index.php?c=activity', 'themes/default/images/nav/nav_3.png', 'middle'),
(5, '', 0, '热门搜索', 1, 0, 0, 'javascript:openSearch();', 'themes/default/images/nav/nav_6.png', 'middle'),
(6, '', 0, '品牌街', 1, 0, 0, 'index.php?c=brand', 'themes/default/images/nav/nav_2.png', 'middle'),
(7, '', 0, '个人中心', 1, 0, 0, 'index.php?c=user', 'themes/default/images/nav/nav_5.png', 'middle'),
(8, '', 0, '购物车', 1, 0, 0, 'index.php?c=flow&amp;a=cart', 'themes/default/images/nav/nav_7.png', 'middle');

-- ----------------------------
-- 增加短信接口配置项
-- ----------------------------
INSERT INTO `ecs_shop_config` (parent_id, code, type, store_range, store_dir, value, sort_order)VALUES (8, 'sms_ecmoban_user', 'text', '', '', '', 0);
INSERT INTO `ecs_shop_config` (parent_id, code, type, store_range, store_dir, value, sort_order)VALUES (8, 'sms_ecmoban_password', 'password', '', '', '', 0);
INSERT INTO `ecs_shop_config` (parent_id, code, type, store_range, store_dir, value, sort_order)VALUES (8, 'sms_signin', 'select', '1,0', '', '0', 1);

--
-- 表的结构 `ecs_touch_user`
--

CREATE TABLE IF NOT EXISTS `ecs_touch_auth` (
  `id` tinyint(2) NOT NULL AUTO_INCREMENT,
  `auth_config` text NOT NULL,
  `from` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='登录插件';

--
-- 表的结构 `ecs_touch_user_info`
--

CREATE TABLE IF NOT EXISTS `ecs_touch_user_info` (
  `user_id` int(10) NOT NULL,
  `aite_id` varchar(200) NOT NULL COMMENT '标识'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户信息';

-- /*DRP_START*/

CREATE TABLE IF NOT EXISTS `ecs_drp_config` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `centent` text COMMENT '多选时的选项',
  `keyword` varchar(20) DEFAULT NULL COMMENT '区分文章的key',
  `name` varchar(50) DEFAULT NULL COMMENT '显示字段名',
  `remarks`  text COMMENT '备注',
  `type` varchar(20) DEFAULT 'text' COMMENT '数据类型',
  `value` TEXT COMMENT '默认值',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- 表的结构 `ecs_drp_log`
--

CREATE TABLE IF NOT EXISTS `ecs_drp_log` (
  `log_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL,
  `user_money` decimal(10,2) NOT NULL,
  `pay_points` mediumint(9) NOT NULL,
  `change_time` int(10) unsigned NOT NULL,
  `change_desc` varchar(255) NOT NULL,
  `change_type` tinyint(3) unsigned NOT NULL,
  `bank_info`  text COMMENT '提现银行卡信息',
  `order_id` int(10) unsigned NOT NULL,
  `status` int(1) unsigned NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- 表的结构 `ecs_drp_profit`
--

CREATE TABLE IF NOT EXISTS `ecs_drp_profit` (
  `profit_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '分类利润id',
  `cate_id` int(10) DEFAULT NULL COMMENT '商品分类',
  `profit1` float(20,2) DEFAULT '0.00' COMMENT '分销利润1级',
  `profit2` float(20,2) DEFAULT '0.00' COMMENT '分销利润2级',
  `profit3` float(20,2) DEFAULT '0.00' COMMENT '分销利润3级',
  PRIMARY KEY (`profit_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- 表的结构 `ecs_drp_shop`
--

CREATE TABLE IF NOT EXISTS `ecs_drp_shop` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '店铺id',
  `shop_name` varchar(20) DEFAULT NULL COMMENT '微店名称',
  `real_name` varchar(20) DEFAULT NULL COMMENT '真实姓名',
  `shop_mobile` varchar(20) DEFAULT NULL COMMENT '手机号',
  `shop_qq` varchar(20) DEFAULT NULL COMMENT 'qq号',
  `shop_img` text COMMENT '店铺头像',
  `user_id` int(10) NOT NULL DEFAULT '0',
  `cat_id` text COMMENT '分销分类id',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `audit` int(1) NOT NULL DEFAULT '0' COMMENT '店铺是否审核',
  `open` int(1) NOT NULL DEFAULT '0' COMMENT '店铺是否开启',
  `bank` int(10) NOT NULL DEFAULT '0' COMMENT '默认银行卡',
  PRIMARY KEY (`id`,`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- ----------------------------
-- Table structure for `ecs_drp_bank`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `ecs_drp_bank` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `bank_region` VARCHAR( 255 ) DEFAULT NULL COMMENT '所在地',
  `bank_name` varchar(50) DEFAULT NULL COMMENT '银行名称',
  `bank_user_name` varchar(50) DEFAULT NULL COMMENT '开户名称',
  `bank_card` varchar(50) DEFAULT NULL COMMENT '银行卡号',
  `user_id` int(10) DEFAULT NULL COMMENT '用户id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ecs_drp_visiter`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `ecs_drp_visiter` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `drp_id` int(10) NOT NULL,
  `visit_time` int(12) NOT NULL COMMENT '访问时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ecs_drp_goods`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `ecs_drp_goods` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `goods_id` int(10) NOT NULL DEFAULT '0',
  `touch_sale` decimal(10,2) NOT NULL DEFAULT '0.00',
  `touch_fencheng` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ecs_drp_order_goods`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `ecs_drp_order_goods` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `goods_id` int(10) DEFAULT NULL,
  `touch_sale` decimal(10,2) DEFAULT '0.00',
  `touch_fencheng` decimal(10,2) DEFAULT '0.00',
  `order_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ecs_drp_order_info`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `ecs_drp_order_info` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `drp_id` int(10) NOT NULL DEFAULT '0',
  `shop_separate` int(1) unsigned NOT NULL DEFAULT '0',
  `order_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ecs_drp_apply`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `ecs_drp_apply` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `apply` int(1) DEFAULT '1',
  `user_id` int(10) DEFAULT NULL,
  `time` int(12) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
)  ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ecs_drp_config
-- ----------------------------

INSERT INTO `ecs_drp_config` VALUES ('1', '', 'apply', '温馨提示', '申请分销商时，提示用户需要注意的信息', 'textarea', '温馨提示1');
INSERT INTO `ecs_drp_config` VALUES ('2', '', 'novice', '新手必读', '分销商申请成功后，用户要注意的事项', 'textarea', '新手必读1');
INSERT INTO `ecs_drp_config` VALUES ('3', '', 'fxts', '间隔', '下单并付款之后经过间隔天数才可以对订单分成', 'text', '1');
INSERT INTO `ecs_drp_config` VALUES ('4', '', 'txxz', '提现标准', '申请提现时，少于该值将无法提现', 'text', '10');
INSERT INTO `ecs_drp_config` VALUES ('5', 'open,close', 'msg_open', '消息推送', '是否开启消息推送', 'radio', 'open');
INSERT INTO `ecs_drp_config` VALUES ('6', 'open,close', 'examine', '购买分销商', '是否开启购买成为分销商', 'radio', 'open');
INSERT INTO `ecs_drp_config` VALUES ('7', '', 'money', '购买金额', '购买分销商金额', 'text', '1');
INSERT INTO `ecs_drp_config` VALUES ('8', 'open,close', 'audit', '分销商审核', '是否对新申请的分销商进行审核', 'radio', 'open');
ALTER TABLE `ecs_users` ADD COLUMN `apply_sale` int(1) unsigned NOT NULL DEFAULT '0';

-- /*DRP_END*/

ALTER TABLE `ecs_brand` ADD COLUMN `brand_banner` varchar(80)  DEFAULT '';
ALTER TABLE `ecs_goods_activity` ADD COLUMN `touch_img` VARCHAR (50)  DEFAULT '';
ALTER TABLE `ecs_favourable_activity` ADD COLUMN `touch_img` VARCHAR (50)  DEFAULT '';
