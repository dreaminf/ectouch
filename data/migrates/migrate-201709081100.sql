-- ------------------------------------------------------------
-- author: han
-- date:20170908
-- description:订单表增加纳税人识别号字段
-- ------------------------------------------------------------
--
-- 字段 `attr_sale_price`
--

ALTER TABLE  `{pre}goods_attr` ADD  `	attr_sale_price` varchar(255) NOT NULL DEFAULT '' AFTER `attr_price`;


--
-- 分销提现类型  字段 `type,zfb_bank_user_name,zfb_bank_card`
--

ALTER TABLE  `{pre}drp_bank` ADD  `type` INT( 10 ) NOT NULL DEFAULT  '1' COMMENT  '1 支付宝，2银行卡' AFTER  `bank_card`;

ALTER TABLE  `{pre}drp_bank` ADD  `zfb_bank_user_name` VARCHAR(255) NOT NULL AFTER  `bank_user_name`;

ALTER TABLE  `{pre}drp_bank` ADD  `zfb_bank_card` VARCHAR(255) NOT NULL AFTER  `bank_card`;