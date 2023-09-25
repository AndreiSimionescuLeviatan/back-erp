<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "position".
 */
class Position extends PositionParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_PMP . '.position';
    }
}
