<?php

use yii\db\Migration;

/**
 * Class m220222_144341_init_ecf_crm_entity_domain
 */
class m220222_144341_init_ecf_crm_entity_domain extends Migration
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

        $this->createTable('{{%entity_domain}}', [
            'id' => $this->primaryKey(),
            'domain_id' => $this->integer(11)->notNull(),
            'entity_id' => $this->integer(11)->notNull(),
            'subdomain_id' => $this->integer(11)->notNull(),
            'item_id' => $this->integer(11)->notNull()->comment('Can be any item(article, equipment, company...) from application'),
            'added' => $this->dateTime()->notNull(),
            'added_by' => $this->integer(11)->notNull()
        ], $tableOptions);

        $this->createIndex(
            'idx-entity_domain-domain_id',
            'entity_domain',
            'domain_id'
        );

        $this->createIndex(
            'idx-entity_domain-entity_id',
            'entity_domain',
            'entity_id'
        );

        $this->createIndex(
            'idx-entity_domain-subdomain_id',
            'entity_domain',
            'subdomain_id'
        );

        $this->addForeignKey(
            'fk-entity_domain-domain_id',
            'entity_domain',
            'domain_id',
            'ecf_adm.' . 'domain',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk-entity_domain-entity_id',
            'entity_domain',
            'entity_id',
            'ecf_adm.' . 'entity',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk-entity_domain-subdomain_id',
            'entity_domain',
            'subdomain_id',
            'ecf_adm.' . 'subdomain',
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
        $this->dropTable('{{%entity_domain}}');
    }
}