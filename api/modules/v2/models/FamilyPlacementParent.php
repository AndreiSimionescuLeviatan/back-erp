<?php

namespace api\modules\v2\models;

use Yii;

/**
 * This is the model class for table "family_placement".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int|null $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class FamilyPlacementParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'family_placement';
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
            [['id', 'code', 'name', 'added', 'added_by'], 'required'],
            [['id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['code', 'name'], 'string', 'max' => 225],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('revit', 'ID'),
            'code' => Yii::t('revit', 'Code'),
            'name' => Yii::t('revit', 'Name'),
            'deleted' => Yii::t('revit', 'Deleted'),
            'added' => Yii::t('revit', 'Added'),
            'added_by' => Yii::t('revit', 'Added By'),
            'updated' => Yii::t('revit', 'Updated'),
            'updated_by' => Yii::t('revit', 'Updated By'),
        ];
    }
}
