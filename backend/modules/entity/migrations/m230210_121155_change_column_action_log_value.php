<?php

use yii\db\Migration;

/**
 * Class m230210_121155_change_column_action_log_value
 */
class m230210_121155_change_column_action_log_value extends Migration
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
        $filePath = Yii::getAlias('@backend/modules/entity/migrations/sql_files/m230210_121155_change_column_action_log_value.sql');
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
