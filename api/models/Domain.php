<?php

namespace api\models;

/**
 * This is the model class for table "domain".
 */
class Domain extends DomainParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_PMP . '.domain';
    }
}
