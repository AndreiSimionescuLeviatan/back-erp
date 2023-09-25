CREATE TABLE IF NOT EXISTS `ecf_location`.`address`
(
    `id` INT NOT NULL AUTO_INCREMENT,
    `street_id` INT NOT NULL,
    `number` VARCHAR(255) NULL,
    `block` VARCHAR(255) NULL,
    `scale` VARCHAR(255) NULL,
    `floor` VARCHAR(255) NULL,
    `apartment` VARCHAR(255) NULL,
    `deleted` TINYINT NOT NULL DEFAULT 0,
    `added` DATETIME NOT NULL,
    `added_by` INT NOT NULL,
    `updated` DATETIME NULL,
    `updated_by` INT NULL,
    PRIMARY KEY (`id`),
    KEY `street_id` (`street_id`),
    KEY `deleted` (`deleted`),
    KEY `added` (`added`),
    KEY `added_by` (`added_by`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;