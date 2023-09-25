<?php

namespace api\models;

/**
 * This is the model class for table "permission_day".
 */
class PermissionDay extends PermissionDayParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.permission_day';
    }
}
