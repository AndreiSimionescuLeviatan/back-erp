<?php

namespace api\modules\v1\models;

class WorkLocationPolygon extends WorkLocationPolygonParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.work_location_polygon';
    }
}
