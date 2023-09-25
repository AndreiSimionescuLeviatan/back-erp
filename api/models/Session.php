<?php

namespace api\models;

use Yii;

class Session extends SessionParent
{
    public static function create($name, $deviceID, $retries = 100)
    {
        if ($retries <= 0) {
            return null;
        }

        $model = self::checkForExistingToken($deviceID);
        if ($model !== null) {
            $model->last_seen = date('Y-m-d H:i:s');
            $model->updated = date('Y-m-d H:i:s');
            $model->updated_by = User::getAPIUserID();
            $model->update();

            return $model;
        }

        $token = self::generateToken();

        $model = self::find()->where("token = '{$token}'")->one();
        if ($model !== null) {
            $retries--;
            return Session::create($name, $deviceID, $retries);
        }

        $model = new Session();
        $model->name = $name;
        $model->device_id = $deviceID;
        $model->token = $token;
        $model->last_seen = date('Y-m-d H:i:s');
        $model->added = date('Y-m-d H:i:s');
        $model->added_by = User::getAPIUserID();

        if (!$model->save()) {
            return null;
        }

        return $model;
    }

    public static function checkForExistingToken($deviceID)
    {
        $model = Session::find()->where("device_id = {$deviceID}")->orderBy('id DESC')->one();
        if ($model === null) {
            return null;
        }

        if (time() - strtotime($model->last_seen) > self::sessionTimeoutThreshold()) {
            return null;
        }

        return $model;
    }

    public static function generateToken()
    {
        return Yii::$app->security->generateRandomString();
    }

    public static function sessionTimeoutThreshold()
    {
        return 24 * 60 * 60;
    }
}
