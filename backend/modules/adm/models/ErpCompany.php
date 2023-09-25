<?php

namespace backend\modules\adm\models;

use backend\modules\crm\models\Company;
use backend\modules\hr\models\Employee;
use yii\helpers\ArrayHelper;

/**
 * This is the model class that extends the "ErpCompanyParent" class.
 *
 * @property Company $company
 * @property Employee $deputyGeneralManager
 * @property Employee $executiveManager
 * @property Employee $generalManager
 * @property Employee $technicalManager
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

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
            [['deputy_general_manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['deputy_general_manager_id' => 'id']],
            [['technical_manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['technical_manager_id' => 'id']],
            [['executive_manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['executive_manager_id' => 'id']],
            [['general_manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['general_manager_id' => 'id']],
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
        return $this->hasOne(Employee::className(), ['id' => 'deputy_general_manager_id']);
    }

    /**
     * Gets query for [[ExecutiveManager]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExecutiveManager()
    {
        return $this->hasOne(Employee::className(), ['id' => 'executive_manager_id']);
    }

    /**
     * Gets query for [[GeneralManager]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGeneralManager()
    {
        return $this->hasOne(Employee::className(), ['id' => 'general_manager_id']);
    }

    /**
     * Gets query for [[TechnicalManager]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTechnicalManager()
    {
        return $this->hasOne(Employee::className(), ['id' => 'technical_manager_id']);
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
}
