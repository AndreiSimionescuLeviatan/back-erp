SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

CREATE TABLE IF NOT EXISTS `entity_action_category` (
    `id` int NOT NULL AUTO_INCREMENT,
    `entity_id` int NOT NULL,
    `category_check_id` int NOT NULL,
    `deleted` tinyint NOT NULL DEFAULT '0',
    `added` datetime NOT NULL,
    `added_by` int NOT NULL,
    `updated` datetime DEFAULT NULL,
    `updated_by` int DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx-entity-action-category-entity_id` (`entity_id`),
    KEY `idx-category_check_id` (`category_check_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
