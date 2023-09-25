<?php

use yii\db\Migration;

/**
 * Class m230322_070620_init_user_erp_company_table
 */
class m230322_070620_init_user_erp_company_table extends Migration
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
        $filePath = Yii::getAlias('@backend/modules/adm/migrations/sql_files/m230322_070620_init_user_erp_company_table.sql');
        $sql = file_get_contents($filePath);
        $this->execute("{$sql}");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_erp_company}}');
    }
}
