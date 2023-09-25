ALTER TABLE `erp_company` ADD `latitude` DECIMAL(8,6) NULL AFTER `updated_by`,
    ADD `longitude` DECIMAL(9,6) NULL AFTER `latitude`,
    ADD `radius` DECIMAL(2,1) NULL AFTER `longitude`;