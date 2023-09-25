<?php

namespace api\models;

/**
 * This is the model class for table "shift".
 *
 * @property HrCompany $company
 * @property Employee $employee
 * @property ShiftBreakInterval[] $shiftBreakIntervals
 * @property ShiftBreakInterval $openedShiftBreak
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
        return [
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => HrCompany::className(), 'targetAttribute' => ['company_id' => 'id']],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['employee_id' => 'id']],
        ];
    }

    /**
     * Gets query for [[HrCompany]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(HrCompany::className(), ['id' => 'company_id']);
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

    /**
     * Creates the query that retrieves the current shift opened break
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOpenedShiftBreak()
    {
        return $this->hasOne(ShiftBreakInterval::className(), ['shift_id' => 'id'])
            ->where([
                'stop_initial' => null,
                'deleted' => 0,

            ])
            ->andWhere(['not', ['start_initial' => null]]);
    }
}
