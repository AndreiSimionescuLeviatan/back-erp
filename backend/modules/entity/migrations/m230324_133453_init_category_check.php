<?php

use yii\db\Migration;

/**
 * Class m230324_133453_init_action_category
 */
class m230324_133453_init_category_check extends Migration
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
        $filePath = Yii::getAlias('@backend/modules/entity/migrations/sql_files/m230324_133453_init_category_check.sql');
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
