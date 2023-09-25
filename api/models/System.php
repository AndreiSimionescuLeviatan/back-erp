<?php

namespace api\models;

/**
 * This is the model class for table "system".
 */
class System extends SystemParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_PMP . '.system';
    }
}
