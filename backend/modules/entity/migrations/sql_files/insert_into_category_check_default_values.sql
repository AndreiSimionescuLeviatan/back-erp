SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

TRUNCATE TABLE `category_check`;
INSERT INTO `category_check` (`id`, `name`, `deleted`, `added`, `added_by`) VALUES
(1, 'Centralizator articole', 0, NOW(), -1),
(2, 'Istoric preturi articole - Proiecte și Achiziții', 0, NOW(), -1),
(3, 'Liste de cantitati - Proiectare și Achiziții', 0, NOW(), -1),
(4, 'Analitycs istoric preturi', 0, NOW(), -1),
(5, 'Centralizator pachete', 0, NOW(), -1),
(6, 'Dispozitii de santier', 0, NOW(), -1);

COMMIT;