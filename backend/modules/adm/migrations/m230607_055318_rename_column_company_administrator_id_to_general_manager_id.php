<?php

use yii\db\Migration;

/**
 * Class m230607_055318_rename_column_company_administrator_id_to_general_manager_id
 */
class m230607_055318_rename_column_company_administrator_id_to_general_manager_id extends Migration
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
        $filePath = Yii::getAlias('@backend/modules/adm/migrations/sql_files/m230607_055318_rename_column_company_administrator_id_to_general_manager_id.sql');
        $sql = file_get_contents($filePath);
        $this->execute("{$sql}");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230607_055318_rename_column_company_administrator_id_to_general_manager_id cannot be reverted.\n";
    }
}
