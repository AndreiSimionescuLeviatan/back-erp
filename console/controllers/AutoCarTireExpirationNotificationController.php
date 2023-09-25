<?php

namespace console\controllers;

use api\models\Car;
use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use backend\modules\auto\models\CarTire;
use backend\modules\auto\models\Event;
use common\components\SendSharePointMailHelper;
use DateTime;
use Yii;
use yii\base\Controller;
use yii\console\ExitCode;

class AutoCarTireExpirationNotificationController extends Controller
{
    public function actionIndex()
    {
        echo "Script run at " . date("Y-m-d-h:m:s") . "\n";
        $this->setViewPath('@app/mail');
        $carParkAdminSetting = Settings::find()->where(['name' => 'CAR_PARK_ADMIN'])->asArray()->one();
        $autoAdminEmailsToNotify = !empty($carParkAdminSetting) && !empty($carParkAdminSetting['value']) ?
            explode(',', $carParkAdminSetting['value']) :
            explode(',', Yii::$app->params['carParkAdmin']);
        $carTires = CarTire::find()->where('deleted = 0')->all();
        $now = new DateTime(date('Y-m-d'));
        if (empty($carTires)) {
            echo("\nNO CAR TIRES FOUND\n");
            Yii::info("\n" . Yii::t('cmd-auto', "NO CAR TIRES FOUND"), 'autoCarTireExpirationNotificationController');
            return ExitCode::OK;
        }
        $notificationMessage = [];
        $userAndHolder = [];
        $count = 0;
        foreach ($carTires as $carTire) {
            if (empty($carTire['tire_validity'])) {
                continue;
            }
            $validity = new DateTime($carTire['tire_validity']);
            $count++;
            if ($now <= $validity) {
                Event::checkForEventAndCreateOrUpdateEvent(
                    [
                        'searchAttributes' => [
                            'car_id' => $carTire['car_id'],
                            'event_type' => Event::EXPIRED_CAR_TIRE
                        ],
                        'createAttributes' => [
                            'car_id' => $carTire['car_id'],
                            'event_type' => Event::EXPIRED_CAR_TIRE,
                            'deleted' => 0
                        ],
                        'updateAttributes' => [
                            'car_id' => $this->id,
                        ]
                    ],
                    [
                        'searchAttributes' => [
                            'car_id' => $carTire['car_id'],
                            'tire_replace_type' => $carTire['tire_type']
                        ],
                        'createAttributes' => [
                            'car_id' => $carTire['car_id'],
                            'tire_replace_type' => $carTire['tire_type'],
                            'tire_replace_required_date' => date('Y-m-d H:i:s', strtotime($carTire['tire_validity']))
                        ],
                        'updateAttributes' => [
                            'tire_replace_type' => $carTire['tire_type'],
                            'tire_replace_required_date' => date('Y-m-d H:i:s', strtotime($carTire['tire_validity']))
                        ]
                    ]
                );

                $dayDiff = $now->diff($validity)->days;
                $carId = $carTire['car_id'];
                $modelCar = Car::find()->where('id = :id', [':id' => $carId])->with('brand', 'brandModel', 'holder', 'user')->one();
                $notificationMessage[$modelCar['id']] = Yii::t('cmd-auto', 'For car with plate number {plate_number}, tires will expire in {dayDiff} days', ['plate_number' => $modelCar->plate_number, 'dayDiff' => $dayDiff]);
                $userAndHolder = [
                    $modelCar['holder']['email'],
                    $modelCar['user']['email']
                ];
                $userAndHolder = array_filter($userAndHolder);
                $this->sendEmail($notificationMessage, $userAndHolder);
                if ($count === count($carTires)-1) {
                    $this->sendEmail($notificationMessage, $autoAdminEmailsToNotify);
                }
            }
        }
    }

    /**
     * @param $notificationContent
     * @param $emailToRecipients
     * @return void
     * @throws \yii\web\BadRequestHttpException
     */
    public function sendEmail($notificationContent, $emailToRecipients)
    {
        foreach ($emailToRecipients as $emailToRecipient) {
            $user = User::find()->where('email = :email', [':email' => $emailToRecipient])->one();
            if (empty($user)) {
                Yii::error("\n" . Yii::t('cmd-auto', "User with email {emailToRecipients} not found", ['emailToRecipients' => $emailToRecipients]), 'autoCarTireExpirationNotificationController');
            } else {
                if (
                    !empty(Yii::$app->params['erp_beneficiary_name'])
                    && Yii::$app->params['erp_beneficiary_name'] === 'ghallard'
                ) {
                    Yii::$app->mailer->compose('@app/mail/auto-car-tire-expiration-notification-html', [
                        'notificationContent' => $notificationContent,
                        'user' => $user,
                    ])
                        ->setFrom('econfaire@ghallard.ro')
                        ->setTo($user['email'])
                        ->setSubject(Yii::t('cmd-auto', "Car tires expiration notification"))
                        ->send();
                } else {
                    $mailBody = $this->renderPartial('auto-car-tire-expiration-notification-html', [
                        'notificationContent' => $notificationContent,
                        'user' => $user,
                    ]);

                    $sendEmail = new SendSharePointMailHelper();
                    $sendEmail->subject = Yii::t('cmd-auto', "Car tires expiration notification");
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

                Yii::info("\n" . Yii::t('cmd-auto', "Successfully sent the mail notification"), 'autoCarTireExpirationNotificationController');
            }
        }
    }
}