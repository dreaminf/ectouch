ALTER TABLE `ecs_drp_bank` ADD COLUMN `bank_region` varchar(255) NULL AFTER `id`;
ALTER TABLE `ecs_drp_bank` ADD COLUMN `bank_user_name` varchar(255) NULL AFTER `bank_region`;