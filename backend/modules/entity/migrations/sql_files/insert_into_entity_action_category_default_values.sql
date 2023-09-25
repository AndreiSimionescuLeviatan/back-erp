SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

TRUNCATE TABLE `entity_action_category`;
INSERT INTO `entity_action_category` (`id`, `entity_id`, `category_check_id`, `deleted`, `added`, `added_by`) VALUES
(1, 1, 1, 0, NOW(), -1),
(2, 1, 2, 0, NOW(), -1),
(3, 1, 3, 0, NOW(), -1),
(4, 1, 4, 0, NOW(), -1),
(5, 10, 5, 0, NOW(), -1),
(6, 10, 3, 0, NOW(), -1),
(7, 10, 6, 0, NOW(), -1);

COMMIT;