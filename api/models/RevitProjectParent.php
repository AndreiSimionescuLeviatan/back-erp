<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "revit_project".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property int $source
 * @property string|null $file_path
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property RevitFamily[] $revitFamilies
 * @deprecated on Revit Andrei renounced to this action since 13/10/2022
 */
class RevitProjectParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'revit_project';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_build_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name', 'source', 'added', 'added_by'], 'required'],
            [['source', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['code'], 'string', 'max' => 64],
            [['name', 'file_path'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 4],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'code' => Yii::t('app', 'Code'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'source' => Yii::t('app', 'Source'),
            'file_path' => Yii::t('app', 'File Path'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[RevitFamilies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRevitFamilies()
    {
        return $this->hasMany(RevitFamily::className(), ['revit_project_id' => 'id']);
    }
}
