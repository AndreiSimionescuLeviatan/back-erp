<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "device_details_data".
 *
 * @property int $id
 * @property int $device_id
 * @property int $device_details_id
 * @property string|null $data
 * @property int $deleted
 * @property string $added
 * @property int|null $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property DeviceDetails $deviceDetails
 * @property Device $device
 */
class DeviceDetailsDataParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'device_details_data';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_pmp_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['device_id', 'device_details_id', 'added'], 'required'],
            [['device_id', 'device_details_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['data'], 'string', 'max' => 255],
            [['device_details_id'], 'exist', 'skipOnError' => true, 'targetClass' => DeviceDetails::className(), 'targetAttribute' => ['device_details_id' => 'id']],
            [['device_id'], 'exist', 'skipOnError' => true, 'targetClass' => Device::className(), 'targetAttribute' => ['device_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'device_id' => Yii::t('app', 'Device ID'),
            'device_details_id' => Yii::t('app', 'Device Details ID'),
            'data' => Yii::t('app', 'Data'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[DeviceDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeviceDetails()
    {
        return $this->hasOne(DeviceDetails::className(), ['id' => 'device_details_id']);
    }

    /**
     * Gets query for [[Device]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDevice()
    {
        return $this->hasOne(Device::className(), ['id' => 'device_id']);
    }
}
