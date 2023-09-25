<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user}}`.
 */
class m221123_103351_add_psw_changed_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /**
         * ALTER TABLE `user` ADD `psw_changed` TINYINT NOT NULL DEFAULT '0' COMMENT 'used to verify if the user changed the default psw generated by APP\r\n0: not changed; 1: changed' AFTER `status`;
         * ALTER TABLE `user` ADD `first_time_login` TINYINT NOT NULL DEFAULT '1' COMMENT '0: no; 1: yes' AFTER `psw_changed`;
         *
         * ALTER TABLE `user`
         * ADD `psw_changed` TINYINT NOT NULL DEFAULT '0' COMMENT 'used to verify if the user changed the default psw generated by APP\r\n0: not changed; 1: changed' AFTER `updated_at`,
         * ADD `first_time_login` TINYINT NOT NULL DEFAULT '1' COMMENT '0: no; 1: yes' AFTER `psw_changed`;
         */
        $filePath = Yii::getAlias('@backend/modules/adm/migrations/sql_files/m221123_103351_add_psw_changed_column_to_user_table.sql');
        $sql = file_get_contents($filePath);
        $this->execute("{$sql}");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221123_103351_add_psw_changed_column_to_user_table cannot be reverted.\n";
        return false;
    }
}
