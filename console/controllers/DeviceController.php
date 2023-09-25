<?php

namespace console\controllers;

use backend\modules\pmp\models\Device;
use Yii;
use yii\console\Controller;
use yii\web\BadRequestHttpException;

class DeviceController extends Controller
{
    /**
     * Update devices status (active, inactive, uninstalled...)
     * After insert status in new table for history
     * The function will be appeal every one minute
     *
     * @author Calin B.
     * @since 08.06.2022
     */
    public function actionSetStatus()
    {
        $conn = Device::getDb();

        try {
            $tableNameUpdate = Device::tableName();
            $sql = "UPDATE `ecf_pmp`.`device` SET `status` = CASE WHEN `last_seen` < DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 99 WHEN `last_seen` > DATE_SUB(NOW(), INTERVAL 7 DAY) AND `last_seen` < DATE_SUB(NOW(), INTERVAL 2 MINUTE) THEN 0 ELSE 1 END WHERE `deleted` = 0";
            $conn->createCommand($sql)->execute();
        } catch (\Exception $exc) {
            throw new BadRequestHttpException(Yii::t('app', $exc->getMessage()));
        }

//        try {
//            $year = date('Y');
//            $tableNameInsert = "device_status_{$year}";
//
//            $sql = "SET FOREIGN_KEY_CHECKS=0;
//                CREATE TABLE IF NOT EXISTS `ecf_pmp`.`{$tableNameInsert}`(
//                `id` INT(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
//                `device_id` INT(11) NOT NULL,
//                `status` TINYINT(1) NOT NULL,
//                `added` DATETIME NOT NULL,
//                `added_by` INT(11) NOT NULL,
//                KEY `idx-{$tableNameInsert}-device_id` (`device_id`),
//                 CONSTRAINT `fk-{$tableNameInsert}-device_id` FOREIGN KEY (`device_id`) REFERENCES `ecf_pmp`.`device`(id) ON DELETE NO ACTION ON UPDATE NO ACTION
//                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
//                SET FOREIGN_KEY_CHECKS=1;";
//
//            $conn->createCommand($sql)->execute();
//        } catch (\Exception $exc) {
//            throw new BadRequestHttpException(Yii::t('app', $exc->getMessage()));
//        }

        try {
            $year = date('Y');
            $tableNameInsert = "`ecf_pmp`.`device_status_{$year}`";
            $date = date('Y-m-d H:i:s');

            $sql = "INSERT INTO $tableNameInsert (`device_id`, `status`, `added`, `added_by`) SELECT `id`, `status`, '{$date}', -3 FROM `ecf_pmp`.`device` WHERE `status` != 99;";
            $conn->createCommand($sql)->execute();

        } catch (\Exception $exc) {
            throw new BadRequestHttpException(Yii::t('app', $exc->getMessage()));
        }
    }
}