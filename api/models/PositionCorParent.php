<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "position_cor".
 *
 * @property int $id
 * @property string $name
 * @property int $department_id
 * @property int $company_id
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class PositionCorParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'position_cor';
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
            [['name', 'added', 'added_by'], 'required'],
            [['department_id', 'company_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['name', 'department_id', 'company_id'], 'unique', 'targetAttribute' => ['name', 'department_id', 'company_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-hr', 'ID'),
            'name' => Yii::t('api-hr', 'Name'),
            'department_id' => Yii::t('api-hr', 'Department ID'),
            'company_id' => Yii::t('api-hr', 'Company ID'),
            'deleted' => Yii::t('api-hr', 'Deleted'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}
