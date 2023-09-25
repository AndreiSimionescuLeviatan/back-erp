<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "employee_company".
 *
 * @property int $id
 * @property int $employee_id
 * @property int $company_id
 * @property string $cim_number
 * @property int $type 1 - angajat permanent; 2 - colaborator
 * @property int $department_id
 * @property int|null $office_id
 * @property int $position_cor_id
 * @property int|null $direct_manager
 * @property string $start_schedule
 * @property string $stop_schedule
 * @property int $holidays
 * @property string|null $health_control
 * @property string|null $employment_date
 * @property string|null $email
 * @property string|null $phone_number
 * @property int|null $workplace 1 - birou; 2 - domiciliu
 * @property int|null $contract_duration 1 - nedeterminată; 2 - determinată
 * @property string|null $contract_end_date
 * @property float|null $schedule_hours
 * @property float|null $break_hours
 * @property int|null $off_hours
 * @property int|null $main_activity 0 - no, 1 - yes
 * @property int|null $order_by
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 * @property int $deleted
 *
 * @property HrCompany $company
 * @property Department $department
 * @property Employee $directManager
 * @property Employee $employee
 * @property Office $office
 * @property PositionCor $positionCor
 */
class EmployeeCompany extends EmployeeCompanyParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.employee_company';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_hr_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['department_id'], 'exist', 'skipOnError' => true, 'targetClass' => Department::className(), 'targetAttribute' => ['department_id' => 'id']],
            [['direct_manager'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['direct_manager' => 'id']],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['employee_id' => 'id']],
            [['office_id'], 'exist', 'skipOnError' => true, 'targetClass' => Office::className(), 'targetAttribute' => ['office_id' => 'id']],
            [['position_cor_id'], 'exist', 'skipOnError' => true, 'targetClass' => PositionCor::className(), 'targetAttribute' => ['position_cor_id' => 'id']],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => HrCompany::className(), 'targetAttribute' => ['company_id' => 'id']],
        ];
    }

    /**
     * Gets query for [[HrCompany]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(HrCompany::className(), ['id' => 'company_id']);
    }

    /**
     * Gets query for [[Department]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDepartment()
    {
        return $this->hasOne(Department::className(), ['id' => 'department_id']);
    }

    /**
     * Gets query for [[DirectManager]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDirectManager()
    {
        return $this->hasOne(Employee::className(), ['id' => 'direct_manager']);
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
     * Gets query for [[Office]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOffice()
    {
        return $this->hasOne(Office::className(), ['id' => 'office_id']);
    }

    /**
     * Gets query for [[PositionCor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPositionCor()
    {
        return $this->hasOne(PositionCor::className(), ['id' => 'position_cor_id']);
    }
}
