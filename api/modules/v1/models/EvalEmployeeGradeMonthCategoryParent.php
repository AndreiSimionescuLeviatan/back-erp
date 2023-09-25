<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "eval_employee_grade_month_category".
 *
 * @property int $id
 * @property int $employee_id
 * @property int $eval_kpi_category_id
 * @property float $eval_kpi_category_percentage
 * @property float|null $grade
 * @property int $year
 * @property int $month
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class EvalEmployeeGradeMonthCategoryParent extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['employee_id', 'eval_kpi_category_id', 'eval_kpi_category_percentage', 'year', 'month', 'added', 'added_by'], 'required'],
            [['employee_id', 'eval_kpi_category_id', 'year', 'month', 'added_by', 'updated_by'], 'integer'],
            [['eval_kpi_category_percentage', 'grade'], 'number'],
            [['added', 'updated'], 'safe'],
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
            'eval_kpi_category_id' => Yii::t('api-hr', 'Eval Kpi Category ID'),
            'eval_kpi_category_percentage' => Yii::t('api-hr', 'Eval Kpi Category Percentage'),
            'grade' => Yii::t('api-hr', 'Grade'),
            'year' => Yii::t('api-hr', 'Year'),
            'month' => Yii::t('api-hr', 'Month'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}
