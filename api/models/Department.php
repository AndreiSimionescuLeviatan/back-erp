<?php

namespace api\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "department".
 *
 * @property HrCompany $company
 * @property EmployeeCompany[] $employeeCompanies
 * @property Evaluation[] $evaluations
 * @property Employee $headOfDepartment
 * @property Office[] $offices
 */
class Department extends DepartmentParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.department';
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['head_of_department'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['head_of_department' => 'id']],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => HrCompany::className(), 'targetAttribute' => ['company_id' => 'id']]
        ]);
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
     * Gets query for [[EmployeeCompanies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeCompanies()
    {
        return $this->hasMany(EmployeeCompany::className(), ['department_id' => 'id']);
    }

    /**
     * Gets query for [[Evaluations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEvaluations()
    {
        return $this->hasMany(Evaluation::className(), ['department_id' => 'id']);
    }

    /**
     * Gets query for [[HeadOfDepartment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHeadOfDepartment()
    {
        return $this->hasOne(Employee::className(), ['id' => 'head_of_department']);
    }

    /**
     * Gets query for [[Offices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOffices()
    {
        return $this->hasMany(Office::className(), ['department_id' => 'id']);
    }
}
