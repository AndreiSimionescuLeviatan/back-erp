<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "family_category".
 *
 * @property int $id
 * @property string|null $code
 * @property string $name
 * @property string|null $description
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class RevitFamilyCategoryParent extends RevitActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'family_category';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_revit_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'added', 'added_by'], 'required'],
            [['deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['code'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 5120],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('revit-api', 'ID'),
            'code' => Yii::t('revit-api', 'Code'),
            'name' => Yii::t('revit-api', 'Name'),
            'description' => Yii::t('revit-api', 'Description'),
            'deleted' => Yii::t('revit-api', 'Deleted'),
            'added' => Yii::t('revit-api', 'Added'),
            'added_by' => Yii::t('revit-api', 'Added By'),
            'updated' => Yii::t('revit-api', 'Updated'),
            'updated_by' => Yii::t('revit-api', 'Updated By'),
        ];
    }
}
