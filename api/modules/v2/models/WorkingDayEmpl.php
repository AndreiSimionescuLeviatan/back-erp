<?php

namespace api\modules\v2\models;

use api\modules\v1\models\Company;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "working_day_empl".
 *
 * @property Employee $employee
 * @property RequestRecord $requestRecord
 */
class WorkingDayEmpl extends WorkingDayEmplParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.working_day_empl';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['employee_id' => 'id']],
            [['request_record_id'], 'exist', 'skipOnError' => true, 'targetClass' => RequestRecord::className(), 'targetAttribute' => ['request_record_id' => 'id']],
        ]);
    }

    /**
     * Gets query for [[Employee]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::className(), ['id' => 'employee_id']);
    }

    /**
     * Gets query for [[RequestRecord]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRequestRecord()
    {
        return $this->hasOne(RequestRecord::className(), ['id' => 'request_record_id']);
    }
}
