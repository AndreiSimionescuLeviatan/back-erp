<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "location_type".
 */
class LocationType extends LocationParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_AUTO . '.location_type';
    }
}
