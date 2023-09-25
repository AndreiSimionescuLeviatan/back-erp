ALTER TABLE `erp_company`
    ADD `company_administrator_id` INT NOT NULL COMMENT 'general manager(company administrator) employee id taken from employee table' AFTER `company_id`;