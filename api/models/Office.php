<?php

namespace api\models;

/**
 * This is the model class for table "office".
 *
 * @property HrCompany $company
 * @property Department $department
 * @property EmployeeCompany[] $employeeCompanies
 * @property Evaluation[] $evaluations
 * @property Employee $headOfOffice
 */
class Office extends OfficeParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.office';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => HrCompany::className(), 'targetAttribute' => ['company_id' => 'id']],
            [['department_id'], 'exist', 'skipOnError' => true, 'targetClass' => Department::className(), 'targetAttribute' => ['department_id' => 'id']],
            [['head_of_office'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['head_of_office' => 'id']],
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
     * Gets query for [[Department]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDepartment()
    {
        return $this->hasOne(Department::className(), ['id' => 'department_id']);
    }

    /**
     * Gets query for [[EmployeeCompanies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeCompanies()
    {
        return $this->hasMany(EmployeeCompany::className(), ['office_id' => 'id']);
    }

    /**
     * Gets query for [[Evaluations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEvaluations()
    {
        return $this->hasMany(Evaluation::className(), ['office_id' => 'id']);
    }

    /**
     * Gets query for [[HeadOfOffice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHeadOfOffice()
    {
        return $this->hasOne(Employee::className(), ['id' => 'head_of_office']);
    }
}
