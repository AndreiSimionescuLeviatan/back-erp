<?php

namespace console\controllers;

use backend\modules\adm\models\Settings;
use backend\modules\hr\models\Shift;
use backend\modules\hr\models\ShiftBreakInterval;
use backend\modules\hr\models\WorkingDay;
use console\models\PushNotification;
use Yii;
use yii\console\Controller;

class HrPushNotificationController extends Controller
{
    public function actionIndex($companyID)
    {
        echo "Script run at " . date('Y-m-d H:i:s') . "\n";

        $firebaseTokenHr = "";
        $settingFirebaseTokenHr = Settings::findOneByAttributes(['name' => "FIREBASE_TOKEN_HR_APP"]);

        if (!empty($settingFirebaseTokenHr)) {
            if (!isset($settingFirebaseTokenHr['value'])) {
                echo Yii::t('cmd-hr', "Firebase token is not set.");
                Yii::error("\n" . Yii::t('cmd-hr', "Firebase token is not set."));
                return;
            }
            $firebaseTokenHr = $settingFirebaseTokenHr['value'];
        }

        $settingStatusNotifications = Settings::findOneByAttributes(['name' => "SEND_NOTIFICATION_HR_APP"]);
        if (!empty($settingStatusNotifications)) {
            if (intval($settingStatusNotifications['value']) == 0) {
                echo Yii::t('cmd-hr', "Notifications are disabled.");
                Yii::error("\n" . Yii::t('cmd-hr', "Notifications are disabled."));
                return;
            }
        }

        if (empty($firebaseTokenHr)) {
            echo Yii::t('cmd-hr', "Insufficient information.");
            Yii::error("\n" . Yii::t('cmd-hr', "Insufficient information."));
            return;
        }
        $currentWorkingDay = WorkingDay::findOneByAttributes([
            'day' => date('Y-m-d'),
            'work' => 1
        ]);

        if (empty($currentWorkingDay)) {
            return;
        }

        $employeesWithUnopenedShifts = Shift::getEmployeesWithUnopenedShifts($companyID);
        $employeesWithUnclosedShifts = Shift::getEmployeesWithUnclosedShifts($companyID);

        $notificationUnclosedShift = [
            "title" => Yii::t('cmd-hr', "Human Resources"),
            "body" => Yii::t('cmd-hr', 'Do not forget to stop your shift.')
        ];
        $notificationUnclosedBreak = [
            "title" => Yii::t('cmd-hr', "Human Resources"),
            "body" => Yii::t('cmd-hr', 'Do not forget to stop your break.')
        ];
        $notificationUnopenedShift = [
            "title" => Yii::t('cmd-hr', "Human Resources"),
            "body" => Yii::t('cmd-hr', 'Do not forget to start your shift.')
        ];

        $tempEmployees = [];
        foreach ($employeesWithUnclosedShifts as $employeeWithUnclosedShift) {
            if (array_search($employeeWithUnclosedShift['user_id'], array_column($employeesWithUnopenedShifts, 'user_id')) !== false) {
                continue;
            }
            array_push($tempEmployees, $employeeWithUnclosedShift);
        }
        $employeesWithUnclosedShifts = $tempEmployees;

        PushNotification::sendNotificationSchedule($firebaseTokenHr, $employeesWithUnopenedShifts, $notificationUnopenedShift);
        PushNotification::sendNotificationSchedule($firebaseTokenHr, $employeesWithUnclosedShifts, $notificationUnclosedShift);

        $idsEmployeesWithUnclosedBreaks = ShiftBreakInterval::getIdsEmployeesWithUnclosedBreak($companyID);
        PushNotification::sendNotificationBreak($firebaseTokenHr, $idsEmployeesWithUnclosedBreaks, $notificationUnclosedBreak);
    }
}