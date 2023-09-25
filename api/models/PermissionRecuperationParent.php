<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "permission_recuperation".
 *
 * @property int $id
 * @property int $company_id
 * @property int $employee_id
 * @property int $permission_day_id
 * @property int|null $year
 * @property int|null $month
 * @property string|null $day
 * @property string|null $start_hour
 * @property string|null $stop_hour
 *
 * @property Employee $employee
 * @property PermissionDay $permissionDay
 */
class PermissionRecuperationParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'permission_recuperation';
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
            [['company_id', 'employee_id', 'permission_day_id'], 'required'],
            [['company_id', 'employee_id', 'permission_day_id', 'year', 'month'], 'integer'],
            [['day', 'start_hour', 'stop_hour'], 'safe'],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['employee_id' => 'id']],
            [['permission_day_id'], 'exist', 'skipOnError' => true, 'targetClass' => PermissionDay::className(), 'targetAttribute' => ['permission_day_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'company_id' => Yii::t('app', 'Company ID'),
            'employee_id' => Yii::t('app', 'Employee ID'),
            'permission_day_id' => Yii::t('app', 'Permission Day ID'),
            'year' => Yii::t('app', 'Year'),
            'month' => Yii::t('app', 'Month'),
            'day' => Yii::t('app', 'Day'),
            'start_hour' => Yii::t('app', 'Start Hour'),
            'stop_hour' => Yii::t('app', 'Stop Hour'),
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
     * Gets query for [[PermissionDay]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPermissionDay()
    {
        return $this->hasOne(PermissionDay::className(), ['id' => 'permission_day_id']);
    }
}
