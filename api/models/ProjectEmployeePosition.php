<?php

namespace api\models;

/**
 * This is the model class for table "project_employee_position".
 */
class ProjectEmployeePosition extends ProjectEmployeePositionParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_PMP . '.project_employee_position';
    }
}
