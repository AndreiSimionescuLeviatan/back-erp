SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

TRUNCATE TABLE `domain`;
INSERT INTO `domain` (`id`, `name`, `description`, `deleted`, `added`, `added_by`) VALUES
(1, 'adm', 'Administrare', 0, NOW(), -1),
(2, 'auto', 'Auto', 0, NOW(), -1),
(3, 'build', 'Executie', 0, NOW(), -1),
(4, 'crm', 'CRM', 0, NOW(), -1),
(5, 'design', 'Design', 0, NOW(), -1),
(6, 'finance', 'Financiar', 0, NOW(), -1),
(7, 'hr', 'HR', 0, NOW(), -1),
(8, 'notification', 'Notificari', 0, NOW(), -1),
(9, 'pmp', 'PMP', 0, NOW(), -1),
(10, 'quiz', 'Checklist', 0, NOW(), -1),
(11, 'windoc', 'Windoc', 0, NOW(), -1),
(12, 'procurement', 'Procurement', 0, NOW(), -1),
(13, 'provision', 'Dispozitii de santier', 0, NOW(), -1);

COMMIT;