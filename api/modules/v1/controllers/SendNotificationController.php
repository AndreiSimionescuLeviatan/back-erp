<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\MailHelper;
use common\components\HttpStatus;
use Yii;
use yii\db\Exception;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;


class SendNotificationController extends RestV1Controller
{
    public $modelClass = 'api\modules\v1\models\MailHelper';

    /**
     * @throws \yii\web\BadRequestHttpException
     * @throws Exception
     * @throws NotFoundHttpException
     * send email notification
     */
    public function actionEmail()
    {
        $post = Yii::$app->request->post();
        if (empty($post)) {
            $msg = Yii::t('app-notification', 'No data received');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
            return [
                'message' => $msg
            ];
        }
        if (empty($post['to'])) {
            $msg = Yii::t('app-notification', 'No emails receipt');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
            return [
                'message' => $msg
            ];
        }
        if (empty($post['subject'])) {
            $msg = Yii::t('app-notification', 'No subject receipt');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
            return [
                'message' => $msg
            ];
        }
        if (empty($post['content'])) {
            $msg = Yii::t('app-notification', 'No content receipt');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
            return [
                'message' => $msg
            ];
        }
        try {
            $model = new MailHelper();
            $model->sendEmailNotification($post);
            return [
                'message' => Yii::t('app-notification', 'Success send emails')
            ];
        } catch (HttpException $exc) {
            Yii::$app->response->statusCode = $exc->statusCode;
            $msg = Yii::t('app-notification', $exc->getMessage());
            self::error($msg);
            return [
                'message' => $msg
            ];
        }
    }
}