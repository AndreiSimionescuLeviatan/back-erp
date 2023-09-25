<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "evaluation".
 *
 * @property int $id
 * @property int $company_id
 * @property int $owner_employee_id
 * @property int $employee_id
 * @property string|null $description
 * @property int $year
 * @property int $month
 * @property int|null $status 0 - new; 1 - in progress; 2 - finished
 * @property float|null $grade
 * @property int|null $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class EvaluationParent extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'owner_employee_id', 'employee_id', 'year', 'month', 'added', 'added_by'], 'required'],
            [['company_id', 'owner_employee_id', 'employee_id', 'year', 'month', 'status', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['grade'], 'number'],
            [['added', 'updated'], 'safe'],
            [['description'], 'string', 'max' => 2048],
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
            'owner_employee_id' => Yii::t('api-hr', 'Owner Employee ID'),
            'employee_id' => Yii::t('api-hr', 'Employee ID'),
            'description' => Yii::t('api-hr', 'Description'),
            'year' => Yii::t('api-hr', 'Year'),
            'month' => Yii::t('api-hr', 'Month'),
            'status' => Yii::t('api-hr', 'Status'),
            'grade' => Yii::t('api-hr', 'Grade'),
            'deleted' => Yii::t('api-hr', 'Deleted'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}