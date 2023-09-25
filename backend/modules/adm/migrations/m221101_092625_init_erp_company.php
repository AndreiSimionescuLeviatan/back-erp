<?php

use yii\db\Migration;

/**
 * Class m221101_092625_init_erp_company
 */
class m221101_092625_init_erp_company extends Migration
{
    public function init()
    {
        $this->db = 'ecf_adm_db';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $filePath = Yii::getAlias('@backend/modules/adm/migrations/sql_files/m221101_092625_init_erp_company.sql');
        $sql = file_get_contents($filePath);
        $this->execute("{$sql}");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%erp_company}}');
    }
}
