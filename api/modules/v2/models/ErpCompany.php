<?php

namespace api\modules\v2\models;

use api\models\Company;
use api\models\ErpCompanyParent;
use api\models\User;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "erp_company".
 *
 * @property Company $company
 * @property User $deputyGeneralManager
 * @property User $executiveManager
 * @property User $generalManager
 * @property User $technicalManager
 */
class ErpCompany extends ErpCompanyParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_INITIAL . '.erp_company';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
            [['deputy_general_manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['deputy_general_manager_id' => 'id']],
            [['technical_manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['technical_manager_id' => 'id']],
            [['executive_manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['executive_manager_id' => 'id']],
            [['general_manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['general_manager_id' => 'id']],
        ]);
    }

    /**
     * Gets query for [[Company]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * Gets query for [[DeputyGeneralManager]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeputyGeneralManager()
    {
        return $this->hasOne(User::className(), ['id' => 'deputy_general_manager_id']);
    }

    /**
     * Gets query for [[ExecutiveManager]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExecutiveManager()
    {
        return $this->hasOne(User::className(), ['id' => 'executive_manager_id']);
    }

    /**
     * Gets query for [[GeneralManager]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGeneralManager()
    {
        return $this->hasOne(User::className(), ['id' => 'general_manager_id']);
    }

    /**
     * Gets query for [[TechnicalManager]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTechnicalManager()
    {
        return $this->hasOne(User::className(), ['id' => 'technical_manager_id']);
    }
}
