<?php

namespace api\models;

/**
 * This is the model class for table "accessory".
 */
class Accessory extends AccessoryParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_AUTO . '.accessory';
    }
}
