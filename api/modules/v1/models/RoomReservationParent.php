<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "room_reservation".
 *
 * @property int $id
 * @property int $room_id
 * @property string $common_identifier this is a common identifier(time()) between meetings for cases when we have a recurring reservation. using this identifier we will retrieve meetings common parts like attendees, ...
 * @property string $title
 * @property int $all_day
 * @property int $recurring
 * @property string|null $recurrence_frequency If is a recurrent meeting the allowed values are: YEARLY, MONTHLY, WEEKLY, DAILY, HOURLY, MINUTELY, SECONDLY
 * @property string|null $recurrence_weekday list with days number in witch the meeting is repeating
 * @property int $recurrence_interval The interval between each freq iteration. For example, when using the recurrence_frequency with value YEARLY, an interval of 2 means once every two years, but with HOURLY, it means once every two hours. The default interval is 1
 * @property string|null $recurrent_from
 * @property string|null $recurrent_until If not null, this must be a Date instance, that will specify the limit of the recurrence. If a recurrence instance happens to be the same as the Date recurrent_until argument, this will be the last occurrence.
 * @property string $check_in
 * @property string $check_out
 * @property string $duration
 * @property int|null $recurrence_count The recurrent_until and recurrent_count rule parts MUST NOT occur in the same rule
 * @property string $rfc_string
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class RoomReservationParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'room_reservation';
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
            [['room_id', 'common_identifier', 'title', 'check_in', 'check_out', 'duration', 'rfc_string', 'added', 'added_by'], 'required'],
            [['room_id', 'all_day', 'recurring', 'recurrence_interval', 'recurrence_count', 'added_by', 'updated_by'], 'integer'],
            [['recurrent_from', 'recurrent_until', 'added', 'updated'], 'safe'],
            [['rfc_string'], 'string'],
            [['common_identifier', 'recurrence_frequency', 'recurrence_weekday'], 'string', 'max' => 16],
            [['title'], 'string', 'max' => 255],
            [['check_in', 'check_out', 'duration'], 'string', 'max' => 8],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-logistic', 'ID'),
            'room_id' => Yii::t('api-logistic', 'Room ID'),
            'common_identifier' => Yii::t('api-logistic', 'Common Identifier'),
            'title' => Yii::t('api-logistic', 'Title'),
            'all_day' => Yii::t('api-logistic', 'All Day'),
            'recurring' => Yii::t('api-logistic', 'Recurring'),
            'recurrence_frequency' => Yii::t('api-logistic', 'Recurrence Frequency'),
            'recurrence_weekday' => Yii::t('api-logistic', 'Recurrence Weekday'),
            'recurrence_interval' => Yii::t('api-logistic', 'Recurrence Interval'),
            'recurrent_from' => Yii::t('api-logistic', 'Recurrent From'),
            'recurrent_until' => Yii::t('api-logistic', 'Recurrent Until'),
            'check_in' => Yii::t('api-logistic', 'Check In'),
            'check_out' => Yii::t('api-logistic', 'Check Out'),
            'duration' => Yii::t('api-logistic', 'Duration'),
            'recurrence_count' => Yii::t('api-logistic', 'Recurrence Count'),
            'rfc_string' => Yii::t('api-logistic', 'Rfc String'),
            'added' => Yii::t('api-logistic', 'Added'),
            'added_by' => Yii::t('api-logistic', 'Added By'),
            'updated' => Yii::t('api-logistic', 'Updated'),
            'updated_by' => Yii::t('api-logistic', 'Updated By'),
        ];
    }
}
