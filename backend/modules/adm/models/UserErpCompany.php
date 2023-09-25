<?php

namespace backend\modules\adm\models;

use backend\modules\crm\models\Company;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "user_erp_company".
 *
 * @property int $id
 * @property int $user_id
 * @property int $company_id
 *
 * @property Company $company
 * @property User $user
 */
class UserErpCompany extends UserErpCompanyParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_INITIAL . '.user_erp_company';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['user_id', 'company_id'], 'unique', 'targetAttribute' => ['user_id', 'company_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']]
        ]);
    }

    /**
     * Gets query for [[Company]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
