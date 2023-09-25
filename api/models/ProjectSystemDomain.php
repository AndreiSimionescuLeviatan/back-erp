<?php

namespace api\models;

/**
 * This is the model class for table "project_system_domain".
 */
class ProjectSystemDomain extends ProjectSystemDomainParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_PMP . '.project_system_domain';
    }
}
