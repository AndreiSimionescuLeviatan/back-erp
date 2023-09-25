<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "car_zone_photo".
 *
 * @property int $id
 * @property int $user_id
 * @property int $car_id
 * @property int $zone_id
 * @property string $name
 * @property string $image_name
 * @property int $type 1 - check in
 2- check out
 * @property string $added
 * @property int $added_by
 */
class CarZonePhoto extends \yii\db\ActiveRecord
{
    const API_FULL_DIR = 'http://ecf-erp-api/web/signature/';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'car_zone_photo';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_auto_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'car_id', 'zone_id', 'name', 'image_name', 'type', 'added', 'added_by'], 'required'],
            [['user_id', 'car_id', 'zone_id', 'type', 'added_by'], 'integer'],
            [['name', 'image_name'], 'string'],
            [['added'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'car_id' => Yii::t('app', 'Car ID'),
            'zone_id' => Yii::t('app', 'Zone ID'),
            'name' => Yii::t('app', 'Name'),
            'image_name' => Yii::t('app', 'Image Name'),
            'type' => Yii::t('app', 'Type'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
        ];
    }
}
