<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "meeting_attendee".
 *
 * @property int $id
 * @property string $meeting_common_identifier see col comments in room_reservation table
 * @property int $user_id
 */
class MeetingAttendeeParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meeting_attendee';
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
            [['meeting_common_identifier', 'user_id'], 'required'],
            [['user_id'], 'integer'],
            [['meeting_common_identifier'], 'string', 'max' => 16],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-logistic', 'ID'),
            'meeting_common_identifier' => Yii::t('api-logistic', 'Meeting Common Identifier'),
            'user_id' => Yii::t('api-logistic', 'User ID'),
        ];
    }
}
