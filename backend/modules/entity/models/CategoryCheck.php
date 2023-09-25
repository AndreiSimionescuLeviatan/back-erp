<?php

namespace backend\modules\entity\models;

use Yii;

class CategoryCheck extends CategoryCheckParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_ENTITY . '.category_check';
    }
}
