<?php

namespace api\models;

use backend\modules\auto\models\AutoActiveRecord;
use Yii;

class LocationNameParent extends AutoActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'location_name';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['location_new_name', 'location_id', 'user_id', 'added', 'added_by'], 'required'],
            [['location_id', 'user_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['location_new_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-auto', 'ID'),
            'location_new_name' => Yii::t('api-auto', 'Location New Name'),
            'location_id' => Yii::t('api-auto', 'Location ID'),
            'user_id' => Yii::t('api-auto', 'User ID'),
            'deleted' => Yii::t('api-auto', 'Deleted'),
            'added' => Yii::t('api-auto', 'Added'),
            'added_by' => Yii::t('api-auto', 'Added By'),
            'updated' => Yii::t('api-auto', 'Updated'),
            'updated_by' => Yii::t('api-auto', 'Updated By'),
        ];
    }
}