<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "product_component".
 */
class ProductComponent extends ProductComponentParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_PMP . '.product_component';
    }
}
