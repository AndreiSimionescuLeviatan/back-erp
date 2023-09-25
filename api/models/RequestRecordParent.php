<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "request_record".
 *
 * @property int $id
 * @property int $employee_id
 * @property int $company_id
 * @property int $holiday_type_id holiday type id
 * @property int $counter time off period in minutes
 * @property string $start start date
 * @property string $stop stop date
 * @property int|null $take_over_employee_id the employee who takes over the duties
 * @property string|null $observations the observations added by the employee when the request was made
 * @property int $status 0: default; 1: in progress; 2: approved; 3: disapproved
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class RequestRecordParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'request_record';
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
            [['employee_id', 'company_id', 'holiday_type_id', 'counter', 'start', 'stop', 'added', 'added_by'], 'required'],
            [['employee_id', 'company_id', 'holiday_type_id', 'counter', 'take_over_employee_id', 'status', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['start', 'stop', 'added', 'updated'], 'safe'],
            [['observations'], 'string', 'max' => 512],
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
            'holiday_type_id' => Yii::t('api-hr', 'Holiday Type ID'),
            'counter' => Yii::t('api-hr', 'Counter'),
            'start' => Yii::t('api-hr', 'Start'),
            'stop' => Yii::t('api-hr', 'Stop'),
            'take_over_employee_id' => Yii::t('api-hr', 'Take Over Employee ID'),
            'observations' => Yii::t('api-hr', 'Observations'),
            'status' => Yii::t('api-hr', 'Status'),
            'deleted' => Yii::t('api-hr', 'Deleted'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}
