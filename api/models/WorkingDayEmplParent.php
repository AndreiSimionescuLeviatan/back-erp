<?php

namespace api\models;

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
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class WorkingDayEmplParent extends \yii\db\ActiveRecord
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
            [['company_id', 'employee_id', 'year', 'month', 'work', 'co', 'added_by', 'updated_by'], 'integer'],
            [['day', 'added', 'updated'], 'safe'],
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
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}
