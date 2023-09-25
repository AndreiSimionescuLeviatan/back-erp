<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "permission_day".
 *
 * @property int $id
 * @property int $company_id
 * @property int $employee_id
 * @property int $year
 * @property int $month
 * @property string $day
 * @property int $work 0: no; 1: yes
 * @property int $co 0: no; 1: yes
 * @property int $permission 0: no; 1: yes
 * @property string|null $start_hour
 * @property string|null $stop_hour
 * @property int|null $permission_type 1: Justificată; 2: Nejustificată, 3: Cu recuperare, 4: Formare profesională
 * @property int|null $with_recuperation 0: no; 1: yes
 * @property int|null $all_day 0: no; 1: yes
 * @property int $deleted 0: no; 1: yes
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Employee $employee
 * @property PermissionRecuperation[] $permissionRecuperations
 */
class PermissionDayParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'permission_day';
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
            [['company_id', 'employee_id', 'year', 'month', 'day', 'work', 'co', 'permission', 'added', 'added_by'], 'required'],
            [['company_id', 'employee_id', 'year', 'month', 'work', 'co', 'permission', 'permission_type', 'with_recuperation', 'all_day', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['day', 'start_hour', 'stop_hour', 'added', 'updated'], 'safe'],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['employee_id' => 'id']],
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
            'year' => Yii::t('app', 'Year'),
            'month' => Yii::t('app', 'Month'),
            'day' => Yii::t('app', 'Day'),
            'work' => Yii::t('app', 'Work'),
            'co' => Yii::t('app', 'Co'),
            'permission' => Yii::t('app', 'Permission'),
            'start_hour' => Yii::t('app', 'Start Hour'),
            'stop_hour' => Yii::t('app', 'Stop Hour'),
            'permission_type' => Yii::t('app', 'Permission Type'),
            'with_recuperation' => Yii::t('app', 'With Recuperation'),
            'all_day' => Yii::t('app', 'All Day'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
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
     * Gets query for [[PermissionRecuperations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPermissionRecuperations()
    {
        return $this->hasMany(PermissionRecuperation::className(), ['permission_day_id' => 'id']);
    }
}
