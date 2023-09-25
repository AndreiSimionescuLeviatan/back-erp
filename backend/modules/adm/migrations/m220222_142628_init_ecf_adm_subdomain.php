<?php

use yii\db\Migration;

/**
 * Class m220222_142628_init_ecf_adm_subdomain
 */
class m220222_142628_init_ecf_adm_subdomain extends Migration
{
    public function init()
    {
        $this->db = 'ecf_adm_db';
        parent::init();
    }

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%subdomain}}', [
            'id' => $this->primaryKey(),
            'domain_id' => $this->integer(11)->notNull(),
            'entity_id' => $this->integer(11)->notNull(),
            'name' => $this->string(32)->unique()->notNull(),
            'deleted' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'added' => $this->dateTime()->notNull(),
            'added_by' => $this->integer(11)->notNull(),
            'updated' => $this->dateTime()->null(),
            'updated_by' => $this->integer(11)->null(),
        ], $tableOptions);

        $this->createIndex(
            'idx-subdomain-domain_id',
            'subdomain',
            'domain_id'
        );

        $this->createIndex(
            'idx-subdomain-entity_id',
            'subdomain',
            'entity_id'
        );

        $this->addForeignKey(
            'fk-subdomain-domain_id',
            'subdomain',
            'domain_id',
            'ecf_adm.' . 'domain',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk-subdomain-entity_id',
            'subdomain',
            'entity_id',
            'ecf_adm.' . 'entity',
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
        $this->dropTable('{{%subdomain}}');
    }
}