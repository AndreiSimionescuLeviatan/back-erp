<?php

namespace api\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class that extends the "EvalEmployeeGradeMonthParent" class.
 *
 * @property Employee $employee
 */
class EvalEmployeeGradeMonth extends EvalEmployeeGradeMonthParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.eval_employee_grade_month';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['employee_id' => 'id']],
        ]);
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
}
