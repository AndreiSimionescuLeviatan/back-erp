<?php

namespace api\models;

/**
 * This is the model class for table "management_position".
 */
class ManagementPosition extends ManagementPositionParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.management_position';
    }
}
