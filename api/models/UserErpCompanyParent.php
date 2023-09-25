<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "user_erp_company".
 *
 * @property int $id
 * @property int $user_id
 * @property int $company_id
 */
class UserErpCompanyParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_erp_company';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_adm_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'company_id'], 'required'],
            [['user_id', 'company_id'], 'integer'],
            [['user_id', 'company_id'], 'unique', 'targetAttribute' => ['user_id', 'company_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'company_id' => Yii::t('app', 'Company ID'),
        ];
    }
}
