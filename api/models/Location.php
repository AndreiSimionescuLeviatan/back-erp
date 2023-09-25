<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "location".
 */
class Location extends LocationParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_AUTO . '.location';
    }

    public function getName()
    {
        if (preg_match("/^HotSpot-\d+$/", $this->name)) {
            return $this->address;
        }
        return $this->name;
    }
}
