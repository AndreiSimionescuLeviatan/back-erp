CREATE TABLE IF NOT EXISTS `user_signature` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `signature` VARCHAR(255) NOT NULL,
    `deleted` tinyint NOT NULL DEFAULT '0',
    `added` datetime NOT NULL,
    `added_by` INT NOT NULL,
    `updated` datetime DEFAULT NULL,
    `updated_by` INT DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `deleted` (`deleted`),
    KEY `added_by` (`added_by`),
    KEY `updated_by` (`updated_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci