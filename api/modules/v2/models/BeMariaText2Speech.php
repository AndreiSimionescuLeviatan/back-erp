<?php

namespace api\modules\v2\models;

class BeMariaText2Speech extends BeMariaText2SpeechParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_BEMARIA . '.text2speech';
    }
}
