<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "employee_position_internal".
 *
 * @property HrCompany $company
 * @property Employee $employee
 * @property PositionInternal $positionInternal
 */
class EmployeePositionInternal extends EmployeePositionInternalParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.employee_position_internal';
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
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => HrCompany::className(), 'targetAttribute' => ['company_id' => 'id']],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['employee_id' => 'id']],
            [['position_internal_id'], 'exist', 'skipOnError' => true, 'targetClass' => PositionInternal::className(), 'targetAttribute' => ['position_internal_id' => 'id']],
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
     * Gets query for [[PositionInternal]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPositionInternal()
    {
        return $this->hasOne(PositionInternal::className(), ['id' => 'position_internal_id']);
    }
}
