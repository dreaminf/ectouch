
-- ----------------------------
-- Table structure for ecs_config
-- ----------------------------
DROP TABLE IF EXISTS `ecs_config`;
CREATE TABLE `ecs_config` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `config` text NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ecs_config
-- ----------------------------
INSERT INTO `ecs_config` VALUES ('1', 'wxpayweb', 'payment', 'wxpay.web', 'wxpay.web', '{\"app_id\":\"\",\"app_secret\":\"\",\"mch_id\":\"\",\"mch_key\":\"\"}', '1', null, null);
