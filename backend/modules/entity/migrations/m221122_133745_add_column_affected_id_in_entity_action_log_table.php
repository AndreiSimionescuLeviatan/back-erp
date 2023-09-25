<?php

use yii\db\Migration;

/**
 * Class m221122_133745_add_column_affected_id_in_entity_action_log_table
 */
class m221122_133745_add_column_affected_id_in_entity_action_log_table extends Migration
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
        $filePath = Yii::getAlias('@backend/modules/entity/migrations/sql_files/m221122_133745_add_column_affected_id_in_entity_action_log_table.sql');
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
