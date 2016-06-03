-- ------------------------------------------------------------
-- author:xiao rui
-- date:20160603
-- description:虚拟销量字段
-- ------------------------------------------------------------
ALTER TABLE `ecs_goods` ADD `virtual_sales` varchar( 10 ) NOT NULL AFTER `market_price`;