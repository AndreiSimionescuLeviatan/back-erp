<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "eval_employee_grade".
 *
 * @property int $id
 * @property int $employee_id
 * @property float|null $grade
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class EvalEmployeeGradeParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eval_employee_grade';
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
            [['employee_id', 'added', 'added_by'], 'required'],
            [['employee_id', 'added_by', 'updated_by'], 'integer'],
            [['grade'], 'number'],
            [['added', 'updated'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-hr', 'ID'),
            'employee_id' => Yii::t('api-hr', 'Employee ID'),
            'grade' => Yii::t('api-hr', 'Grade'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}
