<?php

namespace api\modules\v2\models;


use api\models\UserErpCompany;
use api\modules\v1\models\Company;
use api\modules\v1\models\UserSignature;

/**
 * This is the model class for table "user".
 *
 * @property Company[] $companies
 * @property UserErpCompany[] $userErpCompanies
 * @property UserSignature[] $userSignatures
 */
class User extends UserParent
{

    /**
     * Gets query for [[Companies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompanies()
    {
        return $this->hasMany(Company::className(), ['id' => 'company_id'])->viaTable('user_erp_company', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserErpCompanies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserErpCompanies()
    {
        return $this->hasMany(UserErpCompany::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserSignatures]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserSignatures()
    {
        return $this->hasMany(UserSignature::className(), ['user_id' => 'id']);
    }
}
