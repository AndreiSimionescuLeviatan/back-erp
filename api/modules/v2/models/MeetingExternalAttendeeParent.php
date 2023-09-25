<?php

namespace api\modules\v2\models;

use Yii;

/**
 * This is the model class for table "meeting_external_attendee".
 *
 * @property int $id
 * @property string $meeting_common_identifier see col comments in room_reservation table
 * @property string $email_address
 */
class MeetingExternalAttendeeParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meeting_external_attendee';
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
            [['meeting_common_identifier', 'email_address'], 'required'],
            [['meeting_common_identifier'], 'string', 'max' => 16],
            [['email_address'], 'string', 'max' => 64],
            [['meeting_common_identifier', 'email_address'], 'unique', 'targetAttribute' => ['meeting_common_identifier', 'email_address']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('logistic', 'ID'),
            'meeting_common_identifier' => Yii::t('logistic', 'Meeting Common Identifier'),
            'email_address' => Yii::t('logistic', 'Email Address'),
        ];
    }
}
