<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "meeting_recurrence".
 *
 * @property int $id
 * @property int $meeting_id
 * @property int $room_id
 * @property string $check_in
 * @property string $check_out
 * @property string $recurrence_date
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class MeetingRecurrenceParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meeting_recurrence';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_logistic_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['meeting_id', 'room_id', 'check_in', 'check_out', 'recurrence_date', 'added', 'added_by'], 'required'],
            [['meeting_id', 'room_id', 'added_by', 'updated_by'], 'integer'],
            [['check_in', 'check_out', 'recurrence_date', 'added', 'updated'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-logistic', 'ID'),
            'meeting_id' => Yii::t('api-logistic', 'Meeting ID'),
            'room_id' => Yii::t('api-logistic', 'Room ID'),
            'check_in' => Yii::t('api-logistic', 'Check In'),
            'check_out' => Yii::t('api-logistic', 'Check Out'),
            'recurrence_date' => Yii::t('api-logistic', 'Recurrence Date'),
            'added' => Yii::t('api-logistic', 'Added'),
            'added_by' => Yii::t('api-logistic', 'Added By'),
            'updated' => Yii::t('api-logistic', 'Updated'),
            'updated_by' => Yii::t('api-logistic', 'Updated By'),
        ];
    }
}
