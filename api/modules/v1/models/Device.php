<?php

namespace api\modules\v1\models;


use api\models\DeviceDetails;
use api\models\DeviceDetailsData;
use api\modules\v2\models\EmployeeCompany;
use backend\modules\adm\models\Settings;
use Yii;
use yii\web\BadRequestHttpException;

class Device extends \api\models\Device
{
    const DB_NAME = 'ecf_pmp';

    public static function getTableNameDeviceLocation($date = null)
    {
        $yearAndMonth = date("Y_m");
        if ($date != null) {
            $yearAndMonth = date("Y_m",strtotime($date));
        }

        return "device_location_{$yearAndMonth}";
    }

    public static function getLogoApk($uuid)
    {
        $myDevice = Device::find()->where(['uuid' => $uuid])->one();
        if ($myDevice === null) {
            return '';
        }

        $deviceDetails = DeviceDetails::getDeviceDetails($myDevice->id);
        if ($deviceDetails === null) {
            return '';
        }

        $deviceInternalUuid = $deviceDetails['device_internal_uuid'];
        $deviceDetailsData = DeviceDetailsData::find()->where(['LIKE', 'data', "%{$deviceInternalUuid}%", false])->all();
        $userId = '';
        foreach ($deviceDetailsData as $detailsData) {
            $device = Device::find()->where(['id' => $detailsData['device_id']])->one();
            if ($device === null || $device->updated_by === null) {
                continue;
            }
            $userId = $device->updated_by;
        }

        if ($userId === '') {
            return '';
        }

        $myDevice->owner_id = $userId;
        $myDevice->update();

        $modelEmployee = Employee::find()->where(['user_id' => $userId])->one();
        if ($modelEmployee === null) {
            return '';
        }

        $modelEmployeeCompany = EmployeeCompany::find()->where(['employee_id' => $modelEmployee->id])->one();
        switch ($modelEmployeeCompany->company_id) {
            case 2:
            case 1:
                $logo = 'LOGO_APK_LEVTECH';
                break;
            case 697:
                $logo = 'LOGO_APK_GHALLARD';
                break;
            default:
                $logo = 'LOGO_APK_ECONFAIRE';
        }
        $logoImg = Settings::find()->where(['name' => $logo])->one();

        return $logoImg->value ?? '';
    }

    public static function createDeviceLocationTableIfNotExist()
    {
        $conn = Device::getDb();
        $tableNameInsert = self::getTableNameDeviceLocation();
        $sql = "SET FOREIGN_KEY_CHECKS=0;
                CREATE TABLE IF NOT EXISTS " . self::DB_NAME . ".`{$tableNameInsert}`(
                    `id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
                    `device_id` INT NOT NULL,
                    `location_id` INT NOT NULL,
                    `latitude` DECIMAL(8,6) DEFAULT NULL,
                    `longitude` DECIMAL(9,6) DEFAULT NULL,
                    `location_name` VARCHAR(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
                    `location_address` VARCHAR(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
                    `added` DATETIME NOT NULL,
                KEY `idx-{$tableNameInsert}-device_id` (`device_id`),
                CONSTRAINT `fk-{$tableNameInsert}-device_id` FOREIGN KEY (`device_id`) REFERENCES `ecf_pmp`.`device`(id) ON DELETE NO ACTION ON UPDATE NO ACTION
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
                SET FOREIGN_KEY_CHECKS=1;";
        $conn->createCommand($sql)->execute();
    }

    public static function insertDataIntoDeviceLocationTable($params)
    {
        $conn = Device::getDb();
        try {
            $tableNameInsert = self::getTableNameDeviceLocation();
            $date = date('Y-m-d H:i:s');
            $sql = "INSERT INTO `{$tableNameInsert}` (`device_id`, `location_id`, `latitude`, `longitude`, `location_name`, `location_address`, `added`)
                    VALUES ('{$params['device_id']}', 
                            '{$params['location_id']}', 
                            '{$params['latitude']}', 
                            '{$params['longitude']}', 
                            '{$params['location_name']}',
                            '{$params['location_address']}', 
                            '$date');";

            $conn->createCommand($sql)->execute();
        } catch (\Exception $exc) {}
    }

}
