<?php

namespace api\models;

use backend\modules\auto\models\Fuel;
use Yii;

/**
 * This is the model class for table "car".
 *
 * @property int $id
 * @property int|null $gps_car_id
 * @property string $plate_number
 * @property int $brand_id
 * @property int $model_id
 * @property string|null $vin
 * @property int $fabrication_year
 * @property int $fuel_id
 * @property float|null $medium_consumption
 * @property int $company_id
 * @property string|null $contract_number
 * @property int $acquisition_type
 * @property string $color
 * @property int|null $holder_id Proprietarul masinii in acte
 * @property int|null $user_id Utilizatorul masinii
 * @property int $status 0-Available, 1-In curs de predare/primire
 * @property int $deleted
 * @property int $odo
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Fuel $fuel
 * @property CarAccessory[] $carAccessories
 * @property CarDetail[] $carDetails
 * @property CarDocument[] $carDocuments
 * @property CarOperation[] $carOperations
 * @property CarZone[] $carZones
 * @property CarZoneHistory[] $carZoneHistories
 * @property Journey[] $journeys
 * @property Location[] $locations
 */
class CarParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'car';
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
            [['gps_car_id', 'brand_id', 'model_id', 'fabrication_year', 'fuel_id', 'company_id', 'acquisition_type', 'holder_id', 'user_id', 'status', 'deleted', 'odo', 'added_by', 'updated_by'], 'integer'],
            [['plate_number', 'brand_id', 'model_id', 'fabrication_year', 'fuel_id', 'company_id', 'acquisition_type', 'color', 'added', 'added_by'], 'required'],
            [['medium_consumption'], 'number'],
            [['added', 'updated'], 'safe'],
            [['plate_number'], 'string', 'max' => 16],
            [['vin', 'contract_number', 'color'], 'string', 'max' => 32],
            [['plate_number'], 'unique'],
            [['vin'], 'unique'],
            [['fuel_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fuel::className(), 'targetAttribute' => ['fuel_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'gps_car_id' => Yii::t('app', 'GPS Car ID'),
            'plate_number' => Yii::t('app', 'Plate Number'),
            'brand_id' => Yii::t('app', 'Brand ID'),
            'model_id' => Yii::t('app', 'Model ID'),
            'vin' => Yii::t('app', 'Vin'),
            'fabrication_year' => Yii::t('app', 'Fabrication Year'),
            'fuel_id' => Yii::t('app', 'Fuel ID'),
            'medium_consumption' => Yii::t('auto', 'Medium Consumption'),
            'company_id' => Yii::t('app', 'Company ID'),
            'contract_number' => Yii::t('app', 'Contract Number'),
            'acquisition_type' => Yii::t('app', 'Acquisition Type'),
            'color' => Yii::t('app', 'Color'),
            'holder_id' => Yii::t('app', 'Holder ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'status' => Yii::t('app', 'Status'),
            'deleted' => Yii::t('app', 'Deleted'),
            'odo' => Yii::t('app', 'Odo'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Fuel]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFuel()
    {
        return $this->hasOne(Fuel::className(), ['id' => 'fuel_id']);
    }

    /**
     * Gets query for [[CarAccessories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarAccessories()
    {
        return $this->hasMany(CarAccessory::className(), ['car_id' => 'id']);
    }

    /**
     * Gets query for [[CarDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarDetails()
    {
        return $this->hasMany(CarDetail::className(), ['car_id' => 'id']);
    }

    /**
     * Gets query for [[CarDocuments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarDocuments()
    {
        return $this->hasMany(CarDocument::className(), ['car_id' => 'id']);
    }

    /**
     * Gets query for [[CarOperations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarOperations()
    {
        return $this->hasMany(CarOperation::className(), ['car_id' => 'id']);
    }

    /**
     * Gets query for [[CarZones]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarZones()
    {
        return $this->hasMany(CarZone::className(), ['car_id' => 'id']);
    }

    /**
     * Gets query for [[CarZoneHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarZoneHistories()
    {
        return $this->hasMany(CarZoneHistory::className(), ['car_id' => 'id']);
    }

    /**
     * Gets query for [[Journeys]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJourneys()
    {
        return $this->hasMany(Journey::className(), ['car_id' => 'id']);
    }

    /**
     * Gets query for [[Locations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLocations()
    {
        return $this->hasMany(Location::className(), ['first_car_id' => 'id']);
    }
}
