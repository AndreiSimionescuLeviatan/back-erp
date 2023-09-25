<?php

use yii\db\Migration;

/**
 * Class m220607_074737_init_access_token
 */
class m220607_074737_init_access_token extends Migration
{

    public function init()
    {
        $this->db = 'ecf_adm_db';
        parent::init();
    }

    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%access_token}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'consumer' => $this->string()->null()->comment('We are not using this column for the moment, will decide later and if is useless will remove it'),
            'token' => $this->string(32)->notNull()->unique(),
            'access_given' => $this->json()->null()->comment('We are not using this column for the moment, will decide later and if is useless will remove it'),
            'last_used_at' => $this->integer()->comment('also known as `last_seen`'),
            'expire_at' => $this->integer(),
            'added' => $this->dateTime()->notNull(),
            'updated' => $this->dateTime()->null(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%access_token}}');
    }
}
