<?php

namespace api\models;

/**
 * This is the model class for table "test_scenario".
 */
class TestScenario extends TestScenarioParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_PMP . '.test_scenario';
    }
}
