SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

TRUNCATE TABLE `entity`;
INSERT INTO `entity` (`id`, `domain_id`, `name`, `display_column`, `description`, `find_replace`, `deleted`, `added`, `added_by`) VALUES
(1, 3, 'article', 'code', 'Centralizator articole', 1, 0, NOW(), -1),
(2, 3, 'article_beneficiary_price_history', NULL,'Istoric preturi articole proiecte', 0, 0, NOW(), -1),
(3, 3, 'article_procurement_price_history', NULL, 'Istoric preturi articole achizitii', 0, 0, NOW(), -1),
(4, 3, 'article_quantity', NULL, 'Liste de cantitati - Proiectare - Articole', 0, 0, NOW(), -1),
(5, 3, 'item_price_analytics', NULL, 'Analitycs istoric preturi', 0, 0, NOW(), -1),
(6, 3, 'quantity_list_changes', NULL, 'Rapoarte Liste de cantitati - Proiectare', 0, 0, NOW(), -1),
(7, 3, 'article_category', NULL, 'Categorii/Subcategorii articole', 0, 0, NOW(), -1),
(8, 12, 'offer_package_content', NULL, 'Pachete ofertare', 0, 0, NOW(), -1),
(9, 12, 'offer_provider_details', NULL, 'Oferte furnizori', 0, 0, NOW(), -1),
(10, 3, 'package', 'name', 'Centralizator pachete', 1, 0, NOW(), -1),
(11, 3, 'package_speciality', NULL, 'Specialitati pachet', 0, 0, NOW(), -1),
(12, 3, 'equipment_quantity', NULL, 'Liste de cantitati - Proiectare - Echipamente', 0, 0, NOW(), -1),
(13, 13, 'provision_new_element', NULL, 'Articole/echipamente - dispozitii de santier', 0, 0, NOW(), -1),
(14, 12, 'offer_package', NULL, 'Pachet ofertare', 0, 0, NOW(), -1),
(15, 12, 'offer_provider', NULL, 'Oferta furnizor', 0, 0, NOW(), -1),
(16, 12, 'offer', NULL, 'Oferta', 0, 0, NOW(), -1),
(17, 12, 'item_merge', NULL, 'Item merge', 0, 0, NOW(), -1),
(18, 5, 'speciality', NULL, 'Specialitate', 0, 0, NOW(), -1);

COMMIT;