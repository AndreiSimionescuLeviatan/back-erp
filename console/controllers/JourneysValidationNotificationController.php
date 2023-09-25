<?php

namespace console\controllers;

use backend\modules\adm\models\Settings;
use backend\modules\auto\models\Car;
use backend\modules\auto\models\Journey;
use backend\modules\hr\models\Employee;
use common\components\SendSharePointMailHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\helpers\Html;

class JourneysValidationNotificationController extends Controller
{

    /**
     * @return int
     * @throws GuzzleException
     * @throws \yii\web\BadRequestHttpException
     * @throws Exception
     */
    public function actionIndex()
    {
        Yii::info("\nJourneys invalidation list cron service is running...", 'journeysValidationNotification');
        $yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));
        $users = Journey::getUsers();

        if ($users !== null) {
            foreach ($users as $user) {
                $invalidateJourneys = Journey::find()->where(['status' => 0, 'deleted' => 0, 'user_id' => $user])->andWhere(['<', 'added', $yesterday])->all();
                $countJourneys = count($invalidateJourneys);
                $userEmail = Employee::find()->where(['user_id' => $user])->one();
                if ($userEmail !== null) {
                    if ($countJourneys > 0) {
                        $this->sendJourneyInvalidateNotification($countJourneys, $userEmail);
                        Yii::info("\n" . Yii::t('cmd-auto', "Email was sent to {email}", ["email" => $userEmail->email]), 'journeysValidationNotification');
                        echo "\nEMAIL SENT to $userEmail->email\n";
                    } else {
                        Yii::info("\n" . Yii::t('cmd-auto', "No journeys to validate for {fullName}", ["fullName" => $userEmail->full_name]), 'journeysValidationNotification');
                        echo "\nNo journeys to validate for $userEmail->full_name\n";
                    }
                }
            }
        }
        return ExitCode::OK;
    }

    /**
     * @param $count
     * @param $userEmail
     * @return void
     * @throws \yii\web\BadRequestHttpException
     */
    public function sendJourneyInvalidateNotification($count, $userEmail)
    {
        $sendEmail = new SendSharePointMailHelper();
        $sendEmail->subject = Yii::t('cmd-auto', "ERP - Journeys invalidated notification");
        $sendEmail->content = [
            "contentType" => "html",
            "content" => Yii::t('cmd-auto', 'Hello') . ", <br> <br> " . Yii::t('cmd-auto', 'You have {count} journeys to validate which are older than 24 hours.', ['count' => $count]) . "<br>"
                              . Yii::t('cmd-auto', 'Please validate them.') . "<br> <br>"
                              . Yii::t('cmd-auto', 'Thank you!')
        ];

        $emailsAdminList = Car::getCarParkAdminEmailsList();

        $sendEmail->toRecipients = [
            [
                "emailAddress" => [
                    "name" => 'User',
                    "address" => $userEmail->email,
                ]
            ]
        ];
        $sendEmail->sendEmail();
    }
}