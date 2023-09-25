<?php

namespace api\modules\v1\controllers;
use common\components\HttpStatus;
use Yii;
class NotificationController extends RestV1Controller
{
    public function actionSendEmail()
    {
        $post = Yii::$app->request->post();
        if (empty($post))
        {
            $msg = Yii::t('notification', 'No data received');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
            return [
                'message' => $msg
            ];
        }
        if (empty($post['to']))
        {
            $msg = Yii::t('notification', 'No emails receipt');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
            return [
                'message' => $msg
            ];
        }
        if (empty($post['subject']))
        {
            $msg = Yii::t('notification', 'No subject receipt');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
            return [
                'message' => $msg
            ];
        }
        if (empty($post['content']))
        {
            $msg = Yii::t('notification', 'No content receipt');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
            return [
                'message' => $msg
            ];
        }
    }
}