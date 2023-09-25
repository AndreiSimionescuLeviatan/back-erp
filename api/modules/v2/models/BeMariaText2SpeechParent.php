<?php

namespace api\modules\v2\models;

use Yii;

/**
 * This is the model class for table "text2speech".
 *
 * @property int $id
 * @property int|null $device_id
 * @property string $text
 * @property string|null $ip_address
 * @property int $deleted
 * @property string $added
 * @property int|null $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class BeMariaText2SpeechParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'text2speech';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_bemaria_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['device_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['text', 'added'], 'required'],
            [['added', 'updated'], 'safe'],
            [['text'], 'string', 'max' => 5120],
            [['ip_address'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('bemaria-api', 'ID'),
            'device_id' => Yii::t('bemaria-api', 'Device ID'),
            'text' => Yii::t('bemaria-api', 'Text'),
            'ip_address' => Yii::t('bemaria-api', 'Ip Address'),
            'deleted' => Yii::t('bemaria-api', 'Deleted'),
            'added' => Yii::t('bemaria-api', 'Added'),
            'added_by' => Yii::t('bemaria-api', 'Added By'),
            'updated' => Yii::t('bemaria-api', 'Updated'),
            'updated_by' => Yii::t('bemaria-api', 'Updated By'),
        ];
    }
}
