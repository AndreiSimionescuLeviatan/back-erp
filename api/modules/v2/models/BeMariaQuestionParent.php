<?php

namespace api\modules\v2\models;

use Yii;

/**
 * This is the model class for table "question".
 *
 * @property int $id
 * @property int|null $device_id
 * @property string $question
 * @property string|null $answer
 * @property int $status 0-new;1-success;2-sent2cgpt;3-error
 * @property string|null $observations
 * @property string|null $ip_address IP address
 * @property int $deleted
 * @property string $added
 * @property int|null $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class BeMariaQuestionParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'question';
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
            [['device_id', 'status', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['question', 'added'], 'required'],
            [['question', 'answer'], 'string'],
            [['added', 'updated'], 'safe'],
            [['observations'], 'string', 'max' => 5120],
            [['ip_address'], 'string', 'max' => 16],
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
            'question' => Yii::t('bemaria-api', 'Question'),
            'answer' => Yii::t('bemaria-api', 'Answer'),
            'status' => Yii::t('bemaria-api', 'Status'),
            'observations' => Yii::t('bemaria-api', 'Observations'),
            'ip_address' => Yii::t('bemaria-api', 'Ip Address'),
            'deleted' => Yii::t('bemaria-api', 'Deleted'),
            'added' => Yii::t('bemaria-api', 'Added'),
            'added_by' => Yii::t('bemaria-api', 'Added By'),
            'updated' => Yii::t('bemaria-api', 'Updated'),
            'updated_by' => Yii::t('bemaria-api', 'Updated By'),
        ];
    }
}
