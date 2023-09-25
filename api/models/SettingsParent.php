<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "settings".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $value
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class SettingsParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'value', 'added', 'added_by'], 'required'],
            [['description'], 'string'],
            [['added', 'updated'], 'safe'],
            [['added_by', 'updated_by'], 'integer'],
            [['name', 'value'], 'string', 'max' => 1024],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('adm', 'ID'),
            'name' => Yii::t('adm', 'Name'),
            'description' => Yii::t('adm', 'Description'),
            'value' => Yii::t('adm', 'Value'),
            'added' => Yii::t('adm', 'Added'),
            'added_by' => Yii::t('adm', 'Added By'),
            'updated' => Yii::t('adm', 'Updated'),
            'updated_by' => Yii::t('adm', 'Updated By'),
        ];
    }
}
