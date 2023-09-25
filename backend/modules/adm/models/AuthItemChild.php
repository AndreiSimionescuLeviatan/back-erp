<?php

namespace backend\modules\adm\models;

class AuthItemChild extends AuthItemChildParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_INITIAL . '.auth_item_child';
    }
}