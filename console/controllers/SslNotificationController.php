<?php

namespace console\controllers;

use backend\modules\adm\models\Settings;
use common\components\SendSharePointMailHelper;
use DateTime;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class SslNotificationController extends Controller
{
    /**
     * @return int
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionIndex()
    {
        $currentDate = new DateTime('now');
        $msg = Yii::t('app', 'SSL notification cron service started for day') . ": {$currentDate->format("Y-m-d")}";
        Yii::info("\n{$msg}", 'sslNotification');
        echo "\n{$msg}";

        $sslExpirationDate = Settings::getSslDateExpiration();
        if (empty($sslExpirationDate)) {
            $msg = Yii::t('app', 'No SSL date expiration found');
            Yii::error("\n{$msg}", 'sslNotification');
            echo "\n{$msg}";
            return ExitCode::CONFIG;
        }

        $endDateSSL = new DateTime($sslExpirationDate);
        $differenceDays = $currentDate->diff($endDateSSL);
        $days = $differenceDays->format("%r%a");

        if ($days <= 0) {
            $msg = Yii::t('app', 'SSL has expired') . " ({$sslExpirationDate})";
            $this->sendSslExpirationNotification($msg);
            Yii::info("\n{$msg}", 'sslNotification');
            echo "\n{$msg}";
            return ExitCode::CONFIG;
        }
        $msg = Yii::t('app', 'SSL will expire') . " ({$days}" . Yii::t('app', 'days') . ')' . " ({$sslExpirationDate})";
        Yii::info("\n{$msg}", 'sslNotification');
        echo "\n{$msg}";

        $sslExpirationUntil = Settings::getSslUntilExpiration();
        if (empty($sslExpirationUntil)) {
            $msg = Yii::t('app', 'No SSL expiration days until found');
            Yii::error("\n{$msg}", 'sslNotification');
            echo "\n{$msg}";
            return ExitCode::CONFIG;
        }
        if ($days <= $sslExpirationUntil) {
            $this->sendSslExpirationNotification($msg);
        }
        return ExitCode::OK;
    }

    /**
     * @param $days
     * @throws \yii\web\BadRequestHttpException
     */
    public function sendSslExpirationNotification($msg)
    {
        $headerMsg = Yii::t('app', 'Hello');
        $footerMsg = Yii::t('app', 'Best Regards, <br> Econfaire ID');
        $sendEmail = new SendSharePointMailHelper();
        $sendEmail->subject = Yii::t('app', "SSL - Notification");
        $sendEmail->content = [
            "contentType" => "html",
            "content" => "$headerMsg, <br>
                          $msg <br>
                          $footerMsg",
        ];
        $emailsAdminList = Settings::getSslAdminEmailList();
        $addressesEmail = count($emailsAdminList);
        $i = 0;
        foreach ($emailsAdminList as $emailAdmin) {
            $sendEmail->toRecipients = [
                [
                    "emailAddress" => [
                        "name" => 'Admin',
                        "address" => $emailAdmin,
                    ]
                ]
            ];
            if ($i++ == $addressesEmail) {
                break;
            }
            $sendEmail->sendEmail();
        }
    }
}