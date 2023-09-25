<?php

namespace api\models;

use Yii;

class DeviceDetails extends DeviceDetailsParent
{
    public static function create($device, $detailName)
    {
        if (empty($detailName)) {
            throw new \Exception('Empty name', 400);
        }

        $model = new DeviceDetails();
        $model->device_id = $device->id;
        $model->name = $detailName;
        $model->added = date('Y-m-d H:i:s');
        $model->added_by = $device->added_by;
        if (!$model->validate()) {
            throw new \Exception(json_encode($model->getErrors()), 400);
        }
        $model->insert();

        return $model;
    }

    public static function auth($device, $deviceDetails)
    {
        $deviceDetailsData = self::getDeviceDetails($device->id);
        foreach ($deviceDetailsData as $name => $value) {
            if (!isset($deviceDetails[$name])) {
                throw new \Exception(Yii::t('app', "No detail received for: {$name}"), 400);
            }

            if ($deviceDetails[$name] != $value) {
                throw new \Exception(Yii::t('app', "Wrong detail received for: {$name}"), 401);
            }
        }

        return true;
    }

    public static function getDeviceDetails($deviceID)
    {
        $detailsNames = DeviceDetails::find()->where("device_id = {$deviceID}")->all();
        
        $details = [];
        foreach ($detailsNames as $detailName) {
            $detailData = DeviceDetailsData::find()->where("device_details_id = {$detailName->id}")->one();
            if ($detailData === null) {
                continue;
            }

            $details[$detailName->name] = $detailData->data;
        }

        return $details;
    }
}
