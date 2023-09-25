SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

TRUNCATE TABLE `entity_action_operation`;
INSERT INTO `entity_action_operation` (`id`, `action_category_id`, `entity_change_id`, `entity_source_id`, `action_sql`, `name_column_change`, `name_column_source`, `condition_sql`, `default_value`, `description`, `deleted`, `order_by`, `added`, `added_by`) VALUES
(1, 2, 2, 1, 'UPDATE', 'article_id', 'id', 'article_id = {entity_old_id}', NULL, 'modificare articol', '0', '1', NOW(), '-1'),
(2, 2, 3, 1, 'UPDATE', 'article_id', 'id', 'article_id = {entity_old_id}', NULL, 'modificare articol', '0', '2', NOW(), '-1'),
(3, 3, 4, 7, 'UPDATE', 'category_id', 'category_id', 'article_id = {entity_old_id}', NULL, 'modificare categorie', '0', '3', NOW(), '-1'),
(4, 3, 4, 7, 'UPDATE', 'subcategory_id', 'subcategory_id', 'article_id = {entity_old_id}', NULL, 'modificare subcategorie', '0', '4', NOW(), '-1'),
(5, 3, 4, 1, 'UPDATE', 'article_id', 'id', 'article_id = {entity_old_id}', NULL, 'modificare articol', '0', '5', NOW(), '-1'),
(6, 4, 5, NULL, 'DELETE', NULL, NULL, 'item_type=1 AND item_id = {entity_old_id}', NULL, 'stergere din analiza pret', '0', '6', NOW(), '-1'),
(7, 3, 6, 1, 'UPDATE', 'item_id', 'id', 'item_type=1 AND item_id = {entity_old_id}', NULL, 'modificare articol', '0', '7', NOW(), '-1'),
(8, 1, 1, NULL, 'UPDATE', 'deleted', 'deleted', 'id = {entity_old_id}', 1, 'status sters', '0', '12', NOW(), '-1'),
(9, 3, 8, 1, 'UPDATE', 'item_name', 'name', 'item_type=1 AND item_id = {entity_old_id}', NULL, 'modificare nume articol', '0', '8', NOW(), '-1'),
(10, 3, 8, 1, 'UPDATE', 'item_id', 'id', 'item_type=1 AND item_id = {entity_old_id}', NULL, 'modificare articol', '0', '9', NOW(), '-1'),
(11, 3, 9, 1, 'UPDATE', 'item_name', 'name', 'item_type=1 AND item_id = {entity_old_id}', NULL, 'modificare nume articol', '0', '10', NOW(), '-1'),
(12, 3, 9, 1, 'UPDATE', 'item_id', 'id', 'item_type=1 AND item_id = {entity_old_id}', NULL, 'modificare articol', '0', '11', NOW(), '-1'),
(13, 6, 4, 10, 'UPDATE', 'package_id', 'id', 'package_id = {entity_old_id}', NULL, 'modificare pachet', '0', '1', NOW(), '-1'),
(14, 6, 12, 10, 'UPDATE', 'package_id', 'id', 'package_id = {entity_old_id}', NULL, 'modificare pachet', '0', '2', NOW(), '-1'),
(15, 6, 6, 10, 'UPDATE', 'package_id', 'id', 'package_id = {entity_old_id}', NULL, 'modificare pachet', '0', '3', NOW(), '-1'),
(16, 7, 13, 10, 'UPDATE', 'package_id', 'id', 'package_id = {entity_old_id}', NULL, 'modificare pachet', '0', '4', NOW(), '-1'),
(17, 6, 14, 10, 'UPDATE_OFFER_PACKAGE', 'package_id', 'id', 'package_id = {entity_old_id}', NULL, 'modificare pachet', '0', '5', NOW(), '-1'),
(18, 6, 15, 10, 'UPDATE', 'package_id', 'id', 'package_id = {entity_old_id}', NULL, 'modificare pachet', '0', '6', NOW(), '-1'),
(19, 5, 11, NULL, 'UPDATE', 'deleted', 'deleted', 'package_id = {entity_old_id}', 1, 'status sters', '0', '7', NOW(), '-1'),
(20, 5, 10, NULL, 'UPDATE', 'deleted', 'deleted', 'id = {entity_old_id}', 1, 'status sters', '0', '8', NOW(), '-1');

COMMIT;