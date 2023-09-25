<?php

namespace api\modules\v2\models;

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



    /**
     * return templates list
     * @return array[]
     */
    public static function getTemplates()
    {
        return [
            [
                'id' => 1,
                'name' => Yii::t('logistic', 'Purple Template'),
                'file_name' => 'purple-template.css'
            ],
            [
                'id' => 2,
                'name' => Yii::t('logistic', 'Green Template'),
                'file_name' => 'green-template.css'
            ],
            [
                'id' => 3,
                'name' => Yii::t('logistic', 'Gray Template'),
                'file_name' => 'gray-template.css'
            ],
            [
                'id' => 4,
                'name' => Yii::t('logistic', 'Lime Green Template'),
                'file_name' => 'lime-green-template.css'
            ],
        ];
    }
}
