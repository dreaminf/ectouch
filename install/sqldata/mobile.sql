--
-- 后台权限控制
--

-- /*DRP_START*/
INSERT INTO `ecs_admin_action` VALUES ('200', '0', 'ext_fenxiao', '');
-- /*DRP_END*/
INSERT INTO `ecs_admin_action` VALUES ('201', '0', 'ext_wechat', '');
-- /*DRP_START*/
INSERT INTO `ecs_admin_action` VALUES ('138', '200', 'drp_config', '');
INSERT INTO `ecs_admin_action` VALUES ('139', '200', 'drp_audit', '');
INSERT INTO `ecs_admin_action` VALUES ('140', '200', 'drp_users', '');
INSERT INTO `ecs_admin_action` VALUES ('141', '200', 'drp_yongjin', '');
INSERT INTO `ecs_admin_action` VALUES ('142', '200', 'drp_order_list', '');
INSERT INTO `ecs_admin_action` VALUES ('143', '200', 'drp_affiliate', '');
INSERT INTO `ecs_admin_action` VALUES ('144', '200', 'drp_affiliate_ck', '');
INSERT INTO `ecs_admin_action` VALUES ('145', '200', 'drp_ranking', '');
INSERT INTO `ecs_admin_action` VALUES ('146', '200', 'drp_log', '');
-- /*DRP_END*/
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
INSERT INTO `ecs_admin_action` (`action_id`,`parent_id`, `action_code`, `relevance`) VALUES
('158', '6', 'service_type', ''),
('159', '6', 'back_cause_list', ''),
('160', '6', 'aftermarket_list', ''),
('161', '6', 'add_return_cause', '');
INSERT INTO `ecs_admin_action` (`action_id`, `parent_id`, `action_code`, `relevance`) VALUES
(162, 0, 'menu_tools', ''),
(163, 162, 'navigator', ''),
(164, 162, 'authorization', ''),
(165, 162, 'mail_settings', ''),
(166, 162, 'view_sendlist', ''),
(167, 162, 'captcha_manage', ''),
(168, 162, 'upgrade', '');
INSERT INTO `ecs_admin_action` (`action_id`, `parent_id`, `action_code`, `relevance`) VALUES
(172, 0, 'team', ''),
(173, 172, 'team_index', ''),
(174, 172, 'team_add', ''),
(175, 172, 'team_order', ''),
(176, 0, 'crowd_index', ''),
(177, 176, 'crowd', ''),
(178, 176, 'crowd_category_list', ''),
(179, 176, 'crowd_order_list', ''),
(180, 176, 'crowd_message_list', ''),
(181, 176, 'crowd_article_list', '');
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
  `intro` text NOT NULL DEFAULT '',
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(10) NOT NULL DEFAULT '0',
  `data` text NOT NULL DEFAULT '',
  `template` varchar(255) NOT NULL DEFAULT '',
  `css` text NOT NULL DEFAULT '',
  `topic_img` varchar(255) DEFAULT NULL,
  `title_pic` varchar(255) DEFAULT NULL,
  `base_style` char(6) DEFAULT NULL,
  `htmls` mediumtext,
  `keywords` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`topic_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


--
-- 转存表中的数据 `ecs_touch_ad_position`
--
ALTER TABLE `ecs_ad_position` MODIFY COLUMN `position_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT FIRST;

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
  `msg_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `msg_read` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- 表的结构 `ecs_touch_goods`
--

CREATE TABLE IF NOT EXISTS `ecs_touch_goods` (
  `goods_id` int(10) unsigned default '0' COMMENT '外键',
  `sales_volume` int(10) unsigned default '0' COMMENT '销量统计',
  UNIQUE KEY `goods_id` (`goods_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 表的结构 `ecs_touch_goods_activity`
--

CREATE TABLE IF NOT EXISTS `ecs_touch_goods_activity` (
  `act_id` int(10) DEFAULT '0',
  `act_banner` varchar(255) DEFAULT NULL,
  `sales_count` int(10) DEFAULT '0',
  `click_num` int(10) NOT NULL DEFAULT '0',
  `cur_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  UNIQUE KEY `act_id` (`act_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 表的结构 `ecs_touch_nav`
--

CREATE TABLE IF NOT EXISTS `ecs_touch_nav` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `ctype` varchar(10) DEFAULT NULL,
  `cid` smallint(5) unsigned DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `ifshow` tinyint(1) NOT NULL DEFAULT '0',
  `vieworder` tinyint(1) NOT NULL DEFAULT '0',
  `opennew` tinyint(1) NOT NULL DEFAULT '0',
  `url` varchar(255) NOT NULL DEFAULT '',
  `pic` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `ifshow` (`ifshow`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `ecs_touch_nav`
--

INSERT INTO `ecs_touch_nav` (`id`, `ctype`, `cid`, `name`, `ifshow`, `vieworder`, `opennew`, `url`, `pic`, `type`) VALUES
(1, '', 0, '搜索', 1, 0, 0, 'javascript:openSearch();', 'themes/default/images/nav/nav-01.png', 'middle'),
(2, '', 0, '活动', 1, 0, 0, 'index.php?c=activity', 'themes/default/images/nav/nav-02.png', 'middle'),
(3, '', 0, '团购', 1, 0, 0, 'index.php?c=groupbuy', 'themes/default/images/nav/nav-03.png', 'middle'),
(4, '', 0, '品牌街', 1, 0, 0, 'index.php?c=brand', 'themes/default/images/nav/nav-04.png', 'middle'),
(5, '', 0, '我的微筹', 1, 0, 0, 'index.php?c=crowdfunding', 'themes/default/images/nav/nav_8.png', 'middle');
-- ----------------------------
-- 增加短信接口配置项
-- ----------------------------
INSERT INTO `ecs_shop_config` (parent_id, code, type, store_range, store_dir, value, sort_order)VALUES (8, 'sms_ecmoban_user', 'text', '', '', '', 0);
INSERT INTO `ecs_shop_config` (parent_id, code, type, store_range, store_dir, value, sort_order)VALUES (8, 'sms_ecmoban_password', 'password', '', '', '', 0);
INSERT INTO `ecs_shop_config` (parent_id, code, type, store_range, store_dir, value, sort_order)VALUES (8, 'sms_signin', 'select', '1,0', '', '0', 1);

-- ----------------------------
-- 首页下单提示轮播控制
-- ----------------------------
INSERT INTO `ecs_shop_config` (parent_id, code, type, store_range, store_dir, value, sort_order) VALUES ('2', 'virtual_order', 'select', '1,0', '', '1', '1');

-- ----------------------------
-- 签到送积分
-- ----------------------------

INSERT INTO `ecs_shop_config` (parent_id, code, type, store_range, store_dir, value, sort_order)VALUES (2, 'sign_points', 'text', '', '', '10', 1);


--
-- 表的结构 `ecs_touch_user`
--

CREATE TABLE IF NOT EXISTS `ecs_touch_auth` (
  `id` tinyint(2) NOT NULL AUTO_INCREMENT,
  `auth_config` text NOT NULL DEFAULT '',
  `from` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='登录插件';

--
-- 表的结构 `ecs_touch_user_info`
--

CREATE TABLE IF NOT EXISTS `ecs_touch_user_info` (
  `user_id` int(10) NOT NULL DEFAULT '0',
  `aite_id` varchar(200) NOT NULL DEFAULT '' COMMENT '标识'
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
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_money` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pay_points` mediumint(9) NOT NULL DEFAULT '0',
  `change_time` int(10) unsigned NOT NULL DEFAULT '0',
  `change_desc` varchar(255) NOT NULL DEFAULT '',
  `change_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `bank_info`  text COMMENT '提现银行卡信息',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `status` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- 表的结构 `ecs_drp_profit`
--

CREATE TABLE IF NOT EXISTS `ecs_drp_profit` (
  `profit_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '分类利润id',
  `cate_id` int(10) DEFAULT '0' COMMENT '商品分类',
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
  `user_id` int(10) DEFAULT '0' COMMENT '用户id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ecs_drp_visiter`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `ecs_drp_visiter` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '0',
  `drp_id` int(10) NOT NULL DEFAULT '0',
  `visit_time` int(12) NOT NULL DEFAULT '0' COMMENT '访问时间',
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
  `goods_id` int(10) DEFAULT '0',
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
  `user_id` int(10) DEFAULT '0',
  `time` int(12) DEFAULT '0',
  `amount` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
)  ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ecs_drp_config
-- ----------------------------

INSERT INTO `ecs_drp_config` VALUES ('1', '', 'apply', '温馨提示', '申请分销商时，提示用户需要注意的信息', 'textarea', '亲，您的佣金由三部分组成：<br>1.本店所销售的商品，我所获得的佣金（即本店销售佣金）<br>2.下级分店所销售的商品，我所获得的佣金（即一级分店佣金）<br>3.下级分店发展的分店所销售的商品，我所获得的佣金（即二级分店佣金）');
INSERT INTO `ecs_drp_config` VALUES ('2', '', 'novice', '新手必读', '分销商申请成功后，用户要注意的事项', 'textarea', '1、开微店收入来源之一：您已成功注册微店，已经取得整个商城的商品销售权，只要有人在您的微店购物，即可获得“本店销售佣金”。<br>2、开微店收入来源之二：邀请您的朋友注册微店，他就会成为你的分销商，他店内销售的商品，您即可获得“一级分店佣金”。<br>3、开微店收入来源之三：您的分销商邀请他的朋友注册微店，他店内销售的商品，您即可获得“二级分店佣金”。');
INSERT INTO `ecs_drp_config` VALUES ('3', '', 'fxts', '间隔', '下单并付款之后经过间隔天数才可以对订单分成', 'text', '1');
INSERT INTO `ecs_drp_config` VALUES ('4', '', 'txxz', '提现标准', '申请提现时，少于该值将无法提现', 'text', '10');
INSERT INTO `ecs_drp_config` VALUES ('5', 'open,close', 'msg_open', '消息推送', '是否开启消息推送', 'radio', 'open');
INSERT INTO `ecs_drp_config` VALUES ('6', 'open,close', 'examine', '购买分销商', '是否开启购买成为分销商', 'radio', 'open');
INSERT INTO `ecs_drp_config` VALUES ('7', '', 'money', '购买金额', '购买分销商金额', 'text', '1');
INSERT INTO `ecs_drp_config` VALUES ('8', 'open,close', 'audit', '分销商审核', '是否对新申请的分销商进行审核', 'radio', 'open');
INSERT INTO `ecs_drp_config` VALUES ('9', 'open,close', 'buy_money', '累计消费金额', '是否开启购物累计消费金额满足设置才能开店', 'radio', 'close');
INSERT INTO `ecs_drp_config` VALUES ('10', '', 'buy', '设置累计消费金额', '设置会员累计消费金额', 'text', '0');
INSERT INTO `ecs_drp_config` VALUES ('11', '', 'custom_distributor', '自定义“分销商”名称', '替换设定的分销商名称', 'text', '代言人');
INSERT INTO `ecs_drp_config` VALUES ('12', '', 'custom_distribution', '自定义“分销”名称', '替换设定的分销名称', 'text', '代言');
ALTER TABLE `ecs_users` ADD COLUMN `apply_sale` int(1) unsigned NOT NULL DEFAULT '0';

-- /*DRP_END*/

--
-- 表的结构 `签到记录（ecs_user_sign）`
--

CREATE TABLE IF NOT EXISTS `ecs_user_sign` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) NOT NULL,
  `years` int(10) NOT NULL,
  `month` int(11) NOT NULL,
  `day` int(11) NOT NULL,
  `last_sign_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- 表的结构 `开团记录信息表（ecs_team_log）`
--

CREATE TABLE IF NOT EXISTS `ecs_team_log` (
  `team_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '开团记录id',
  `goods_id` mediumint(8) NOT NULL  COMMENT '拼团商品id',
  `start_time` int(10) COMMENT '开团时间',
  `status` tinyint(10) NOT NULL DEFAULT '0' COMMENT '拼团状态（1成功，2失败）',
  `is_show` int(10) NOT NULL DEFAULT '1' COMMENT '是否显示',
  PRIMARY KEY (`team_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `频道表（ecs_team_category）`
--

CREATE TABLE IF NOT EXISTS `ecs_team_category` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '频道id',
  `name` varchar(255) DEFAULT NULL COMMENT '频道名称',
  `parent_id` int(10) NOT NULL DEFAULT '0' COMMENT '父级id',
  `content` varchar(120) DEFAULT NULL COMMENT '频道描述',
  `tc_img` varchar(255) DEFAULT NULL COMMENT '频道图标',
  `sort_order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(10) NOT NULL DEFAULT '1' COMMENT '显示0否 1显示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

--
-- 转存表中的数据 `ecs_team_category 频道列表`
--

INSERT INTO `ecs_team_category` (`id`, `name`, `parent_id`, `content`, `tc_img`, `sort_order`, `status`) VALUES
(1, '生鲜', 0, '', '', 0, 1),
(2, '服装', 0, '', '', 0, 1),
(3, '美妆', 0, NULL, '', 0, 1),
(4, '母婴', 0, NULL, '', 0, 1),
(5, '数码', 0, NULL, '', 0, 1),
(6, '电器', 0, NULL, '', 0, 1),
(7, '水果', 1, '', 'images/201610/1477003670262146374.jpg', 0, 1),
(8, '海鲜', 1, '', 'images/201610/1477003723558440986.jpg', 0, 1),
(9, '蔬菜', 1, '', 'images/201610/1477003737543093730.jpg', 0, 1),
(10, '肉类', 1, '', 'images/201610/1477003765554186648.jpg', 0, 1),
(11, '半身裙', 2, '', 'images/201610/1476238976644478183.jpg', 0, 1),
(12, '小衫', 2, '', 'images/201610/1476238818094280453.jpg', 0, 1),
(13, '裤子', 2, '', 'images/201610/1476238847669867996.jpg', 0, 1),
(14, '套装', 2, '', 'images/201610/1476238872975750020.jpg', 0, 1),
(15, '天然面膜', 3, '', 'images/201610/1477003847639238159.jpg', 0, 1),
(16, '唇彩口红', 3, '', 'images/201610/1477003868289504448.jpg', 0, 1),
(17, '保湿面乳', 3, '', 'images/201610/1477003883140316778.jpg', 0, 1),
(18, '时尚香水', 3, '', 'images/201610/1477003989942279800.jpg', 0, 1),
(19, '婴儿起居', 4, '', 'images/201610/1475967205858355624.jpg', 1, 1),
(20, '妈咪护理', 4, '', 'images/201610/1475967168002105903.jpg', 2, 1),
(21, '婴儿洗护', 4, '', 'images/201610/1475967135212140877.jpg', 3, 1),
(22, '智力开发', 4, '', 'images/201610/1475967092323697897.jpg', 4, 1),
(23, '数码相机', 5, '', 'images/201610/1475967219850127489.jpg', 1, 1),
(24, '电脑配件', 5, '', 'images/201610/1475967180221500573.jpg', 2, 1),
(25, '智能设备', 5, '', 'images/201610/1475967146915398463.jpg', 3, 1),
(26, '智能配件', 5, '', 'images/201610/1475967109605446878.jpg', 4, 1),
(27, '厨房电器', 6, '', 'images/201610/1475967232110271481.jpg', 1, 1),
(28, '生活电器', 6, '', 'images/201610/1475967192886890302.jpg', 2, 1),
(29, '个人护理', 6, '', 'images/201610/1475967156765457477.jpg', 3, 1),
(30, '影音电器', 6, '', 'images/201610/1475967122572998827.jpg', 4, 1);


ALTER TABLE `ecs_brand` ADD COLUMN `brand_banner` varchar(80)  DEFAULT '';
ALTER TABLE `ecs_goods_activity` ADD COLUMN `touch_img` VARCHAR (50)  DEFAULT '';
ALTER TABLE `ecs_favourable_activity` ADD COLUMN `touch_img` VARCHAR (50)  DEFAULT '';

--
-- 表的结构 `ecs_crowd_article`
--

CREATE TABLE IF NOT EXISTS `ecs_crowd_article` (
  `article_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'id号',
  `title` varchar(120) NOT NULL COMMENT '标题',
  `description` text COMMENT '评论内容',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `sort_order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_open` tinyint(1) NOT NULL DEFAULT '1' COMMENT '显示 1是  0否',
  PRIMARY KEY (`article_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `ecs_crowd_article`
--

INSERT INTO `ecs_crowd_article` (`article_id`, `title`, `description`, `add_time`, `sort_order`, `is_open`) VALUES
(1, '什么是众筹？', '微筹属于回报众筹，适用于对投资人产生实物或服务回报的众筹，发起人可以设置多个回报方案，每个回报还可以设置购买金额。', 1472448401, 0, 1),
(2, '怎么判定项目成功或失败？', '在筹款期限内，如果筹得资金大于项目目标金额，则项目成功。否则，项目失败。', 1472118300, 0, 1),
(3, '如果项目失败筹资怎么处理？', '项目失败，会将各支持者投入的资金分别返给各支持者。\n\n特别地，在项目筹款周期内享受了回报的支持者不享受退款。', 1472118300, 0, 1),
(4, '目标金额达到，是否可以继续支持？', '项目在目标时间内，达到目标金额，用户可以继续支持，直到项目截止时间点', 1473156244, 0, 1);

--
-- 表的结构 `ecs_crowd_category`
--

CREATE TABLE IF NOT EXISTS `ecs_crowd_category` (
  `cat_id` mediumint(5) NOT NULL AUTO_INCREMENT COMMENT '众筹分类id',
  `cat_name` varchar(90) DEFAULT NULL COMMENT '分类名称',
  `cat_desc` varchar(255) DEFAULT NULL COMMENT '分类描述',
  `parent_id` mediumint(5) NOT NULL DEFAULT '0' COMMENT '父分类id',
  `sort_order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_show` int(1) NOT NULL DEFAULT '0' COMMENT '显示 0 否 1显示',
  PRIMARY KEY (`cat_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `ecs_crowd_category`
--

INSERT INTO `ecs_crowd_category` (`cat_id`, `cat_name`, `cat_desc`, `parent_id`, `sort_order`, `is_show`) VALUES
(5, '生鲜水果', '', 0, 2, 1),
(4, '智能产品', '', 0, 1, 1),
(6, '运动器械', '', 0, 3, 1),
(7, '灯光音箱', '', 0, 4, 1);

--
-- 表的结构 `ecs_crowd_comment`
--

CREATE TABLE IF NOT EXISTS `ecs_crowd_comment` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '评论id号',
  `parent_id` mediumint(8) NOT NULL DEFAULT '0' COMMENT '父级id',
  `user_id` mediumint(8) NOT NULL COMMENT '会员id',
  `user_name` varchar(120) NOT NULL COMMENT '会员名称',
  `goods_id` mediumint(8) NOT NULL COMMENT '商品id',
  `content` text COMMENT '评论内容',
  `add_time` int(10) DEFAULT NULL COMMENT '评论时间',
  `order_id` mediumint(8) DEFAULT NULL COMMENT '订单id',
  `reply` text COMMENT '回复内容',
  `reply_time` int(10) DEFAULT NULL COMMENT '回复时间',
  `rank` tinyint(1) NOT NULL DEFAULT '0' COMMENT '评论等级',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '显示 1是  0否',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- 表的结构 `ecs_crowd_goods`
--

CREATE TABLE IF NOT EXISTS `ecs_crowd_goods` (
  `goods_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '商品id',
  `cat_id` smallint(5) DEFAULT NULL COMMENT '众筹分类id',
  `admin_user_id` smallint(5) DEFAULT NULL COMMENT '发起人id',
  `goods_name` varchar(120) DEFAULT NULL COMMENT '项目名称',
  `goods_img` varchar(255) DEFAULT NULL COMMENT '项目图片',
  `sum_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '众筹金额',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '已筹金额',
  `buy_num` int(10) NOT NULL DEFAULT '0' COMMENT '累计销售数量',
  `start_time` int(10) DEFAULT NULL COMMENT '上架时间',
  `update_time` int(10) DEFAULT NULL COMMENT '修改时间',
  `shiping_time` varchar(255) DEFAULT NULL COMMENT '发货时间',
  `end_time` int(10) DEFAULT NULL COMMENT '结束时间',
  `goods_desc` text COMMENT '项目详情',
  `gallery_img` varchar(255) DEFAULT NULL COMMENT '项目相册',
  `recommend` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品推荐',
  `is_verify` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审核状态，0未审核 1正在审核 2已审核 ',
  `sort_order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(10) NOT NULL DEFAULT '0' COMMENT '状态 0进行中 1成功 2失败',
  PRIMARY KEY (`goods_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `ecs_crowd_goods`
--

INSERT INTO `ecs_crowd_goods` (`goods_id`, `cat_id`, `admin_user_id`, `goods_name`, `goods_img`, `sum_price`, `total_price`, `buy_num`, `start_time`, `update_time`, `shiping_time`, `end_time`, `goods_desc`, `gallery_img`, `recommend`, `is_verify`, `sort_order`, `status`) VALUES
(1, 5, NULL, '武台黄桃---黄金果肉水果，分享不一样的味道', 'images/201610/1476665689111641610.jpg', '10000.00', '0.00', 0, 1476608040, NULL, '筹款成功后3天发货', 1479372840, '', 'images/201610/1476665689248999238.jpg', 1, 1, 10, 0),
(2, 6, NULL, '小乔智能跑步机——高颜值时尚范 年轻人专属跑步机', 'images/201610/1476666069411926174.jpg', '20000.00', '0.00', 0, 1476608400, NULL, '筹款成功后5天发货', 1479373200, '', 'images/201610/1476666069785698615.jpg', 1, 1, 10, 0),
(3, 7, NULL, '魅动阿里智能水上灯光音箱，和你一起，发现生活的乐趣', 'images/201610/1476666232914237701.jpg', '12000.00', '0.00', 0, 1476608580, NULL, '筹款成功后3天发货', 1479373380, '', 'images/201610/1476666232209009163.jpg', 1, 1, 10, 0),
(4, 4, NULL, '我的智慧家庭全能管家——莱迪管家型智能服务机器人', 'images/201610/1476666336123991023.jpg', '16000.00', '0.00', 0, 1476608700, NULL, '筹款成功后5天发货', 1479373500, '', 'images/201610/1476666336469344111.jpg', 1, 1, 10, 0);

--
-- 表的结构 `ecs_crowd_like`
--
CREATE TABLE IF NOT EXISTS `ecs_crowd_like` (
  `id` mediumint(5) NOT NULL AUTO_INCREMENT COMMENT '关注id',
  `goods_id` mediumint(8) NOT NULL COMMENT '项目id',
  `user_id` mediumint(8) NOT NULL COMMENT '会员id',
  `add_time` int(10) DEFAULT NULL COMMENT '关注时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- 表的结构 `ecs_crowd_order_action`
--

CREATE TABLE IF NOT EXISTS `ecs_crowd_order_action` (
  `action_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `action_user` varchar(30) NOT NULL DEFAULT '',
  `order_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `shipping_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `pay_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `action_place` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `action_note` varchar(255) NOT NULL DEFAULT '',
  `log_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`action_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- 表的结构 `ecs_crowd_order_info`
--

CREATE TABLE IF NOT EXISTS `ecs_crowd_order_info` (
  `order_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '订单id号',
  `order_sn` varchar(20) DEFAULT NULL COMMENT '订单号,唯一',
  `user_id` mediumint(8) NOT NULL COMMENT '会员id',
  `goods_id` mediumint(8) NOT NULL COMMENT '众筹项目id',
  `cp_id` mediumint(8) NOT NULL COMMENT '众筹方案id',
  `goods_name` varchar(120) NOT NULL COMMENT '众筹项目名称',
  `goods_number` int(10) NOT NULL DEFAULT '0' COMMENT '购买众筹方案数量',
  `goods_price` decimal(10,2) NOT NULL COMMENT '众筹方案价格',
  `order_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订单的状态',
  `shipping_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '配送情况',
  `pay_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '支付状态',
  `consignee` varchar(60) NOT NULL DEFAULT '' COMMENT '收货人的姓名',
  `country` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '国家',
  `province` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '省份',
  `city` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '城市',
  `district` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '收货人的地区',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '收货人的详细地址',
  `zipcode` varchar(60) NOT NULL DEFAULT '' COMMENT '收货人的邮编',
  `tel` varchar(60) NOT NULL DEFAULT '' COMMENT '收货人的电话',
  `mobile` varchar(60) NOT NULL DEFAULT '' COMMENT '收货人的手机',
  `email` varchar(60) NOT NULL DEFAULT '收货人的Email',
  `best_time` varchar(120) NOT NULL DEFAULT '' COMMENT '收货人的最佳送货时间',
  `sign_building` varchar(120) NOT NULL DEFAULT '' COMMENT '送货人的地址的标志性建筑',
  `postscript` varchar(255) NOT NULL DEFAULT '' COMMENT '订单附言',
  `shipping_id` text NOT NULL COMMENT '配送方式id',
  `shipping_name` text NOT NULL COMMENT '配送方式的名称',
  `pay_id` tinyint(3) NOT NULL DEFAULT '0' COMMENT '支付方式的id',
  `pay_name` varchar(120) NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `how_oos` varchar(120) NOT NULL DEFAULT '' COMMENT '缺货处理方式',
  `goods_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品的总金额',
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '配送费用',
  `insure_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '保价费用',
  `pay_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '支付费用',
  `pack_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '包装费用',
  `card_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '贺卡费用,取值card',
  `money_paid` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'money_paid',
  `surplus` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '该订单使用金额的数量',
  `integral` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用的积分的数量',
  `integral_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '使用积分金额',
  `bonus` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '使用红包金额',
  `order_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '应付款金额',
  `referer` varchar(255) NOT NULL DEFAULT '' COMMENT '订单的来源页面',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单生成时间',
  `confirm_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单确认时间',
  `pay_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单支付时间',
  `shipping_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单配送时间',
  `pack_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '包装id',
  `card_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '贺卡id',
  `bonus_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '红包id',
  `invoice_no` varchar(255) NOT NULL DEFAULT '' COMMENT '发货时填写',
  `extension_code` varchar(30) NOT NULL DEFAULT '',
  `extension_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `to_buyer` varchar(255) NOT NULL DEFAULT '' COMMENT '商家给客户的留言',
  `pay_note` varchar(255) NOT NULL DEFAULT '' COMMENT '付款备注',
  `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '自增ID',
  `tax` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '发票税额',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- 表的结构 `ecs_crowd_plan`
--

CREATE TABLE IF NOT EXISTS `ecs_crowd_plan` (
  `cp_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '方案id号',
  `name` varchar(120)  DEFAULT NULL COMMENT '方案名称',
  `goods_id` mediumint(8) NOT NULL  COMMENT '商品id',
  `shop_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '方案价格',
  `number` int(11) DEFAULT '0' COMMENT '计划销售数量',
  `backey_num` int(11) DEFAULT '0' COMMENT '售出数量',
  `cp_img` varchar(255)  DEFAULT NULL COMMENT '方案图标',
  `return_time` int(11) NOT NULL DEFAULT '0' COMMENT '预计回报时间',
  `sort_order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` int(10) NOT NULL DEFAULT '1' COMMENT '显示0否 1显示',
  PRIMARY KEY (`cp_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
--
-- 转存表中的数据 `ecs_crowd_plan`
--

INSERT INTO `ecs_crowd_plan` (`cp_id`, `name`, `goods_id`, `shop_price`, `number`, `backey_num`, `cp_img`, `return_time`, `sort_order`, `status`) VALUES
(1, '黄金果肉水果2斤', 1, '100.00', 100, 0, 'data/attached/crowd_plan/fccd013ee7d086f49d0f4e9230e1d3ca.jpg', 0, 1, 1),
(2, '小乔智能跑步机v1型', 2, '200.00', 20, 0, 'data/attached/crowd_plan/01400c1d68dfc6522af9f6ef3296f8d8.jpg', 0, 1, 1),
(3, '小乔智能跑步机v2型', 2, '300.00', 40, 0, 'data/attached/crowd_plan/01218bfcd416a13c5b96509034106849.jpg', 0, 2, 1),
(4, '魅动阿里智能水上灯光音箱', 3, '200.00', 60, 0, 'data/attached/crowd_plan/ad4ab548f1b6920689100ba6fd88e94b.jpg', 0, 1, 1),
(5, '我的智慧家庭全能管家', 4, '200.00', 80, 0, 'data/attached/crowd_plan/42198359eeba51d57b4ab8fc7d83bb7b.jpg', 0, 1, 1);

--
-- 表的结构 `ecs_crowd_trends`
--

CREATE TABLE IF NOT EXISTS `ecs_crowd_trends` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '动态id号',
  `goods_id` mediumint(8) NOT NULL  COMMENT '商品id',
  `content` varchar(120)  DEFAULT NULL COMMENT '动态内容',
  `add_time` int(10) COMMENT '添加时间',
  `sort_order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` int(10) NOT NULL DEFAULT '1' COMMENT '显示 0否1显示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `ecs_connect_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `connect_code` char(30) NOT NULL DEFAULT '' COMMENT '登录插件名sns_qq，sns_wechat',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否管理员,0是会员 ,1是管理员',
  `open_id` char(64) NOT NULL DEFAULT '' COMMENT '标识',
  `refresh_token` char(64) DEFAULT '',
  `access_token` char(64) NOT NULL DEFAULT '' COMMENT 'token',
  `profile` text COMMENT '序列化用户信息',
  `create_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `expires_in` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'token过期时间',
  `expires_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'token保存时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
--
-- 表的结构 `ecs_order_return`
--
DROP TABLE IF EXISTS `ecs_order_return`;

CREATE TABLE IF NOT EXISTS `ecs_order_return` (
  `ret_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '退换货id',
  `service_sn` varchar(20) NOT NULL COMMENT '服务订单编号',
  `goods_id` int(13) NOT NULL COMMENT '商品唯一id',
  `user_id` int(10) NOT NULL COMMENT '用户id',
  `rec_id` int(10) NOT NULL COMMENT '订单商品唯一id',
  `order_id` mediumint(8) NOT NULL COMMENT '所属订单号',
  `order_sn` varchar(20) NOT NULL,
  `service_id` int(2) NOT NULL,
  `cause_id` int(10) NOT NULL COMMENT '退换货原因',
  `add_time` varchar(120) NOT NULL COMMENT '插入时间',
  `should_return` decimal(10,2) NOT NULL COMMENT '应退金额',
  `actual_return` decimal(10,2) NOT NULL COMMENT '实退金额',
  `remark` text NOT NULL COMMENT '备注',
  `country` smallint(5) NOT NULL COMMENT '国家',
  `province` smallint(5) NOT NULL COMMENT '省份',
  `city` smallint(5) NOT NULL COMMENT '城市',
  `district` smallint(5) NOT NULL COMMENT '区',
  `addressee` varchar(30) NOT NULL COMMENT '收件人',
  `phone` varchar(20) NOT NULL COMMENT '联系电话',
  `address` varchar(100) NOT NULL COMMENT '详细地址',
  `zipcode` int(6) DEFAULT NULL COMMENT '邮编',
  `return_status` tinyint(3) NOT NULL COMMENT '退换货状态',
  `refund_status` tinyint(3) NOT NULL COMMENT '退款状态',
  `back_shipping_name` varchar(30) NOT NULL COMMENT '退回快递名称',
  `back_other_shipping` varchar(30) NOT NULL COMMENT '其他快递名称',
  `back_invoice_no` varchar(50) NOT NULL COMMENT '退回快递单号',
  `out_shipping_name` varchar(30) NOT NULL COMMENT '换出快递名称',
  `out_invoice_no` varchar(50) NOT NULL COMMENT '换出快递单号',
  `seller_id` int(11) NOT NULL,
  `is_check` tinyint(1) NOT NULL COMMENT '是否审核',
  `to_buyer` varchar(255) NOT NULL,
  PRIMARY KEY (`ret_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='商品退货表' AUTO_INCREMENT=4 ;


--
-- 表的结构 `ecs_return_action`
--
DROP TABLE IF EXISTS `ecs_return_action`;

CREATE TABLE IF NOT EXISTS `ecs_return_action` (
  `action_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ret_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `action_user` varchar(30) NOT NULL DEFAULT '',
  `return_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `refund_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_check` tinyint(2) NOT NULL COMMENT '审核是否通过',
  `action_place` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `action_note` varchar(255) NOT NULL DEFAULT '',
  `action_info` varchar(255) NOT NULL COMMENT '操作介绍',
  `log_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`action_id`),
  KEY `order_id` (`ret_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;


--
-- 表的结构 `ecs_return_cause`
--
DROP TABLE IF EXISTS `ecs_return_cause`;

CREATE TABLE IF NOT EXISTS `ecs_return_cause` (
  `cause_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `cause_name` varchar(50) NOT NULL COMMENT '退换货原因',
  `parent_id` int(11) NOT NULL COMMENT '父级id',
  `sort_order` int(10) NOT NULL COMMENT '排序',
  `is_show` tinyint(3) NOT NULL COMMENT '是否显示',
  PRIMARY KEY (`cause_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='退换货原因说明' AUTO_INCREMENT=24 ;

--
-- 转存表中的数据 `ecs_return_cause`
--
INSERT INTO `ecs_return_cause` (`cause_name`, `parent_id`, `sort_order`, `is_show`) VALUES
('颜色问题', 0, 50, 1),
('质量问题', 0, 50, 1);

--
-- 表的结构 `ecs_return_goods`
--
DROP TABLE IF EXISTS `ecs_return_goods`;

CREATE TABLE IF NOT EXISTS `ecs_return_goods` (
  `rg_id` int(10) NOT NULL AUTO_INCREMENT,
  `rec_id` mediumint(8) unsigned NOT NULL,
  `goods_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `product_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `product_sn` varchar(60) DEFAULT NULL,
  `goods_name` varchar(120) DEFAULT NULL,
  `brand_name` varchar(60) DEFAULT NULL,
  `goods_sn` varchar(60) DEFAULT NULL,
  `is_real` tinyint(1) unsigned DEFAULT '0',
  `goods_attr` text,
  `goods_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `return_type` tinyint(3) NOT NULL,
  `back_num` smallint(6) NOT NULL,
  `out_num` smallint(6) NOT NULL,
  `out_attr` varchar(100) NOT NULL,
  `refund` decimal(10,2) NOT NULL,
  PRIMARY KEY (`rg_id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;


--
-- 表的结构 `ecs_aftermarket_attachments`
--
DROP TABLE IF EXISTS `ecs_aftermarket_attachments`;

CREATE TABLE IF NOT EXISTS `ecs_aftermarket_attachments` (
  `img_id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `rec_id` mediumint(8) NOT NULL,
  `img_url` varchar(255) NOT NULL,
  `goods_id` mediumint(8) NOT NULL,
  UNIQUE KEY `img_id` (`img_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

--
-- 表的结构 `ecs_service_type`
--
DROP TABLE IF EXISTS `ecs_service_type`;

CREATE TABLE IF NOT EXISTS `ecs_service_type` (
  `service_id` int(10) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(60) NOT NULL,
  `service_desc` text NOT NULL,
  `received_days` mediumint(4) NOT NULL,
  `unreceived_days` mediumint(6) NOT NULL,
  `is_show` tinyint(1) NOT NULL,
  `sort_order` tinyint(3) NOT NULL,
  `service_type` tinyint(1) NOT NULL COMMENT '服务类型',
  PRIMARY KEY (`service_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- 转存表中的数据 `ecs_service_type`
--

INSERT INTO `ecs_service_type` (`service_id`, `service_name`, `service_desc`, `received_days`, `unreceived_days`, `is_show`, `sort_order`, `service_type`) VALUES
(1, '退货退款', '已收到货，需要退还已收到的货物1', 7, 8, 1, 9, 1),
(3, '换货', '对已收到的货物不满意，联系卖家协商换货', 7, 10, 1, 3, 3);
