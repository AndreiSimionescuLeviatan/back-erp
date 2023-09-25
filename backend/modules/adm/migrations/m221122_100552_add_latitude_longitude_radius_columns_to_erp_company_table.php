<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%erp_company}}`.
 */
class m221122_100552_add_latitude_longitude_radius_columns_to_erp_company_table extends Migration
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
        $filePath = Yii::getAlias('@backend/modules/adm/migrations/sql_files/m221122_100552_add_latitude_longitude_radius_columns_to_erp_company_table.sql');
        $sql = file_get_contents($filePath);
        $this->execute("{$sql}");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221122_100552_add_latitude_longitude_radius_columns_to_erp_company_table cannot be reverted.\n";
        return false;
    }
}
