<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "employee_position_internal".
 *
 * @property int $id
 * @property int $company_id
 * @property int $employee_id
 * @property int $position_internal_id
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 * @property int $deleted
 */
class EmployeePositionInternalParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'employee_position_internal';
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
            [['company_id', 'employee_id', 'position_internal_id', 'added', 'added_by'], 'required'],
            [['company_id', 'employee_id', 'position_internal_id', 'added_by', 'updated_by', 'deleted'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['company_id', 'employee_id', 'position_internal_id'], 'unique', 'targetAttribute' => ['company_id', 'employee_id', 'position_internal_id']],
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
            'employee_id' => Yii::t('api-hr', 'Employee ID'),
            'position_internal_id' => Yii::t('api-hr', 'Position Internal ID'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
            'deleted' => Yii::t('api-hr', 'Deleted'),
        ];
    }
}
