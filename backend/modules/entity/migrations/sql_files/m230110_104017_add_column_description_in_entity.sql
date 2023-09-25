SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

ALTER TABLE `entity` ADD `description` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL AFTER `name`;
ALTER TABLE `domain` ADD `description` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL AFTER `name`;

COMMIT;