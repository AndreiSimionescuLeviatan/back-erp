<?php

use yii\db\Migration;

/**
 * Class m230420_092933_remove_company_nexus_api_key_table
 */
class m230420_092933_remove_company_nexus_api_key_table extends Migration
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
        $filePath = Yii::getAlias('@backend/modules/crm/migrations/sql_files/m230420_092933_remove_company_nexus_api_key_table.sql');
        $sql = file_get_contents($filePath);
        $this->execute("{$sql}");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230420_092933_remove_company_nexus_api_key_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230420_092933_remove_company_nexus_api_key_table cannot be reverted.\n";

        return false;
    }
    */
}
