<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "employee_auto_fleet".
 *
 * @property int $id
 * @property int $employee_id
 * @property int $company_id
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Employee $employee
 */
class EmployeeAutoFleetParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'employee_auto_fleet';
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
            [['employee_id', 'company_id', 'added', 'added_by'], 'required'],
            [['employee_id', 'company_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['employee_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'employee_id' => Yii::t('app', 'Employee ID'),
            'company_id' => Yii::t('app', 'Company ID'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
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
