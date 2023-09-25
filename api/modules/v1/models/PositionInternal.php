<?php

namespace api\modules\v1\models;

/**
 * This is the model class that extends the "PositionInternalParent" class.
 */
class PositionInternal extends PositionInternalParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.position_internal';
    }
}
