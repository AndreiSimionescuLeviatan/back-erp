<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "meeting_room".
 *
 * @property int $id
 * @property string $name
 * @property int $capacity
 * @property string $details Details about room equipments and other details
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class MeetingRoom extends MeetingRoomParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_LOGISTIC . '.meeting_room';
    }
}
