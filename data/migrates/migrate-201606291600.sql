-- ------------------------------------------------------------
-- author:xiao rui
-- date:20160629
-- description:新增自由组合插件的数据表
-- ------------------------------------------------------------
ALTER TABLE `ecs_group_goods` ADD `group_id` tinyint(3) UNSIGNED NOT NULL AFTER `admin_id`;
ALTER TABLE `ecs_cart` ADD `group_id` varchar(255) NOT NULL AFTER `goods_attr_id`;
CREATE TABLE ecs_cart_combo like  ecs_cart;