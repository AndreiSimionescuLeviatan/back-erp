<?php

namespace api\modules\v2\models;

class BeMariaQuestionFeedback extends BeMariaQuestionFeedbackParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_BEMARIA . '.question_feedback';
    }
}
