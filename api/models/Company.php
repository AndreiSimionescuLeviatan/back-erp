<?php

namespace api\models;

use api\modules\v1\models\EntityDomain;
use backend\modules\adm\models\UserSignature;

class Company extends CompanyParent
{
    public static $auto = null;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_CRM . '.company';
    }

    /**
     * @return void
     * @updated_by Alex G.
     * @updated 28/02/2022
     * Due to this function we can extract ether all Company from domain Auto subdomain auto
     */
    public static function setNamesAuto()
    {
        self::$auto = [];
        $entityDomains = EntityDomain::find()->where([
            'domain_id' => 1,
            'entity_id' => 2,
            'subdomain_id' => 2
        ])->all();
        $itemsIDs = [];
        foreach ($entityDomains as $entityDomain) {
            $itemsIDs[] = $entityDomain->item_id;
        }

        if (empty($itemsIDs)) {
            return;
        }

        $companies = self::find()->where(['in', 'id', $itemsIDs])->all();
        foreach ($companies as $company) {
            self::$auto[$company->id] = !empty($company->short_name) ? $company->short_name : $company->name;
        }
    }

    public static function companyLegalAdminName($companyId, $signature = false) {
        $company = ErpCompany::find()
            ->where(['company_id' => $companyId])
            ->one();
        $employee = Employee::find()->where(['user_id' => $company->general_manager_id])->one();
        if (!empty($employee)) {
            if ($signature && !empty(UserSignature::getSignature($company->general_manager_id))) {
                return UserSignature::getSignature($company->general_manager_id);
            }
            return $employee->last_name . ' ' . $employee->first_name . ' ' . $employee->middle_name;
        }
        return '';
    }
}
