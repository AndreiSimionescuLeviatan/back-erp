<?php

namespace api\models;

/**
 * This is the model class for table "position_cor".
 *
 * @property HrCompany $company
 * @property Department $department
 * @property EmployeeCompany[] $employeeCompanies
 */
class PositionCor extends PositionCorParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.position_cor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['department_id'], 'exist', 'skipOnError' => true, 'targetClass' => Department::className(), 'targetAttribute' => ['department_id' => 'id']],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => HrCompany::className(), 'targetAttribute' => ['company_id' => 'id']],
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
        return $this->hasMany(EmployeeCompany::className(), ['position_cor_id' => 'id']);
    }
}
