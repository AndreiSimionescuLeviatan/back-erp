<?php

namespace api\models;

use Yii;

class DeviceDetailsData extends DeviceDetailsDataParent
{
    public static function create($device, $deviceDetails, $data)
    {
        $model = new DeviceDetailsData();
        $model->device_details_id = $deviceDetails->id;
        $model->device_id = $device->id;
        $model->data = $data;
        $model->added = date('Y-m-d H:i:s');
        $model->added_by = $device->added_by;

        if (!$model->validate()) {
            throw new \Exception(json_encode($model->getErrors()), 400);
        }
        if (!$model->insert()) {
            throw new \Exception(Yii::t('app', 'Could not insert details data'), 500);
        }
        return $model;
    }
}
