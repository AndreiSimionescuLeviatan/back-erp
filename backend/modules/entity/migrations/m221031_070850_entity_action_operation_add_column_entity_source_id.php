<?php

use yii\db\Migration;

/**
 * Class m221031_070850_entity_action_operation_add_column_entity_source_id
 */
class m221031_070850_entity_action_operation_add_column_entity_source_id extends Migration
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
        $filePath = Yii::getAlias('@backend/modules/entity/migrations/sql_files/m221031_070850_entity_action_operation_add_column_entity_source_id.sql');
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
