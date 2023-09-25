<?php

namespace console\controllers;

use backend\modules\auto\models\Car;
use backend\modules\auto\models\CarKm;
use backend\modules\auto\models\Event;
use common\components\SendSharePointMailHelper;
use GuzzleHttp\Exception\GuzzleException;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Exception;

class AutoNotificationDifferenceKmController extends Controller
{
    /**
     * @return false|string
     * @throws GuzzleException
     * @throws \yii\web\BadRequestHttpException
     * @throws Exception
     */
    public function actionIndex()
    {
        Yii::info("\nNotification for difference between NEXUS and apk cron service is running...", 'autoNotificationDifferenceKm');

        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $emails = Car::getCarParkAdminEmailsList();
        $apkKms = [];
        $nexusKms = [];
        $rompetrolKms = [];
        $carsWithDiffKm = [];

        $cars = Car::findAllByAttributes(['deleted' => 0]);
        foreach ($cars as $key => $car) {
            $carKms = CarKm::find()
                ->where(['car_id' => $car->id, 'deleted' => 0])
                ->andWhere(['LIKE', 'added', "%{$yesterday}%", false])
                ->all();
            $nexusKm = null;
            $appKm = null;
            foreach ($carKms as $carKm) {
                switch ($carKm->source) {
                    case CarKm::SOURCE_APPLICATION:
                        $apkKms[$car->id] = $carKm->km;
                        $appKm = $carKm->km;
                        break;
                    case CarKm::SOURCE_NEXUS:
                        $nexusKms[$car->id] = $carKm->km;
                        $nexusKm = $carKm->km;
                        break;
                    case CarKm::SOURCE_ROMPETROL:
                        $rompetrolKms[$car->id] = $carKm->km;
                }
            }
            if (isset($apkKms[$car->id]) && isset($nexusKms[$car->id]) && abs($apkKms[$car->id] - $nexusKms[$car->id]) > 50) {
                $carsWithDiffKm[$car->plate_number] = abs($apkKms[$car->id] - $nexusKms[$car->id]);
            } else if (isset($rompetrolKms[$car->id]) && isset($nexusKms[$car->id]) && abs($nexusKms[$car->id] - $rompetrolKms[$car->id]) > 50) {
                $carsWithDiffKm[$car->plate_number] = abs($nexusKms[$car->id] - $rompetrolKms[$car->id]);
            }

            if (!empty($carsWithDiffKm[$car->plate_number])) {
                Event::checkForEventAndCreateOrUpdateEvent(
                    [
                        'searchAttributes' => [
                            'car_id' => $car->id,
                            'event_type' => Event::CAR_NEXUS_VS_APP_KM
                        ],
                        'createAttributes' => [
                            'car_id' => $car->id,
                            'event_type' => Event::CAR_NEXUS_VS_APP_KM,
                            'deleted' => 0
                        ],
                        'updateAttributes' => [
                            'car_id' => $car->id,
                        ]
                    ],
                    [
                        'searchAttributes' => [
                            'car_id' => $car->id
                        ],
                        'createAttributes' => [
                            'car_id' => $car->id,
                            'plate_number' => $car->plate_number,
                            'nexus_km' => $nexusKm,
                            'app_km' => $appKm,
                            'nexus_app_threshold' => 50
                        ],
                        'updateAttributes' => [
                            'nexus_km' => $nexusKm,
                            'app_km' => $appKm,
                        ]
                    ]
                );
            }
        }

        if (!empty($carsWithDiffKm)) {
            foreach ($emails as $email) {
                self::sendEmail($carsWithDiffKm, $email);
            }
        }

        return ExitCode::OK;
    }

    public function sendEmail($plateNumbers, $emailAdmin)
    {
        $contentForEmail = [];
        foreach ($plateNumbers as $key => $number) {
            $contentForEmail[] = Yii::t('cmd-auto', 'For car <b>{plateNumber}</b> there is a difference of {km} km on date {date}.', [
                    'plateNumber' => $key,
                    'km' => $number,
                    'date' => date('Y-m-d', strtotime('-1 day'))
                ]) . "<br>";
        }

        if (
            !empty(Yii::$app->params['erp_beneficiary_name'])
            && Yii::$app->params['erp_beneficiary_name'] === 'ghallard'
        ) {
            Yii::$app->mailer->compose('@app/mail/auto-notification-difference-km-html', [
                'contentForEmail' => $contentForEmail
            ])
                ->setFrom('econfaire@ghallard.ro')
                ->setTo($emailAdmin)
                ->setSubject(Yii::t('cmd-auto', "Nexus kilometers difference"))
                ->send();
        } else {
            $sendEmail = new SendSharePointMailHelper();
            $sendEmail->subject = Yii::t('cmd-auto', "Nexus kilometers difference");
            $sendEmail->content = [
                "contentType" => "html",
                "content" => $this->renderPartial('auto-notification-difference-km-html', [
                    'contentForEmail' => $contentForEmail
                ])
            ];
            $sendEmail->toRecipients = [
                [
                    "emailAddress" => [
                        "name" => 'Admin',
                        "address" => $emailAdmin,
                    ]
                ]
            ];
            $sendEmail->sendEmail();
        }
        echo Yii::t('cmd-auto', 'Email has been sent');
    }
}