<?php

namespace api\modules\v1\models;

use Yii;

class EntityDomain extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_CRM . '.entity_domain';
    }
}
