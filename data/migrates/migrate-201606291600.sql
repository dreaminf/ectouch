-- ------------------------------------------------------------
-- author:xiao rui
-- date:20160629
-- description:新增自由组合插件的数据表
-- ------------------------------------------------------------
ALTER TABLE `ecs_group_goods` ADD COLUMN `group_id` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '配件分组' AFTER `admin_id`;

ALTER TABLE `ecs_cart` ADD COLUMN `group_id` varchar(255) NOT NULL DEFAULT ‘’ COMMENT '套餐组合分组标识' AFTER `goods_attr_id`;

CREATE TABLE ecs_cart_combo like  ecs_cart;

