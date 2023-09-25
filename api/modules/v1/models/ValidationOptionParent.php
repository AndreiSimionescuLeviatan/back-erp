<?php

namespace api\modules\v1\models;

use backend\modules\auto\models\AutoActiveRecord;
use Yii;

/**
 * This is the model class for table "validation_option".
 *
 * @property int $id
 * @property string $name
 * @property int $validation_type 1 - serviciu / 2 - protocol
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class ValidationOptionParent extends AutoActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'validation_option';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'validation_type', 'added', 'added_by'], 'required'],
            [['validation_type', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'validation_type' => Yii::t('app', 'Validation Type'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }
}
