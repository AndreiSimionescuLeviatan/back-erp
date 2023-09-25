SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `erp_company`;
CREATE TABLE IF NOT EXISTS `erp_company` (
    `id` int NOT NULL AUTO_INCREMENT,
    `company_id` int NOT NULL,
    `added` datetime NOT NULL,
    `added_by` int NOT NULL,
    `updated` datetime DEFAULT NULL,
    `updated_by` int DEFAULT NULL,
    `deleted` tinyint NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `company_id` (`company_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
COMMIT;

ALTER TABLE `erp_company` ADD CONSTRAINT `fk-erp_company-company_id` FOREIGN KEY (`company_id`) REFERENCES `ecf_crm`.`company`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
