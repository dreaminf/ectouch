/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50538
Source Host           : localhost:3306
Source Database       : ectouchv2

Target Server Type    : MYSQL
Target Server Version : 50538
File Encoding         : 65001

Date: 2015-09-29 13:22:06
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ecs_wechat_wall
-- ----------------------------
DROP TABLE IF EXISTS `ecs_wechat_wall`;
CREATE TABLE `ecs_wechat_wall` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '活动名称',
  `logo` varchar(100) NOT NULL COMMENT '公司logo',
  `background` varchar(100) NOT NULL COMMENT '活动背景',
  `starttime` int(11) unsigned NOT NULL COMMENT '开始时间',
  `endtime` int(11) unsigned NOT NULL COMMENT '结束时间',
  `prize` text NOT NULL COMMENT '奖品列表',
  `content` varchar(255) NOT NULL COMMENT '活动说明',
  `support` varchar(255) NOT NULL COMMENT '赞助支持',
  `status` tinyint(1) unsigned zerofill NOT NULL COMMENT '活动状态，0未开始， 1进行中， 2已结束',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ecs_wechat_wall_msg
-- ----------------------------
DROP TABLE IF EXISTS `ecs_wechat_wall_msg`;
CREATE TABLE `ecs_wechat_wall_msg` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户留言id',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户编号',
  `content` text NOT NULL COMMENT '留言内容',
  `addtime` int(11) unsigned NOT NULL COMMENT '发送时间',
  `checktime` int(11) unsigned NOT NULL COMMENT '审核时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '审核状态，0未审核，1审核通过',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ecs_wechat_wall_user
-- ----------------------------
DROP TABLE IF EXISTS `ecs_wechat_wall_user`;
CREATE TABLE `ecs_wechat_wall_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `wall_id` int(11) unsigned NOT NULL COMMENT '微信墙id',
  `nickname` varchar(60) NOT NULL COMMENT '用户昵称',
  `sex` tinyint(1) unsigned NOT NULL COMMENT '性别,2女，1男，0保密',
  `headimg` varchar(255) NOT NULL COMMENT '头像',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '审核状态',
  `addtime` int(11) unsigned NOT NULL COMMENT '添加时间',
  `checktime` int(11) unsigned NOT NULL COMMENT '审核时间',
  `openid` varchar(100) NOT NULL COMMENT '微信用户openid',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- 增加字段
ALTER TABLE `ecs_wechat_prize` ADD COLUMN `wall_id` int(11) UNSIGNED NOT NULL DEFAULT 0;
