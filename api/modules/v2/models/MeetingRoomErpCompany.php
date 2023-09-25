<?php

namespace api\modules\v2\models;

/**
 * This is the model class for table "meeting_room_erp_company".
 */
class MeetingRoomErpCompany extends MeetingRoomErpCompanyParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_LOGISTIC . '.meeting_room_erp_company';
    }
}
