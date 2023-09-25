<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "car_accessory".
 *
 * @property int $id
 * @property int $car_id
 * @property int $accessory_id
 * @property int $accessory_qty
 * @property string|null $observation
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Accessory $accessory
 * @property Car $car
 */
class CarAccessoryParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'car_accessory';
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
            [['car_id', 'accessory_id', 'accessory_qty', 'added', 'added_by'], 'required'],
            [['car_id', 'accessory_id', 'accessory_qty', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['observation'], 'string', 'max' => 255],
            [['accessory_id'], 'exist', 'skipOnError' => true, 'targetClass' => Accessory::className(), 'targetAttribute' => ['accessory_id' => 'id']],
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
            'accessory_id' => Yii::t('app', 'Accessory ID'),
            'accessory_qty' => Yii::t('app', 'Accessory Qty'),
            'observation' => Yii::t('app', 'Observation'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Accessory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccessory()
    {
        return $this->hasOne(Accessory::className(), ['id' => 'accessory_id']);
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
