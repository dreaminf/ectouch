--
-- 删除表结构
--
DROP TABLE IF EXISTS `ecs_touch_nav`;
DROP TABLE IF EXISTS `ecs_touch_auth`;
DROP TABLE IF EXISTS `ecs_touch_users`;

--
-- 更新表结构
--
ALTER TABLE `ecs_category` DROP COLUMN `touch_img`;
ALTER TABLE `ecs_brand` DROP COLUMN `touch_img`, DROP COLUMN `touch_content`;
ALTER TABLE `ecs_goods` DROP COLUMN `touch_sales`, DROP COLUMN `touch_content`;
ALTER TABLE `ecs_goods_activity` DROP COLUMN `touch_img`;
ALTER TABLE `ecs_article` DROP COLUMN `touch_content`;
ALTER TABLE `ecs_topic` DROP COLUMN `touch_img`, DROP COLUMN `touch_intro`;
ALTER TABLE `ecs_feedback` DROP COLUMN `mark_read`;
ALTER TABLE `ecs_order_info` DROP COLUMN `touch_order`;

--
-- 插入数据
--
DELETE FROM ecs_ad_position where position_id = 256;
DELETE FROM ecs_ad where position_id = 256;

DELETE FROM ecs_shop_config where code = 'sms_ecmoban_user';
DELETE FROM ecs_shop_config where code = 'sms_ecmoban_password';
DELETE FROM ecs_shop_config where code = 'sms_signin';

DELETE FROM ecs_shop_config where code = 'shop_url';
DELETE FROM ecs_shop_config where code = 'touch_template';
DELETE FROM ecs_shop_config where code = 'touch_stylename';
DELETE FROM ecs_shop_config where code = 'touch_logo';
DELETE FROM ecs_shop_config where code = 'touch_nopic';
DELETE FROM ecs_shop_config where code = 'show_asynclist';
