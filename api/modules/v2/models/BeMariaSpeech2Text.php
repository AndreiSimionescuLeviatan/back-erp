<?php

namespace api\modules\v2\models;

class BeMariaSpeech2Text extends BeMariaSpeech2TextParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_BEMARIA . '.speech2text';
    }
}
