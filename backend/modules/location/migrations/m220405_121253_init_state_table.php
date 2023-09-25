<?php

use yii\db\Migration;

/**
 * Class m220405_121253_init_state_table
 */
class m220405_121253_init_state_table extends Migration
{
    public function init()
    {
        $this->db = 'ecf_location_db';
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

        $this->createTable('{{%state}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->notNull(),
            'code' => $this->string(64)->notNull(),
            'country_id' => $this->integer(11)->notNull(),
            'country_code' => $this->string(64)->notNull(),
            'deleted' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'added' => $this->dateTime()->notNull(),
            'added_by' => $this->integer(11)->notNull(),
            'updated' => $this->dateTime()->null(),
            'updated_by' => $this->integer(11)->null(),
        ], $tableOptions);

        $this->createIndex(
            'idx-state-country_id',
            'state',
            'country_id'
        );

        // add foreign key for table `state.country_id`
        $this->addForeignKey(
            'fk-state-country_id',
            'state',
            'country_id',
            'ecf_location.' . 'country',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        $filePath = Yii::getAlias('@backend/modules/location/migrations/sql_files/location_states.sql');
        $sql = file_get_contents($filePath);
        $this->execute("{$sql}");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%state}}');
    }
}
