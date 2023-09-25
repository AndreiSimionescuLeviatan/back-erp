<?php

use yii\db\Migration;

/**
 * Class m220405_122555_init_company_table
 */
class m220405_122555_init_company_table extends Migration
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

        $this->createTable('{{%company}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(255)->unique()->notNull(),
            'name' => $this->string(255)->notNull(),
            'short_name' => $this->string(255)->null(),
            'cui' => $this->char(255)->notNull(),
            'reg_number' => $this->char(255)->notNull(),
            'country_id' => $this->integer(11)->null(),
            'state_id' => $this->integer(11)->null(),
            'city_id' => $this->integer(11)->null(),
            'address' => $this->char(255)->null(),
            'tva' => $this->tinyInteger(1)->null()->defaultValue(0),
            'deleted' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'added' => $this->dateTime()->notNull(),
            'added_by' => $this->integer(11)->notNull(),
            'updated' => $this->dateTime()->null(),
            'updated_by' => $this->integer(11)->null(),
        ], $tableOptions);

        $this->createIndex(
            'idx-company-country_id',
            'company',
            'country_id'
        );
        $this->createIndex(
            'idx-company-state_id',
            'company',
            'state_id'
        );
        $this->createIndex(
            'company-city_id',
            'company',
            'city_id'
        );

        // add foreign key for table `company.country_id`
        $this->addForeignKey(
            'fk-company-country_id',
            'company',
            'country_id',
            'ecf_location.' . 'country',
            'id',
            'NO ACTION',
            'NO ACTION'
        );
        // add foreign key for table `company.state_id`
        $this->addForeignKey(
            'fk-company-state_id',
            'company',
            'state_id',
            'ecf_location.' . 'state',
            'id',
            'NO ACTION',
            'NO ACTION'
        );
        // add foreign key for table `company.city_id`
        $this->addForeignKey(
            'fk-company-city_id',
            'company',
            'city_id',
            'ecf_location.' . 'city',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        $filePath = Yii::getAlias('@backend/modules/crm/migrations/sql_files/crm_company.sql');
        $sql = file_get_contents($filePath);
        $this->execute("{$sql}");
    }

    public function safeDown()
    {
        $this->dropTable('{{%company}}');
    }
}
