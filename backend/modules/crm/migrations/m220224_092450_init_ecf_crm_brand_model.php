<?php

use yii\db\Migration;

/**
 * Class m220224_092450_init_ecf_crm_brand_model
 */
class m220224_092450_init_ecf_crm_brand_model extends Migration
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

        $this->createTable('{{%brand_model}}', [
            'id' => $this->primaryKey(),
            'brand_id' => $this->integer(11)->notNull(),
            'name' => $this->string(32)->unique()->notNull(),
            'deleted' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'added' => $this->dateTime()->notNull(),
            'added_by' => $this->integer(11)->notNull(),
            'updated' => $this->dateTime()->null(),
            'updated_by' => $this->integer(11)->null(),
        ], $tableOptions);

        $this->createIndex(
            'idx-brand_model-brand_id',
            'brand_model',
            'brand_id'
        );

        $this->addForeignKey(
            'fk-brand_model-brand_id',
            'brand_model',
            'brand_id',
            'ecf_crm.' . 'brand',
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
        $this->dropTable('{{%brand_model}}');
    }
}