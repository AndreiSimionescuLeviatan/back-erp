<?php

namespace api\models;

/**
 * This is the model class for table "holiday_type".
 */
class HolidayType extends HolidayTypeParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.holiday_type';
    }
}
