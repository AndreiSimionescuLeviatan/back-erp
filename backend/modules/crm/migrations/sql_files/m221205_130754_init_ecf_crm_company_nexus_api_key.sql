START TRANSACTION;
SET time_zone = "+00:00";

--
-- Bază de date: `ecf_crm`
--

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `company_nexus_api_key`
--

DROP TABLE IF EXISTS `company_nexus_api_key`;
CREATE TABLE IF NOT EXISTS `company_nexus_api_key` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `company_id` int(11) NOT NULL,
    `api_key` varchar(255) NOT NULL,
    `deleted` tinyint(4) NOT NULL,
    `added` datetime NOT NULL,
    `added_by` int(11) NOT NULL,
    `updated` datetime DEFAULT NULL,
    `updated_by` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `company_id` (`company_id`),
    KEY `added_by` (`added_by`),
    KEY `updated_by` (`updated_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Eliminarea datelor din tabel `company_nexus_api_key`
--

INSERT INTO `company_nexus_api_key` (`id`, `company_id`, `api_key`, `deleted`, `added`, `added_by`, `updated`, `updated_by`) VALUES
                                                                                                                                 (1, 1, 'f2565c7d807bbeaf5f4ddbba8f650a10', 0, '2022-12-05 13:15:28', 190, NULL, NULL),
                                                                                                                                 (2, 2, 'e42d77b35c3558f480adc48d8911c666', 0, '2022-12-05 13:15:28', 190, NULL, NULL),
                                                                                                                                 (3, 697, '247e591c5240d0a1a69d2afe09193cf0', 0, '2022-12-05 13:16:29', 190, NULL, NULL);
COMMIT;