SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `ecf_adm`
--
-- --------------------------------------------------------
--
-- Table structure for table `user_erp_company`
--

CREATE TABLE IF NOT EXISTS `user_erp_company`
(
    `id`         int NOT NULL AUTO_INCREMENT,
    `user_id`    int NOT NULL,
    `company_id` int NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_id` (`user_id`, `company_id`),
    KEY `company_id` (`company_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT ='connects users from adm DB to ERP companies';
COMMIT;