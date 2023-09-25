<?php

namespace api\models;

/**
 * This is the model class for table "product_history".
 */
class ProductHistory extends ProductHistoryParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_PMP . '.product_history';
    }
}
