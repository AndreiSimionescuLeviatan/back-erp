<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "meeting_attendee".
 */
class MeetingAttendee extends MeetingAttendeeParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_LOGISTIC . '.meeting_attendee';
    }
}
