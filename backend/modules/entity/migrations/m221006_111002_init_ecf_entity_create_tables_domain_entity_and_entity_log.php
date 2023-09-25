<?php

use yii\db\Migration;

/**
 * Class m221006_111002_init_ecf_entity_create_tables_domain_entity_and_entity_log
 */
class m221006_111002_init_ecf_entity_create_tables_domain_entity_and_entity_log extends Migration
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
        $filePath = Yii::getAlias('@backend/modules/entity/migrations/sql_files/m221006_111002_init_ecf_entity_create_tables_domain_entity_and_entity_log.sql');
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
