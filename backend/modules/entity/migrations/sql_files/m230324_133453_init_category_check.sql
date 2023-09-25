SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

CREATE TABLE IF NOT EXISTS `category_check` (
    `id` int NOT NULL AUTO_INCREMENT,
    `name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
    `deleted` tinyint NOT NULL DEFAULT '0',
    `added` datetime NOT NULL,
    `added_by` int NOT NULL,
    `updated` datetime DEFAULT NULL,
    `updated_by` int DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;