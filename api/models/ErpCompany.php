<?php

namespace api\models;

use backend\modules\crm\models\Company;
use yii\db\ActiveQuery;

/**
 * This is the model class that extends the "ErpCompanyParent" class.
 *
 * @property UserErpCompany[] $userCompanies
 * @property Company $company
 * @property User[] $users
 */
class ErpCompany extends ErpCompanyParent
{
    public static $names = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_INITIAL . '.erp_company';
    }

    /**
     * Gets query for [[Companies]].
     * @return ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * return erp companies names
     * @return void
     */
    public static function setNames()
    {
        self::$names = [];
        $models = self::find()->where(['deleted' => 0])->all();
        foreach ($models as $model) {
            self::$names[$model->company_id] = $model->company->name;
        }
        asort(self::$names);
    }


    /**
     * Gets query for [[UserCompanies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserCompanies()
    {
        return $this->hasMany(UserErpCompany::class, ['company_id' => 'id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable(UserErpCompany::tableName(), ['company_id' => 'id']);
    }
}
