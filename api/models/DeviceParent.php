<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "device".
 *
 * @property int $id
 * @property string $uuid
 * @property string $owner_id
 * @property int $product_type_id
 * @property int $environment_id
 * @property string|null $product_version
 * @property string|null $current_version
 * @property string|null $last_seen
 * @property string|null $last_seen_ip_lan
 * @property string|null $last_seen_ip_wan
 * @property string|null $first_seen
 * @property string|null $first_seen_ip_lan
 * @property string|null $first_seen_ip_wan
 * @property int $status 0-inactiv; 1-activ
 * @property int $deleted
 * @property string $added
 * @property int|null $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Environment $environment
 * @property ProductType $productType
 * @property DeviceDetails[] $deviceDetails
 * @property DeviceDetailsData[] $deviceDetailsDatas
 * @property DeviceStatus2022[] $deviceStatus2022s
 */
class DeviceParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'device';
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
            [['uuid', 'product_type_id', 'added'], 'required'],
            [['product_type_id', 'environment_id', 'status', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['last_seen', 'first_seen', 'added', 'updated'], 'safe'],
            [['uuid', 'product_version', 'current_version', 'last_seen_ip_lan', 'last_seen_ip_wan', 'first_seen_ip_lan', 'first_seen_ip_wan'], 'string', 'max' => 255],
            [['environment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Environment::className(), 'targetAttribute' => ['environment_id' => 'id']],
            [['product_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductType::className(), 'targetAttribute' => ['product_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'uuid' => Yii::t('app', 'Uuid'),
            'owner_id' => Yii::t('app', 'Owner ID'),
            'product_type_id' => Yii::t('app', 'Product Type ID'),
            'environment_id' => Yii::t('app', 'Environment ID'),
            'product_version' => Yii::t('app', 'Product Version'),
            'current_version' => Yii::t('app', 'Current Version'),
            'last_seen' => Yii::t('app', 'Last Seen'),
            'last_seen_ip_lan' => Yii::t('app', 'Last Seen Ip Lan'),
            'last_seen_ip_wan' => Yii::t('app', 'Last Seen Ip Wan'),
            'first_seen' => Yii::t('app', 'First Seen'),
            'first_seen_ip_lan' => Yii::t('app', 'First Seen Ip Lan'),
            'first_seen_ip_wan' => Yii::t('app', 'First Seen Ip Wan'),
            'status' => Yii::t('app', 'Status'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Environment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEnvironment()
    {
        return $this->hasOne(Environment::className(), ['id' => 'environment_id']);
    }

    /**
     * Gets query for [[ProductType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductType()
    {
        return $this->hasOne(ProductType::className(), ['id' => 'product_type_id']);
    }

    /**
     * Gets query for [[DeviceDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeviceDetails()
    {
        return $this->hasMany(DeviceDetails::className(), ['device_id' => 'id']);
    }

    /**
     * Gets query for [[DeviceDetailsDatas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeviceDetailsDatas()
    {
        return $this->hasMany(DeviceDetailsData::className(), ['device_id' => 'id']);
    }

    /**
     * Gets query for [[DeviceStatus2022s]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeviceStatus2022s()
    {
        return $this->hasMany(DeviceStatus2022::className(), ['device_id' => 'id']);
    }
}
