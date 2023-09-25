<?php

use yii\db\Migration;

/**
 * Class m220617_111519_init_new_contract_offer_table
 */
class m220617_111519_init_new_contract_offer_table extends Migration
{
    public function init()
    {
        $this->db = 'ecf_crm_db';
        parent::init();
    }

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%contract_offer}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(11)->notNull(),
            'code' => $this->string(64)->null(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->string(4096)->null(),
            'deleted' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'added' => $this->dateTime()->notNull(),
            'added_by' => $this->integer(11)->notNull(),
            'updated' => $this->dateTime()->null(),
            'updated_by' => $this->integer(11)->null(),
        ], $tableOptions);

        $this->createIndex(
            'idx-contract_offer-company_id',
            'contract_offer',
            'company_id'
        );

        $this->addForeignKey(
            'fk-contract_offer-company_id',
            'contract_offer',
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
        $this->dropTable('{{%contract_offer}}');
    }
}
