SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

ALTER TABLE `entity` ADD `find_replace` tinyint NOT NULL DEFAULT '0' AFTER `name`;
ALTER TABLE `entity_action_operation`  ADD `entity_source_id` INT DEFAULT NULL AFTER `entity_change_id`;

COMMIT;