<?php

use yii\db\Migration;

class m130524_201442_init extends Migration
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

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'first_name' => $this->string(64)->null(),
            'last_name' => $this->string(64)->null(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'verification_token' => $this->string()->null(),
            'email' => $this->string()->notNull()->unique(),
            'photo' => $this->string(255)->unique()->null(),
            'last_auth' => $this->dateTime(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->batchInsert('{{%user}}', [
            'id', 'username', 'first_name', 'last_name', 'auth_key', 'password_hash', 'password_reset_token',
            'verification_token', 'email', 'photo', 'last_auth', 'status', 'created_at', 'updated_at'
        ],
            [
                [
                    -1, 'super.admin', 'super', 'admin', 'L6k3nUh3Un9cMzXnCC7b4OCvQdIKu5Y0', '$2y$13$VNsXdXqR2VQD2GwayswRyeG6iPWC.GfDO1DIgLgI.rVQRbtLs/35m',
                    null, null, 'super.admin@econfaire.ro', null, null, 10, time(), time()
                ],
                [
                    -2, 'api.user', 'api', 'user', 'L6k3nUh3Un9cMzXnCC7b4OCvQdIKu5Y0', '$2y$13$VNsXdXqR2VQD2GwayswRyeG6iPWC.GfDO1DIgLgI.rVQRbtLs/35m',
                    null, null, 'api.user@econfaire.ro', null, null, 10, time(), time()
                ],
                [
                    -3, 'crontab.user', 'crontab', 'user', 'L6k3nUh3Un9cMzXnCC7b4OCvQdIKu5Y0', '$2y$13$VNsXdXqR2VQD2GwayswRyeG6iPWC.GfDO1DIgLgI.rVQRbtLs/35m',
                    null, null, 'crontab.user@econfaire.ro', null, null, 10, time(), time()
                ],
                [
                    -4, 'basic.user', 'basic', 'user', 'L6k3nUh3Un9cMzXnCC7b4OCvQdIKu5Y0', '$2y$13$VNsXdXqR2VQD2GwayswRyeG6iPWC.GfDO1DIgLgI.rVQRbtLs/35m',
                    null, null, 'basic.user@econfaire.ro', null, null, 10, time(), time()
                ]
            ]);
    }

    public function down()
    {
        $this->dropTable('{{%user}}');
    }
}
