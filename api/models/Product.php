<?php

namespace api\models;


/**
 * This is the model class for table "product".
 */
class Product extends ProductParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_PMP . '.product';
    }
}
