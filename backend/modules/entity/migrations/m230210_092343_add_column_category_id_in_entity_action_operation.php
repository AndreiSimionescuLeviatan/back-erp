<?php

use yii\db\Migration;

/**
 * Class m230210_092343_add_column_category_id_in_entity_action_operation
 */
class m230210_092343_add_column_category_id_in_entity_action_operation extends Migration
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
        $filePath = Yii::getAlias('@backend/modules/entity/migrations/sql_files/m230210_092343_add_column_category_id_in_entity_action_operation.sql');
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
