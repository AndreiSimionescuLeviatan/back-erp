<?php

namespace api\modules\v2\models;

use Yii;

/**
 * This is the model class for table "question_feedback".
 *
 * @property int $id
 * @property int|null $device_id
 * @property int $question_id
 * @property int|null $feedback 0-ko;1-ok
 * @property string|null $observations
 * @property string|null $ip_address
 * @property int $deleted
 * @property string $added
 * @property int|null $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class BeMariaQuestionFeedbackParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'question_feedback';
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
            [['device_id', 'question_id', 'feedback', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['question_id', 'added'], 'required'],
            [['added', 'updated'], 'safe'],
            [['observations'], 'string', 'max' => 5120],
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
            'question_id' => Yii::t('bemaria-api', 'Question ID'),
            'feedback' => Yii::t('bemaria-api', 'Feedback'),
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
