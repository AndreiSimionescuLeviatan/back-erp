<?php

use yii\db\Migration;

/**
 * Class m220218_071931_init_ecf_adm_session
 */
class m220218_071931_init_ecf_adm_session extends Migration
{
    public function init()
    {
        $this->db = 'ecf_adm_db';
        parent::init();
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%session}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
            'device_id' => $this->integer(11),
            'token' => $this->string(64)->notNull(),
            'last_seen' => $this->dateTime()->null(),
            'added' => $this->dateTime()->notNull(),
            'added_by' => $this->integer(11)->notNull()->defaultValue(null),
            'updated' => $this->dateTime()->null(),
            'updated_by' => $this->integer(11)->null(),
        ], $tableOptions);

        $this->createIndex(
            'idx-session-device_id',
            'session',
            'device_id'
        );

       // THE REST OF FOREIGN KEYS COULD BE FOUND IN PMP 'm220406_064152_init_device_table'
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%session}}');
    }
}
