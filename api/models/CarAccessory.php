<?php

namespace api\models;

/**
 * This is the model class for table "car_accessory".
 */
class CarAccessory extends CarAccessoryParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_AUTO . '.car_accessory';
    }
}
