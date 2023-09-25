<?php

namespace api\modules\v2\models;

/**
 * This is the model class for table "question".
 */
class BeMariaQuestion extends BeMariaQuestionParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_BEMARIA . '.question';
    }
}
