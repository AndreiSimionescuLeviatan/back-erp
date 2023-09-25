<?php

namespace api\modules\v2\models;

use api\models\WorkingDayEmpl;
use Yii;

/**
 * This is the model class for table "working_day_empl".
 *
 * @property int $id
 * @property int $company_id
 * @property int $employee_id
 * @property int $year
 * @property int $month
 * @property string $day
 * @property int $work 0: no; 1: yes
 * @property int $co 0: no; 1: yes
 * @property int|null $request_record_id request record id
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Employee $employee
 * @property RequestRecord $requestRecord
 */
class WorkingDayEmplParent extends WorkingDayEmpl
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'working_day_empl';
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
            [['company_id', 'employee_id', 'year', 'month', 'day', 'work', 'co', 'added', 'added_by'], 'required'],
            [['company_id', 'employee_id', 'year', 'month', 'work', 'co', 'request_record_id', 'added_by', 'updated_by'], 'integer'],
            [['day', 'added', 'updated'], 'safe'],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['employee_id' => 'id']],
            [['request_record_id'], 'exist', 'skipOnError' => true, 'targetClass' => RequestRecord::className(), 'targetAttribute' => ['request_record_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-hr', 'ID'),
            'company_id' => Yii::t('api-hr', 'Company ID'),
            'employee_id' => Yii::t('api-hr', 'Employee ID'),
            'year' => Yii::t('api-hr', 'Year'),
            'month' => Yii::t('api-hr', 'Month'),
            'day' => Yii::t('api-hr', 'Day'),
            'work' => Yii::t('api-hr', 'Work'),
            'co' => Yii::t('api-hr', 'Co'),
            'request_record_id' => Yii::t('api-hr', 'Request Record ID'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
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
