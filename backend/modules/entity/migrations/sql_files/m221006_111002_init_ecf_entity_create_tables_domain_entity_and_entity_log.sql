SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

CREATE TABLE IF NOT EXISTS `domain` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
    `deleted` tinyint(1) NOT NULL DEFAULT '0',
    `added` datetime NOT NULL,
    `added_by` int(11) NOT NULL,
    `updated` datetime DEFAULT NULL,
    `updated_by` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `entity` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `domain_id` int(11) NOT NULL,
    `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
    `deleted` tinyint(1) NOT NULL DEFAULT '0',
    `added` datetime NOT NULL,
    `added_by` int(11) NOT NULL,
    `updated` datetime DEFAULT NULL,
    `updated_by` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx-entity-domain_id` (`domain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `entity_action_operation` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `entity_id` int(11) NOT NULL,
    `entity_change_id` int(11) NOT NULL,
    `action_sql` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
    `name_column_change` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
    `name_column_source` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
    `condition_sql` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
    `default_value` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
    `description` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
    `deleted` tinyint(1) NOT NULL DEFAULT '0',
    `order_by` int(11) NOT NULL DEFAULT '0',
    `added` datetime NOT NULL,
    `added_by` int(11) NOT NULL,
    `updated` datetime DEFAULT NULL,
    `updated_by` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx-entity-action-operation-entity_id` (`entity_id`),
    KEY `idx-entity-action-operation-entity_change_id` (`entity_change_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `entity_action` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `entity_id` int(11) NOT NULL,
    `entity_new_id` int(11) NOT NULL,
    `added` datetime NOT NULL,
    `added_by` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx-entity-action-entity_id` (`entity_id`),
    KEY `idx-entity-action-entity_new_id` (`entity_new_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `entity_action_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `entity_action_id` int(11) NOT NULL,
    `entity_operation_id` int(11) NOT NULL,
    `entity_old_id` int(11) NOT NULL,
    `old_value` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
    `new_value` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
    `added` datetime NOT NULL,
    `added_by` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx-entity-entity_action_id` (`entity_action_id`),
    KEY `idx-entity-entity_operation_id` (`entity_operation_id`),
    KEY `idx-entity-entity_old_id` (`entity_old_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

COMMIT;
