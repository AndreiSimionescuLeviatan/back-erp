<?php

namespace api\modules\v1\models;

/**
 * This is the model class that extends the "EvalEmployeeGradeMonthCategoryParent" class.
 */
class EvalEmployeeGradeMonthCategory extends EvalEmployeeGradeMonthCategoryParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.eval_employee_grade_month_category';
    }
}
