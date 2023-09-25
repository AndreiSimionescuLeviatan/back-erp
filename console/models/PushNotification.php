<?php

namespace console\models;

use api\modules\v1\models\Employee;
use backend\modules\hr\models\Shift;
use backend\modules\hr\models\ShiftBreakInterval;
use backend\modules\pmp\models\DeviceToken;
use DateTime;

class PushNotification
{
    public static function sendNotificationSchedule($firebaseToken, $employees, $notification)
    {
        $fcmHr = new FCM();
        $fcmHr->setServerKey($firebaseToken);

        foreach ($employees as $employee) {
            if (empty($employee['user_id'])) {
                continue;
            }
            if(date('H:i:s', strtotime($employee['start_schedule'])) < date('H:i:s')) {
                $tokenForNotify = DeviceToken::getTokenForNotify($employee['user_id']);
                if ($tokenForNotify === null) {
                    continue;
                }
                echo "Unopened shift -> " . $employee['full_name'] . PHP_EOL;
                $fcmHr->sendTo($tokenForNotify, $notification);
                continue;
            }

            if(date('H:i:s', strtotime($employee['stop_schedule'])) < date('H:i:s')) {
                $tokenForNotify = DeviceToken::getTokenForNotify($employee['user_id']);
                if ($tokenForNotify == null) {
                    continue;
                }
                echo "Unclosed shift -> " . $employee['full_name'] . PHP_EOL;
                $fcmHr->sendTo($tokenForNotify, $notification);
            }
        }
    }
    public static function sendNotificationBreak($firebaseToken, $idsEmployees, $notification)
    {
        $fcmHr = new FCM();
        $fcmHr->setServerKey($firebaseToken);
        foreach ($idsEmployees as $idEmployee) {
            $currentEmployee = Employee::findOne(['id' => $idEmployee]);
            $tokenForNotify = DeviceToken::getTokenForNotify(
                $currentEmployee['user_id']
            );
            if ($tokenForNotify === null) {
                Yii::error("There is no token for " . $currentEmployee['full_name']);
                continue;
            }
            echo "Unclosed break -> " . $currentEmployee['full_name'] . PHP_EOL;
            $fcmHr->sendTo($tokenForNotify, $notification);
        }
    }
}