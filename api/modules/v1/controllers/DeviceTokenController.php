<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Device;
use backend\modules\pmp\models\DeviceToken;
use Yii;

class DeviceTokenController extends RestV1Controller
{
    public $modelClass = 'backend\modules\pmp\models\DeviceToken';

    public function actionSaveFirebaseToken()
    {
        $message = Yii::t('app', 'Your firebase token is up to date');

        $post = Yii::$app->request->post();
        $device = Device::find()->where('uuid = :uuid', [':uuid'=>$post['uuid']])->one();

        if (empty($device)) {
            $message = Yii::t('app', 'Incomplete received data, missing uuid');
            return $this->prepareResponse($message, 400);
        }
        if (empty($post['user_id'])) {
            $message = Yii::t('app', 'Incomplete received data, missing user_id');
            return $this->prepareResponse($message, 400);
        }

        $deviceTokenModel = DeviceToken::findOneByAttributes(['device_id'=>$device['id']]);

        if (
            !empty($post['firebase_token'])
            &&!empty($device)
        ) {
            $isCleared = DeviceToken::clearOldTokens($post['user_id']);
            if (empty($deviceTokenModel) || $isCleared) {
                DeviceToken::createByAttributes([
                    'device_id' => $device['id'],
                    'firebase_token' => $post['firebase_token'],
                    'added_by' => $post['user_id']
                ]);
                $message = Yii::t('app', 'Successfully added your device token');
                return $this->prepareResponse($message, 200);
            }

            if ($post['firebase_token'] !== $deviceTokenModel['firebase_token']) {
                $deviceTokenModel->updateByAttributes([
                    'firebase_token'=>$post['firebase_token'],
                    'updated'=>date('Y-m-d H:i:s'),
                    'updated_by'=>$post['user_id']
                ]);
                $message = Yii::t('app', 'Successfully updated your device token');
                return $this->prepareResponse($message, 200);
            }

            if ($post['user_id'] !== $deviceTokenModel['added_by']) {
                $deviceTokenModel->updateByAttributes([
                    'updated'=>date('Y-m-d H:i:s'),
                    'updated_by'=>$post['user_id']
                ]);
                $message = Yii::t('app', 'Successfully updated your user_id');
                return $this->prepareResponse($message, 200);
            }
        }
        return $this->prepareResponse($message, 200);
    }
}