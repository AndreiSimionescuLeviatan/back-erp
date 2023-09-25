<?php

use yii\db\Migration;

/**
 * Class m230511_125221_change_radius_column_type_in_erp_company_table
 */
class m230511_125221_change_radius_column_type_in_erp_company_table extends Migration
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
        $filePath = Yii::getAlias('@backend/modules/adm/migrations/sql_files/m230511_125221_change_radius_column_type_in_erp_company_table.sql');
        $sql = file_get_contents($filePath);
        $this->execute("{$sql}");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230511_125221_change_radius_column_type_in_erp_company_table cannot be reverted.\n";

        return false;
    }
}
