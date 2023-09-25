<?php

namespace api\modules\v1\models;


class RevitFamilyCategory extends RevitFamilyCategoryParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_REVIT . '.family_category';
    }
}
