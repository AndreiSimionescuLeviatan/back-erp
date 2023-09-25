<?php

use yii\db\Migration;

/**
 * Class m220422_074800_init_settings_table
 */
class m220422_074800_init_settings_table extends Migration
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

        $this->createTable('{{%settings}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(128)->unique()->notNull(),
            'description' => $this->text()->null(),
            'value' => $this->string(128)->notNull(),
            'added' => $this->dateTime()->notNull(),
            'added_by' => $this->integer(11)->notNull(),
            'updated' => $this->dateTime()->null(),
            'updated_by' => $this->integer(11)->null(),
        ], $tableOptions);

        $this->batchInsert('settings', ['name', 'description', 'value', 'added', 'added_by', 'updated', 'updated_by'], [
            [
                'REPEAT_CHECKLIST',
                'Variabila arata daca putem sau nu sa repetam checklistul. 0 - Checlistul nu poate fi repetat. 1 - Checklistul poate fi repetat',
                '0', date('Y-m-d H:i:s'), 52, null, null
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%settings}}');
    }
}
