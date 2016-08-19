-- ------------------------------------------------------------
-- author:xiao rui
-- date:20160607
-- description:新增众筹模块数据表
-- ------------------------------------------------------------


--
-- 表的结构 `ecs_crowd_goods 众筹项目`
--

CREATE TABLE IF NOT EXISTS `{pre}crowd_goods` (
  `goods_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '商品id',
  `cat_id` smallint(5) DEFAULT NULL COMMENT '众筹分类id',
  `admin_user_id` smallint(5) DEFAULT NULL COMMENT '发起人id',
  `goods_name` varchar(120) DEFAULT NULL COMMENT '项目名称',
  `goods_img` varchar(255) DEFAULT NULL COMMENT '项目图片',
  `sum_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '众筹金额',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '已筹金额',
  `buy_num` int(10) NOT NULL DEFAULT '0' COMMENT '累计销售数量',
  `start_time` int(10) COMMENT '上架时间',
  `update_time` int(10) COMMENT '修改时间',
  `shiping_time` varchar(255) DEFAULT NULL COMMENT '发货时间',
  `end_time` int(10) COMMENT '结束时间',
  `goods_desc` text DEFAULT NULL COMMENT '项目详情',
  `gallery_img` varchar(255) DEFAULT NULL COMMENT '项目相册',
  `recommend` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品推荐',
  `is_verify` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审核状态，0未审核 1正在审核 2已审核 ',
  `sort_order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(10) NOT NULL DEFAULT '0' COMMENT '状态 0进行中 1成功 2失败',
  PRIMARY KEY (`goods_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;


--
-- 表的结构 `ecs_crowd_plan  众筹项目方案`
--

CREATE TABLE IF NOT EXISTS `{pre}crowd_plan` (
  `cp_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '方案id号',
  `name` varchar(120)  DEFAULT NULL COMMENT '方案名称',
  `goods_id` mediumint(8) NOT NULL  COMMENT '商品id',
  `shop_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '方案价格',
  `number` int(11) DEFAULT '0' COMMENT '计划销售数量',
  `backey_num` int(11) DEFAULT '0' COMMENT '售出数量',
  `cp_img` varchar(255)  DEFAULT NULL COMMENT '方案图标',
  `return_time` int(11) NOT NULL DEFAULT '0' COMMENT '预计回报时间',
  `sort_order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(10) NOT NULL DEFAULT '1' COMMENT '显示0否 1显示',
  PRIMARY KEY (`cp_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


--
-- 表的结构 `ecs_crowd_trends 众筹项目动态`
--

CREATE TABLE IF NOT EXISTS `{pre}crowd_trends` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '动态id号',
  `goods_id` mediumint(8) NOT NULL  COMMENT '商品id',
  `content` varchar(120)  DEFAULT NULL COMMENT '动态内容',
  `add_time` int(10) COMMENT '添加时间',
  `sort_order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(10) NOT NULL DEFAULT '1' COMMENT '显示 0否1显示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



--
-- 表的结构 `ecs_crowd_category 众筹项目分类`
--

CREATE TABLE IF NOT EXISTS `{pre}crowd_category` (
  `cat_id` mediumint(5) NOT NULL AUTO_INCREMENT COMMENT '众筹分类id',
  `cat_name` varchar(90)  DEFAULT NULL COMMENT '分类名称',
  `cat_desc` varchar(255)  DEFAULT NULL COMMENT '分类描述',
  `parent_id` mediumint(5)  NOT NULL DEFAULT '0' COMMENT '父分类id',
  `sort_order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '显示 0 否 1显示',
  PRIMARY KEY (`cat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- 表的结构 `ecs_crowd_like 关注众筹项目`
--

CREATE TABLE IF NOT EXISTS `{pre}crowd_like` (
  `id` mediumint(5) NOT NULL AUTO_INCREMENT COMMENT '关注id',
  `goods_id` mediumint(8) NOT NULL  COMMENT '项目id',
  `user_id` mediumint(8) NOT NULL  COMMENT '会员id',
  `add_time` int(10) COMMENT '关注时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- 表的结构 `ecs_crowd_comment 众筹项目评论`
--

CREATE TABLE IF NOT EXISTS `{pre}crowd_comment` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '评论id号',
  `parent_id` mediumint(8) NOT NULL DEFAULT '0' COMMENT '父级id',
  `user_id` mediumint(8) NOT NULL  COMMENT '会员id',
  `user_name` varchar(120) NOT NULL  COMMENT '会员名称',
  `goods_id` mediumint(8) NOT NULL  COMMENT '商品id',
  `content` text  DEFAULT NULL COMMENT '评论内容',
  `add_time` int(10) COMMENT '评论时间',
  `order_id` mediumint(8) DEFAULT NULL COMMENT '订单id',
  `rank` tinyint(1) NOT NULL DEFAULT '0' COMMENT '评论等级',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '显示 1是  0否',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



--
-- 表的结构 `ecs_crowd_article 众筹文章`
--

CREATE TABLE IF NOT EXISTS `{pre}crowd_article` (
  `article_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'id号',
  `title` varchar(120) NOT NULL  COMMENT '标题',
  `description` text  DEFAULT NULL COMMENT '评论内容',
  `add_time` int(10) COMMENT '添加时间',
  `sort_order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_open` tinyint(1) NOT NULL DEFAULT '1' COMMENT '显示 1是  0否',
  PRIMARY KEY (`article_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


--
-- 表的结构 `ecs_crowd_order_info  众筹项目订单`
--

CREATE TABLE IF NOT EXISTS `{pre}crowd_order_info` (
  `order_id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '订单id号',
  `order_sn` varchar(20) DEFAULT NULL COMMENT '订单号,唯一',
  `user_id` mediumint(8) NOT NULL  COMMENT '会员id',  
  `goods_id` mediumint(8) NOT NULL  COMMENT '众筹项目id',
  `cp_id` mediumint(8) NOT NULL  COMMENT '众筹方案id',
  `goods_name` varchar(120) NOT NULL  COMMENT '众筹项目名称',
  `goods_number` int(10) NOT NULL DEFAULT '0' COMMENT '购买众筹方案数量',
  `goods_price` decimal(10,2) NOT NULL  COMMENT '众筹方案价格',
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
  `extension_code` varchar(30) NOT NULL DEFAULT '' COMMENT '',
  `extension_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '',
  `to_buyer` varchar(255) NOT NULL DEFAULT '' COMMENT '商家给客户的留言',
  `pay_note` varchar(255) NOT NULL DEFAULT '' COMMENT '付款备注',
  `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '自增ID',
  `tax` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '发票税额',
  PRIMARY KEY (`order_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
