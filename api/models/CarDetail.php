<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "car_detail".
 *
 * @property int $id
 * @property int $car_id
 * @property int|null $fuel_ring_company_id
 * @property int|null $fuel_card_company_id
 * @property int|null $gps_company_id
 * @property string|null $observations
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Car $car
 */
class CarDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_AUTO . '.car_detail';
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
            [['car_id', 'fuel_ring_company_id', 'fuel_card_company_id', 'gps_company_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['observations'], 'string'],
            [['added', 'updated'], 'safe'],
            [['car_id'], 'exist', 'skipOnError' => true, 'targetClass' => Car::className(), 'targetAttribute' => ['car_id' => 'id']],
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
            'fuel_ring_company_id' => Yii::t('app', 'Fuel Ring Company ID'),
            'fuel_card_company_id' => Yii::t('app', 'Fuel Card Company ID'),
            'gps_company_id' => Yii::t('app', 'Gps Company ID'),
            'observations' => Yii::t('app', 'Observations'),
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
}
