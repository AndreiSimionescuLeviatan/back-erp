<?php

use yii\db\Migration;

/**
 * Class m230309_074648_alter_table_entity_domain
 */
class m230309_074648_alter_table_entity_domain extends Migration
{
    public function init()
    {
        $this->db = 'ecf_crm_db';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $filePath = Yii::getAlias('@backend/modules/crm/migrations/sql_files/m230309_074648_alter_table_entity_domain.sql');
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
