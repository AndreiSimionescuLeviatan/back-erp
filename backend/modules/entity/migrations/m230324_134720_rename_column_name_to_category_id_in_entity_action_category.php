<?php

use yii\db\Migration;

/**
 * Class m230324_134720_rename_column_name_to_category_id_in_entity_action_category
 */
class m230324_134720_rename_column_name_to_category_id_in_entity_action_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->db = 'ecf_entity_db';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $filePath = Yii::getAlias('@backend/modules/entity/migrations/sql_files/m230324_134720_rename_column_name_to_category_id_in_entity_action_category.sql');
        $sql = file_get_contents($filePath);
        $this->execute("{$sql}");
    }
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }
}
