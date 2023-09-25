SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

ALTER TABLE `ecf_entity`.`entity_action_category` CHANGE `name` `category_check_id` INT NOT NULL;

COMMIT;