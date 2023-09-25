<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "evaluation".
 *
 * @property int $id
 * @property int $company_id
 * @property int|null $department_id
 * @property int|null $office_id
 * @property int $owner_employee_id
 * @property int $employee_id
 * @property string|null $description
 * @property int $year
 * @property int $month
 * @property int|null $status 0 - new; 1 - in progress; 2 - finished
 * @property float|null $grade
 * @property float|null $countersigned_grade countersigned grade
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class EvaluationParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'evaluation';
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
            [['company_id', 'owner_employee_id', 'employee_id', 'year', 'month', 'added', 'added_by'], 'required'],
            [['company_id', 'department_id', 'office_id', 'owner_employee_id', 'employee_id', 'year', 'month', 'status', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['grade', 'countersigned_grade'], 'number'],
            [['added', 'updated'], 'safe'],
            [['description'], 'string', 'max' => 2048],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => HrCompany::className(), 'targetAttribute' => ['company_id' => 'id']],
            [['owner_employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['owner_employee_id' => 'id']],
            [['office_id'], 'exist', 'skipOnError' => true, 'targetClass' => Office::className(), 'targetAttribute' => ['office_id' => 'id']],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['employee_id' => 'id']],
            [['department_id'], 'exist', 'skipOnError' => true, 'targetClass' => Department::className(), 'targetAttribute' => ['department_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-hr', 'ID'),
            'company_id' => Yii::t('api-hr', 'Company ID'),
            'department_id' => Yii::t('api-hr', 'Department ID'),
            'office_id' => Yii::t('api-hr', 'Office ID'),
            'owner_employee_id' => Yii::t('api-hr', 'Owner Employee ID'),
            'employee_id' => Yii::t('api-hr', 'Employee ID'),
            'description' => Yii::t('api-hr', 'Description'),
            'year' => Yii::t('api-hr', 'Year'),
            'month' => Yii::t('api-hr', 'Month'),
            'status' => Yii::t('api-hr', 'Status'),
            'grade' => Yii::t('api-hr', 'Grade'),
            'countersigned_grade' => Yii::t('api-hr', 'Countersigned Grade'),
            'deleted' => Yii::t('api-hr', 'Deleted'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}
