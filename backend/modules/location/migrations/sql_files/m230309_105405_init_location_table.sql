CREATE TABLE IF NOT EXISTS `ecf_location`.`street`
(
    `id` INT NOT NULL AUTO_INCREMENT,
    `city_id` INT NOT NULL,
    `name` VARCHAR(255) NULL,
    `deleted` TINYINT NOT NULL DEFAULT 0,
    `added` DATETIME NOT NULL,
    `added_by` INT NOT NULL,
    `updated` DATETIME NULL,
    `updated_by` INT NULL,
    PRIMARY KEY (`id`),
    KEY `city_id` (`city_id`),
    KEY `name` (`name`),
    KEY `deleted` (`deleted`),
    KEY `added` (`added`),
    KEY `added_by` (`added_by`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;