<?php

use yii\db\Migration;

/**
 * Class m230327_054432_insert_into_entity_action_operation_default_values
 */
class m230327_054432_insert_into_entity_action_operation_default_values extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->db = 'ecf_entity_db';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $filePath = Yii::getAlias('@backend/modules/entity/migrations/sql_files/insert_into_entity_action_operation_default_values.sql');
        $sql = file_get_contents($filePath);
        $this->execute("{$sql}");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }
}
