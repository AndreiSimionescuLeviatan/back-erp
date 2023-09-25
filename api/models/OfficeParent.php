<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "office".
 *
 * @property int $id
 * @property string $name
 * @property int $department_id
 * @property int $company_id
 * @property int|null $head_of_office
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class OfficeParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'office';
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
            [['name', 'department_id', 'added', 'added_by'], 'required'],
            [['department_id', 'company_id', 'head_of_office', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 64],
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
            'head_of_office' => Yii::t('api-hr', 'Head Of Office'),
            'deleted' => Yii::t('api-hr', 'Deleted'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}
