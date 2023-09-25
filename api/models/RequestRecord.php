<?php

namespace api\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "request_record".
 *
 * @property ApprovalHistory[] $approvalHistories
 * @property Employee $employee
 * @property Employee $takeOverEmployee
 * @property HrCompany $hrCompany
 * @property HolidayType $holidayType
 * @property WorkingDayEmpl[] $workingDayEmpls
 */
class RequestRecord extends RequestRecordParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.request_record';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => HrCompany::className(), 'targetAttribute' => ['company_id' => 'id']],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['employee_id' => 'id']],
            [['take_over_employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['take_over_employee_id' => 'id']],
            [['holiday_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => HolidayType::className(), 'targetAttribute' => ['holiday_type_id' => 'id']],
        ]);
    }

    /**
     * Gets query for [[ApprovalHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getApprovalHistories()
    {
        return $this->hasMany(ApprovalHistory::className(), ['request_record_id' => 'id']);
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
     * Gets query for [[TakeOverEmployee]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTakeOverEmployee()
    {
        return $this->hasOne(Employee::className(), ['id' => 'take_over_employee_id']);
    }

    /**
     * Gets query for [[HolidayType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHolidayType()
    {
        return $this->hasOne(HolidayType::className(), ['id' => 'holiday_type_id']);
    }

    /**
     * Gets query for [[HrCompany]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getErpCompany()
    {
        return $this->hasOne(HrCompany::className(), ['company_id' => 'id']);
    }

    /**
     * Gets query for [[WorkingDayEmpls]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkingDayEmpls()
    {
        return $this->hasMany(WorkingDayEmpl::className(), ['request_record_id' => 'id']);
    }
}
