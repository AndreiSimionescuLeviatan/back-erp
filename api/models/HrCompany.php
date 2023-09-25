<?php

namespace api\models;

/**
 * This is the model class for table "company" from HR module.
 *
 * @property Department[] $departments
 * @property EmployeeCompany[] $employeeCompanies
 * @property EmployeePositionInternal[] $employeePositionInternals
 * @property Evaluation[] $evaluations
 * @property Office[] $offices
 * @property ShiftBreakInterval[] $shiftBreakIntervals
 * @property Shift[] $shifts
 * @property WorkLocation[] $workLocations
 * @property WorkingDayEmpl[] $workingDayEmpls
 * @property WorkingDay[] $workingDays
 */
class HrCompany extends HrCompanyParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.company';
    }

    /**
     * Gets query for [[Departments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDepartments()
    {
        return $this->hasMany(Department::className(), ['company_id' => 'id']);
    }

    /**
     * Gets query for [[EmployeeCompanies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeCompanies()
    {
        return $this->hasMany(EmployeeCompany::className(), ['company_id' => 'id']);
    }

    /**
     * Gets query for [[EmployeePositionInternals]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeePositionInternals()
    {
        return $this->hasMany(EmployeePositionInternal::className(), ['company_id' => 'id']);
    }

    /**
     * Gets query for [[Evaluations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEvaluations()
    {
        return $this->hasMany(Evaluation::className(), ['company_id' => 'id']);
    }

    /**
     * Gets query for [[Offices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOffices()
    {
        return $this->hasMany(Office::className(), ['company_id' => 'id']);
    }

    /**
     * Gets query for [[ShiftBreakIntervals]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShiftBreakIntervals()
    {
        return $this->hasMany(ShiftBreakInterval::className(), ['company_id' => 'id']);
    }

    /**
     * Gets query for [[Shifts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShifts()
    {
        return $this->hasMany(Shift::className(), ['company_id' => 'id']);
    }

    /**
     * Gets query for [[WorkLocations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkLocations()
    {
        return $this->hasMany(WorkLocation::className(), ['company_id' => 'id']);
    }

    /**
     * Gets query for [[WorkingDayEmpls]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkingDayEmpls()
    {
        return $this->hasMany(WorkingDayEmpl::className(), ['company_id' => 'id']);
    }

    /**
     * Gets query for [[WorkingDays]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkingDays()
    {
        return $this->hasMany(WorkingDay::className(), ['company_id' => 'id']);
    }
}
