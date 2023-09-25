ALTER TABLE `erp_company`
    ADD `deputy_general_manager_id` INT NULL DEFAULT NULL COMMENT 'company deputy general manager employee id taken from employee table' AFTER `general_manager_id`,
    ADD `technical_manager_id`      INT NULL DEFAULT NULL COMMENT 'company technical manager employee id taken from employee table' AFTER `deputy_general_manager_id`,
    ADD `executive_manager_id`      INT NULL DEFAULT NULL COMMENT 'company executive manager employee id taken from employee table' AFTER `technical_manager_id`;