<?php

namespace console\controllers;

use api\models\Car;
use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use backend\modules\auto\models\CarKm;
use backend\modules\auto\models\Event;
use common\components\SendSharePointMailHelper;
use Yii;
use yii\base\Controller;
use yii\console\ExitCode;

class AutoRevisionNotificationController extends Controller
{
    public function actionIndex()
    {
        echo "Script run at " . date("Y-m-d-h:m:s") . "\n";
        $this->setViewPath('@app/mail');
        $userAndHolder = [];
        $carParkAdminSetting = Settings::findOneByAttributes([
            'name' => 'CAR_PARK_ADMIN'
        ]);
        $autoAdminEmailsToNotify = !empty($carParkAdminSetting) && !empty($carParkAdminSetting['value']) ?
            explode(',', $carParkAdminSetting['value']) :
            explode(',', Yii::$app->params['carParkAdmin']);
        $cars = Car::find()->where(['deleted' => 0])->with('brand', 'brandModel', 'holder', 'user')->all();
        if (empty($cars)) {
            echo("\nNO CARS FOUND\n");
            Yii::info("\n" . Yii::t('cmd-auto', "NO CARS FOUND"), 'autoRevisionNotification');
            return ExitCode::OK;
        }

        $adminNotification = [];
        foreach ($cars as $car) {
            $notificationMessages = [];
            $carKm = 0;
            if ($car->last_revision !== null) {
                $car_km = CarKm::find()->where(['car_id' => $car->id, 'deleted' => 0])->andWhere(['LIKE', 'added', $car->last_revision])->one();
                if ($car_km !== null) {
                    $carKm = $car_km->km;
                }
                $maxKm = $carKm + 10000;
                $carKms = CarKm::find()->where(['car_id' => $car->id, 'deleted' => 0])->andWhere(['>', 'added', $car->last_revision])->all();
                foreach ($carKms as $carKm) {
                    if ($carKm->km == $maxKm || $carKm->km > $maxKm) {
                        $currentKm = CarKm::find()
                            ->where(['car_id' => $car->id])
                            ->orderBy(['added' => SORT_DESC])
                            ->one();
                        $lastRevisionKm = CarKm::find()
                            ->where(['car_id' => $car->id])
                            ->andWhere(['<=', 'added', $car->last_revision])
                            ->orderBy(['added' => SORT_DESC])
                            ->one();
                        if (empty($lastRevisionKm)) {
                            $lastRevisionKm = CarKm::find()->where(['car_id' => $car->id])->orderBy(['added' => SORT_ASC])->one();
                        }
                        Event::checkForEventAndCreateOrUpdateEvent(
                            [
                                'searchAttributes' => [
                                    'car_id' => $car['id'],
                                    'event_type' => Event::REVISION
                                ],
                                'createAttributes' => [
                                    'car_id' => $car['id'],
                                    'event_type' => Event::REVISION,
                                    'deleted' => 0
                                ],
                                'updateAttributes' => [
                                    'car_id' => $car['id'],
                                ]
                            ],
                            [
                                'searchAttributes' => [
                                    'car_id' => $car['id'],
                                ],
                                'createAttributes' => [
                                    'car_id' => $car['id'],
                                    'last_revision_km' => $lastRevisionKm->km,
                                    'last_revision_date' => date('Y-m-d H:i:s', strtotime($car['last_revision'])),
                                    'car_current_km' => $currentKm->km,
                                    'plate_number' => $car['plate_number']
                                ],
                                'updateAttributes' => [
                                    'last_revision_km' => $lastRevisionKm->km,
                                    'last_revision_date' => date('Y-m-d H:i:s', strtotime($car['last_revision'])),
                                    'car_current_km' => $currentKm->km,
                                ]
                            ]
                        );

                        $notificationMessages[$car['id']] = Yii::t('cmd-auto', "For car with plate number <b>{plate_number}</b>, the revision must be done", ['plate_number' => $car['plate_number']]) . '!';
                        $adminNotification[$car['id']] = $notificationMessages[$car['id']];
                        $userAndHolder = [
                            $car['holder']['email']
                        ];
                    }
                }
            }
//            if (empty($notificationMessages)) {
//                continue;
//            }
//            $this->sendEmail($notificationMessages, $userAndHolder);
        }
        if (!empty($adminNotification)) {
            $this->sendEmail($adminNotification, $autoAdminEmailsToNotify);
        }
        echo("\nNO REVISION DATE FOUND\n");
        Yii::info("\n" . Yii::t('cmd-auto', "NO REVISION DATE FOUND"), 'autoRevisionNotification');
        return ExitCode::OK;
    }

    public function sendEmail($notificationContent, $emailToRecipients)
    {
        foreach ($emailToRecipients as $emailToRecipient) {
            $user = User::findOneByAttributes([
                'email' => $emailToRecipient
            ]);
            if (empty($user)) {
                Yii::error("\n" . Yii::t('cmd-auto', "User with email {emailToRecipients} not found", ['emailToRecipients' => $emailToRecipient]), 'autoRevisionNotification');
            } else {
                if (
                    !empty(Yii::$app->params['erp_beneficiary_name'])
                    && Yii::$app->params['erp_beneficiary_name'] === 'ghallard'
                ) {
                    Yii::$app->mailer->compose('@app/mail/auto-revision-notification-html', [
                        'notificationContent' => $notificationContent,
                        'user' => $user,
                    ])
                        ->setFrom('econfaire@ghallard.ro')
                        ->setTo($user['email'])
                        ->setSubject(Yii::t('cmd-auto', "Car revision notification"))
                        ->send();
                } else {
                    $mailBody = $this->renderPartial('auto-revision-notification-html', [
                        'notificationContent' => $notificationContent,
                        'user' => $user,
                    ]);

                    $sendEmail = new SendSharePointMailHelper();
                    $sendEmail->subject = Yii::t('cmd-auto', "Car revision notification");
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

                Yii::info("\n" . Yii::t('cmd-auto', "Successfully sent the mail notification"), 'autoRevisionNotification');
            }
        }
    }
}