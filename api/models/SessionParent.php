<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "session".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $device_id
 * @property string $token
 * @property string $last_seen
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Device $device
 */
class SessionParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'session';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['device_id', 'added_by', 'updated_by'], 'integer'],
            [['token', 'last_seen', 'added', 'added_by'], 'required'],
            [['last_seen', 'added', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['token'], 'string', 'max' => 64],
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
            'name' => Yii::t('app', 'Name'),
            'device_id' => Yii::t('app', 'Device ID'),
            'token' => Yii::t('app', 'Token'),
            'last_seen' => Yii::t('app', 'Last Seen'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
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
