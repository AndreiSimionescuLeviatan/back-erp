<?php

namespace api\models;

use backend\modules\auto\models\AutoActiveRecord;
use Yii;

/**
 * This is the model class for table "location".
 *
 * @property int $id
 * @property string $name
 * @property string|null $roadmap_name
 * @property int|null $nexus_location_id
 * @property int|null $company_id
 * @property int|null $location_type_id
 * @property string|null $address
 * @property string|null $description
 * @property int|null $first_car
 * @property int|null $first_car_id
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Journey[] $journeys
 * @property Journey[] $journeys0
 * @property Car $firstCar
 * @property LocationType $locationType
 */
class LocationParent extends AutoActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'location';
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
            [['name', 'added', 'added_by'], 'required'],
            [['nexus_location_id', 'company_id', 'location_type_id', 'first_car', 'first_car_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['address', 'description'], 'string'],
            [['latitude', 'longitude'], 'number'],
            [['added', 'updated'], 'safe'],
            [['name', 'roadmap_name'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['first_car_id'], 'exist', 'skipOnError' => true, 'targetClass' => Car::className(), 'targetAttribute' => ['first_car_id' => 'id']],
            [['location_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => LocationType::className(), 'targetAttribute' => ['location_type_id' => 'id']],
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
            'roadmap_name' => Yii::t('app', 'Roadmap Name'),
            'nexus_location_id' => Yii::t('app', 'Nexus Location ID'),
            'company_id' => Yii::t('app', 'Company ID'),
            'location_type_id' => Yii::t('app', 'Location Type ID'),
            'address' => Yii::t('app', 'Address'),
            'description' => Yii::t('app', 'Description'),
            'first_car' => Yii::t('app', 'First Car'),
            'first_car_id' => Yii::t('app', 'First Car ID'),
            'latitude' => Yii::t('app', 'Latitude'),
            'longitude' => Yii::t('app', 'Longitude'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Journeys]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJourneys()
    {
        return $this->hasMany(Journey::className(), ['start_hotspot_id' => 'id']);
    }

    /**
     * Gets query for [[Journeys0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJourneys0()
    {
        return $this->hasMany(Journey::className(), ['stop_hotspot_id' => 'id']);
    }

    /**
     * Gets query for [[FirstCar]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFirstCar()
    {
        return $this->hasOne(Car::className(), ['id' => 'first_car_id']);
    }

    /**
     * Gets query for [[LocationType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLocationType()
    {
        return $this->hasOne(LocationType::className(), ['id' => 'location_type_id']);
    }
}
