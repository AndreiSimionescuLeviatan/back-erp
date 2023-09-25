<?php

use yii\db\Migration;

/**
 * Class m230302_130617_init_ecf_adm_user_signature
 */
class m230302_130617_init_ecf_adm_user_signature extends Migration
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
        $filePath = Yii::getAlias('@backend/modules/adm/migrations/sql_files/m230302_130617_init_ecf_adm_user_signature.sql');
        $sql = file_get_contents($filePath);
        $this->execute("{$sql}");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_signature}}');
    }
}
