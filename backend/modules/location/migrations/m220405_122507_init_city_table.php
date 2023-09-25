<?php

use yii\db\Migration;

/**
 * Class m220405_122507_init_city_table
 */
class m220405_122507_init_city_table extends Migration
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

        $this->createTable('{{%city}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->notNull(),
            'country_id' => $this->integer(11)->notNull(),
            'country_code' => $this->string(64)->notNull(),
            'state_id' => $this->integer(11)->notNull(),
            'state_code' => $this->string(64)->notNull(),
            'deleted' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'added' => $this->dateTime()->notNull(),
            'added_by' => $this->integer(11)->notNull(),
            'updated' => $this->dateTime()->null(),
            'updated_by' => $this->integer(11)->null(),
        ], $tableOptions);


        $this->createIndex(
            'idx-city-country_id',
            'city',
            'country_id'
        );

        $this->createIndex(
            'idx-city-state_id',
            'city',
            'state_id'
        );

        // add foreign key for table `city.country_id`
        $this->addForeignKey(
            'fk-city-country_id',
            'city',
            'country_id',
            'ecf_location.' . 'country',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        // add foreign key for table `city.state_id`
        $this->addForeignKey(
            'fk-city-state_id',
            'city',
            'state_id',
            'ecf_location.' . 'state',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        $filePath = Yii::getAlias('@backend/modules/location/migrations/sql_files/location_city-ro-only.sql');
        $sql = file_get_contents($filePath);
        $this->execute("{$sql}");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%city}}');
    }
}
