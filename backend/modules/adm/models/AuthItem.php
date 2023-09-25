<?php

namespace backend\modules\adm\models;

class AuthItem extends AuthItemParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_INITIAL . '.auth_item';
    }
}