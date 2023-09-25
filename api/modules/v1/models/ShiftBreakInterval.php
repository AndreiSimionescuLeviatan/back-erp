<?php

namespace api\modules\v1\models;

/**
 * This is the model class that extends the "ShiftBreakIntervalParent".
 */
class ShiftBreakInterval extends ShiftBreakIntervalParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.shift_break_interval';
    }
}
