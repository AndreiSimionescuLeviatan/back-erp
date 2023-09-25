<?php

namespace api\models;

/**
 * This is the model class for table "employee_position".
 *
 * @property int $id
 * @property int $employee_id
 * @property int $position_id
 * @property string $added
 * @property int $added_by
 *
 * @property Employee $employee
 * @property Position $position
 */
class EmployeePosition extends EmployeePositionParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_PMP . '.employee_position';
    }
}
