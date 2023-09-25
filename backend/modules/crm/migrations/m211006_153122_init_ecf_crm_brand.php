<?php

use yii\db\Migration;

/**
 * Class m211006_153122_init_ecf_crm_brand
 */
class m211006_153122_init_ecf_crm_brand extends Migration
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

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%brand}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->unique()->notNull(),
            'deleted' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'added' => $this->dateTime()->notNull(),
            'added_by' => $this->integer(11)->notNull(),
            'updated' => $this->dateTime()->null(),
            'updated_by' => $this->integer(11)->null(),
        ], $tableOptions);

        $this->addCommentOnTable('{{%brand}}', 'This is the table responsible for values from "Furnizor" column in excel files');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%brand}}');
    }
}
