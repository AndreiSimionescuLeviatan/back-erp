<?php

namespace api\modules\v1\models;

use api\models\Company;
use yii\helpers\ArrayHelper;

class EmployeeAutoFleet extends EmployeeAutoFleetParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.employee_auto_fleet';
    }

    /**
     * Gets query for [[Company]].
     */
    public function rules()
    {
        $rules = parent::rules();
        return ArrayHelper::merge($rules, [
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
        ]);
    }
}
