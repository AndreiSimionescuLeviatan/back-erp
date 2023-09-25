<?php

namespace api\models;

/**
 * This is the model class for table "car_operation".
 */
class CarOperation extends CarOperationParent
{

    const CAR_CHECK_IN = 1;
    const CAR_CHECK_OUT = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_AUTO . '.car_operation';
    }
}
