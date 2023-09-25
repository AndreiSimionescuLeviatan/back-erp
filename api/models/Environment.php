<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "environment".
 */
class Environment extends EnvironmentParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_PMP . '.environment';
    }
}
