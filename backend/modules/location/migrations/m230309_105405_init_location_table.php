<?php

use yii\db\Migration;

/**
 * Class m230309_105405_init_location_table
 */
class m230309_105405_init_location_table extends Migration
{
    public function init()
    {
        $this->db = 'ecf_location_db';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $filePath = Yii::getAlias('@backend/modules/location/migrations/sql_files/m230309_105405_init_location_table.sql');
        $sql = file_get_contents($filePath);
        $this->execute("{$sql}");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP TABLE IF EXISTS `location`");
    }
}