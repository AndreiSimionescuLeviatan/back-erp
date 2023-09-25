<?php

use yii\db\Migration;

/**
 * Class m220510_084947_init_ecf_crm_iban_company_table
 */
class m220510_084947_init_ecf_crm_iban_company_table extends Migration
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

        $this->createTable('{{%iban_company}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(11)->notNull(),
            'iban' => $this->string(64)->null()->unique(),
            'deleted' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'added' => $this->dateTime()->notNull(),
            'added_by' => $this->integer(11)->notNull(),
            'updated' => $this->dateTime()->null(),
            'updated_by' => $this->integer(11)->null(),
        ], $tableOptions);

        $this->createIndex(
            'idx-iban_company-company_id',
            'iban_company',
            'company_id'
        );

        $this->addForeignKey(
            'fk-iban_company-company_id',
            'iban_company',
            'company_id',
            'ecf_crm.' . 'company',
            'id',
            'NO ACTION',
            'NO ACTION'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%iban_company}}');
    }
}
