<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "eval_employee_grade_month".
 *
 * @property int $id
 * @property int $employee_id
 * @property float|null $grade
 * @property int|null $accuracy
 * @property int $year
 * @property int $month
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 */
class EvalEmployeeGradeMonthParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eval_employee_grade_month';
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
            [['employee_id', 'year', 'month', 'added', 'added_by'], 'required'],
            [['employee_id', 'accuracy', 'year', 'month', 'added_by', 'updated_by'], 'integer'],
            [['grade'], 'number'],
            [['added', 'updated'], 'safe']
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
            'grade' => Yii::t('api-hr', 'Grade'),
            'accuracy' => Yii::t('api-hr', 'Accuracy'),
            'year' => Yii::t('api-hr', 'Year'),
            'month' => Yii::t('api-hr', 'Month'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}
