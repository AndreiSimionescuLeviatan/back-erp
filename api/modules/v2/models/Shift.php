<?php

namespace api\modules\v2\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "shift".
 *
 * @property Employee $employee
 * @property ShiftBreakInterval[] $shiftBreakIntervals
 */
class Shift extends ShiftParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.shift';
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

    /**
     * Gets query for [[ShiftBreakIntervals]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShiftBreakIntervals()
    {
        return $this->hasMany(ShiftBreakInterval::className(), ['shift_id' => 'id']);
    }
}
