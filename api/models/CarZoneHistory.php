<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "car_zone_history".
 *
 * @property int $id
 * @property int $car_id
 * @property int|null $zone_id
 * @property int|null $zone_option_id
 * @property string|null $observations
 * @property string|null $zone_photo
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Car $car
 * @property Zone $zone
 * @property ZoneOption $zoneOption
 */
class CarZoneHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'car_zone_history';
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
            [['car_id', 'added', 'added_by'], 'required'],
            [['car_id', 'zone_id', 'zone_option_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['observations', 'zone_photo'], 'string', 'max' => 255],
            [['car_id'], 'exist', 'skipOnError' => true, 'targetClass' => Car::className(), 'targetAttribute' => ['car_id' => 'id']],
            [['zone_id'], 'exist', 'skipOnError' => true, 'targetClass' => Zone::className(), 'targetAttribute' => ['zone_id' => 'id']],
            [['zone_option_id'], 'exist', 'skipOnError' => true, 'targetClass' => ZoneOption::className(), 'targetAttribute' => ['zone_option_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'car_id' => Yii::t('app', 'Car ID'),
            'zone_id' => Yii::t('app', 'Zone ID'),
            'zone_option_id' => Yii::t('app', 'Zone Option ID'),
            'observations' => Yii::t('app', 'Observations'),
            'zone_photo' => Yii::t('app', 'Zone Photo'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Car]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCar()
    {
        return $this->hasOne(Car::className(), ['id' => 'car_id']);
    }

    /**
     * Gets query for [[Zone]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getZone()
    {
        return $this->hasOne(Zone::className(), ['id' => 'zone_id']);
    }

    /**
     * Gets query for [[ZoneOption]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getZoneOption()
    {
        return $this->hasOne(ZoneOption::className(), ['id' => 'zone_option_id']);
    }
}
