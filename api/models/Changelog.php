<?php

namespace api\models;

/**
 * This is the model class for table "changelog".
 */
class Changelog extends ChangelogParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_PMP . '.changelog';
    }
}
