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
 */
class EmployeeCompanyParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'employee_company';
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
            [['employee_id', 'company_id', 'cim_number', 'type', 'department_id', 'position_cor_id', 'start_schedule', 'stop_schedule', 'added', 'added_by'], 'required'],
            [['employee_id', 'company_id', 'type', 'department_id', 'office_id', 'position_cor_id', 'direct_manager', 'holidays', 'workplace', 'contract_duration', 'off_hours', 'main_activity', 'order_by', 'added_by', 'updated_by', 'deleted'], 'integer'],
            [['start_schedule', 'stop_schedule', 'health_control', 'employment_date', 'contract_end_date', 'added', 'updated'], 'safe'],
            [['schedule_hours', 'break_hours'], 'number'],
            [['cim_number', 'phone_number'], 'string', 'max' => 32],
            [['email'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-hr', 'ID'),
            'employee_id' => Yii::t('api-hr', 'Employee ID'),
            'company_id' => Yii::t('api-hr', 'Company ID'),
            'cim_number' => Yii::t('api-hr', 'Cim Number'),
            'type' => Yii::t('api-hr', 'Type'),
            'department_id' => Yii::t('api-hr', 'Department ID'),
            'office_id' => Yii::t('api-hr', 'Office ID'),
            'position_cor_id' => Yii::t('api-hr', 'Position Cor ID'),
            'direct_manager' => Yii::t('api-hr', 'Direct Manager'),
            'start_schedule' => Yii::t('api-hr', 'Start Schedule'),
            'stop_schedule' => Yii::t('api-hr', 'Stop Schedule'),
            'holidays' => Yii::t('api-hr', 'Holidays'),
            'health_control' => Yii::t('api-hr', 'Health Control'),
            'employment_date' => Yii::t('api-hr', 'Employment Date'),
            'email' => Yii::t('api-hr', 'Email'),
            'phone_number' => Yii::t('api-hr', 'Phone Number'),
            'workplace' => Yii::t('api-hr', 'Workplace'),
            'contract_duration' => Yii::t('api-hr', 'Contract Duration'),
            'contract_end_date' => Yii::t('api-hr', 'Contract End Date'),
            'schedule_hours' => Yii::t('api-hr', 'Schedule Hours'),
            'break_hours' => Yii::t('api-hr', 'Break Hours'),
            'off_hours' => Yii::t('api-hr', 'Off Hours'),
            'main_activity' => Yii::t('api-hr', 'Main Activity'),
            'order_by' => Yii::t('api-hr', 'Order By'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
            'deleted' => Yii::t('api-hr', 'Deleted'),
        ];
    }
}
