ALTER TABLE `erp_company`
    CHANGE `company_administrator_id` `general_manager_id` INT(11) NOT NULL COMMENT 'general manager(company administrator) employee id taken from employee table';