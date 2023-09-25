<?php

namespace console\controllers;

use api\models\Car;
use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use backend\modules\auto\models\Accessory;
use backend\modules\auto\models\CarAccessory;
use backend\modules\auto\models\Event;
use common\components\SendSharePointMailHelper;
use DateTime;
use Yii;
use yii\console\ExitCode;
use yii\base\Controller;

class AutoCarAccessoryExpirationNotificationsController extends Controller
{
    public function actionIndex()
    {
        echo "Script run at " . date("Y-m-d-h:m:s") . "\n";
        $this->setViewPath('@app/mail');
        $carParkAdminSetting = Settings::findOneByAttributes([
            'name' => 'CAR_PARK_ADMIN'
        ]);
        $autoAdminEmailsToNotify = !empty($carParkAdminSetting) && !empty($carParkAdminSetting['value']) ?
            explode(',', $carParkAdminSetting['value']) :
            explode(',', Yii::$app->params['carParkAdmin']);
        $carAccessories = CarAccessory::findAllByAttributes([
            'deleted' => 0
        ]);
        if (empty($carAccessories)) {
            echo("\nNO CAR ACCESSORIES FOUND\n");
            Yii::info("\n" . Yii::t('cmd-auto', "NO CAR ACCESSORIES FOUND"), 'autoCarAccessoryExpirationNotifications');
            return ExitCode::OK;
        }
        $now = new DateTime(date('Y-m-d'));
        $notificationMessages = [];
        $userAndHolder = [];
        $intervals = [0, 1, 2, 3, 4, 5, 6, 7, 14, 28];
        foreach ($carAccessories as $accessory) {
            if (empty($accessory['expiration_date'])) {
                continue;
            }
            $accessoryName = Accessory::findOneByAttributes([
                'id' => $accessory['accessory_id']
            ]);
            if (
                $accessoryName['name'] !== Yii::t('app', 'Extinguisher')
                && $accessoryName['name'] !== Yii::t('app', 'Medical kit')
            ) {
                continue;
            }
            $validity = new DateTime($accessory['expiration_date']);
            foreach ($intervals as $interval) {
                $car = Car::find()->where('id = :id', [':id' => $accessory['car_id']])->with('brand', 'brandModel', 'holder', 'user')->one();
                if ($now <= $validity && $now->diff($validity)->days == $interval) {
                    Event::checkForEventAndCreateOrUpdateEvent(
                        [
                            'searchAttributes' => [
                                'car_id' => $accessory['car_id'],
                                'event_type' => Event::EXPIRING_CAR_ACCESSORIES,
                            ],
                            'createAttributes' => [
                                'car_id' => $accessory['car_id'],
                                'event_type' => Event::EXPIRING_CAR_ACCESSORIES,
                                'deleted' => 0
                            ],
                            'updateAttributes' => [
                                'car_id' => $accessory['car_id'],
                            ]
                        ],
                        [
                            'searchAttributes' => [
                                'car_id' => $accessory['car_id'],
                                'accessory_id' => $accessory['accessory_id']
                            ],
                            'createAttributes' => [
                                'car_id' => $accessory['car_id'],
                                'accessory_id' => $accessory['accessory_id'],
                                'accessory_valability_date' => $accessory['expiration_date'],
                                'plate_number' => $car['plate_number']
                            ],
                            'updateAttributes' => [
                                'accessory_valability_date' => $accessory['expiration_date'],
                            ]
                        ]
                    );

                    $dayDiff = $now->diff($validity)->days;
                    $notificationMessages[$car['id']] = Yii::t('cmd-auto', 'For car with plate number {plate_number}, {accessory_name} will expire in {dayDiff} days', ['plate_number' => $car['plate_number'], 'dayDiff' => $dayDiff, 'accessory_name' => $accessoryName['name']]);
                    if (!empty($car['user']['email'])) {
                        $userAndHolder = [
                            $car['holder']['email'],
                            $car['user']['email']
                        ];
                        $userAndHolder = array_filter($userAndHolder);
                    }
                } else if ($validity < $now) {
                    Event::checkForEventAndCreateOrUpdateEvent(
                        [
                            'searchAttributes' => [
                                'car_id' => $accessory['car_id'],
                                'event_type' => Event::EXPIRED_CAR_ACCESSORIES
                            ],
                            'createAttributes' => [
                                'car_id' => $accessory['car_id'],
                                'event_type' => Event::EXPIRED_CAR_ACCESSORIES,
                                'deleted' => 0
                            ],
                            'updateAttributes' => [
                                'car_id' => $accessory['car_id'],
                            ]
                        ],
                        [
                            'searchAttributes' => [
                                'car_id' => $accessory['car_id'],
                                'accessory_id' => $accessory['accessory_id'],
                            ],
                            'createAttributes' => [
                                'car_id' => $accessory['car_id'],
                                'accessory_id' => $accessory['accessory_id'],
                                'accessory_valability_date' => $accessory['expiration_date'],
                                'plate_number' => $car['plate_number']
                            ],
                            'updateAttributes' => [
                                'accessory_valability_date' => $accessory['expiration_date'],
                            ]
                        ]
                    );

                    $dayDiff = $now->diff($validity)->days;
                    $car = Car::find()->where('id = :id', [':id' => $accessory['car_id']])->with('brand', 'brandModel', 'holder', 'user')->one();
                    $notificationMessages[$car['id']] = Yii::t('cmd-auto', 'For car with plate number {plate_number}, {accessory_name} is expired for {dayDiff} days', ['plate_number' => $car['plate_number'], 'dayDiff' => $dayDiff, 'accessory_name' => $accessoryName['name']]);
                    if (!empty($car['user']['email'])) {
                        $userAndHolder = [
                            $car['holder']['email'],
                            $car['user']['email']
                        ];
                        $userAndHolder = array_filter($userAndHolder);
                    }
                }
            }
        }
        if (empty($notificationMessages)) {
            echo("\nNO NOTIFICATION TO SEND\n");
            Yii::info("\n" . Yii::t('cmd-auto', "NO NOTIFICATION TO SEND"), 'autoCarAccessoryExpirationNotifications');
            return ExitCode::OK;
        }

        $this->sendEmail($notificationMessages, $userAndHolder);
        $this->sendEmail($notificationMessages, $autoAdminEmailsToNotify);
    }

    public function sendEmail($notificationContent, $emailToRecipients)
    {
        foreach ($emailToRecipients as $emailToRecipient) {
            $user = User::findOneByAttributes([
                'email' => $emailToRecipient
            ]);
            if (empty($user)) {
                Yii::error("\n" . Yii::t('cmd-auto', "User with email {emailToRecipients} not found", ['emailToRecipients' => $emailToRecipients]), 'autoCarTireExpirationNotificationController');
            } else {
                if (
                    !empty(Yii::$app->params['erp_beneficiary_name'])
                    && Yii::$app->params['erp_beneficiary_name'] === 'ghallard'
                ) {
                    Yii::$app->mailer->compose('@app/mail/auto-car-accessory-expiration-notifications-html', [
                        'notificationContent' => $notificationContent,
                        'user' => $user,
                    ])
                        ->setFrom('econfaire@ghallard.ro')
                        ->setTo($user['email'])
                        ->setSubject(Yii::t('cmd-auto', "Car accessories expiration notification"))
                        ->send();
                } else {
                    $mailBody = $this->renderPartial('auto-car-accessory-expiration-notifications-html', [
                        'notificationContent' => $notificationContent,
                        'user' => $user,
                    ]);
                    $sendEmail = new SendSharePointMailHelper();
                    $sendEmail->subject = Yii::t('cmd-auto', "Car accessories expiration notification");
                    $sendEmail->content = [
                        "contentType" => "html",
                        "content" => $mailBody,
                    ];
                    $sendEmail->toRecipients = [
                        [
                            "emailAddress" => [
                                "name" => $user->fullName(),
                                "address" => $user['email'],
                            ]
                        ]
                    ];
                    $sendEmail->sendEmail();
                }

                Yii::info("\n" . Yii::t('cmd-auto', "Successfully sent the mail notification"), 'autoCarAccessoryExpirationNotifications');
            }
        }
    }
}