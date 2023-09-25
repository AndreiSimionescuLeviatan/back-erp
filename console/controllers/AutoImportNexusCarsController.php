<?php

namespace console\controllers;

use backend\modules\auto\models\Car;
use backend\modules\auto\models\Event;
use backend\modules\crm\models\Company;
use common\components\HttpStatus;
use common\components\SendSharePointMailHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\console\ExitCode;

class AutoImportNexusCarsController extends Controller
{
    /**
     * @param $company
     * @return int
     * @throws GuzzleException
     * @throws \yii\web\BadRequestHttpException
     * @throws Exception
     */
    public function actionIndex($company)
    {
        Yii::info("\nImport NEXUS cars list cron service is running...", 'nexusCarsListImport');

        $emails = Car::getCarParkAdminEmailsList();

        $url = NEXUS_API_ENTRYPOINT . '?' . NEXUS_API_FETCH_VEHICLES . '=' . (Company::getApiKeyByCompanyId($company) === false ? '' : Company::getApiKeyByCompanyId($company));
        if (Company::getApiKeyByCompanyId($company) === false) {
            Yii::error("\n" . Yii::t('cmd-auto', 'The company id is wrong'), 'nexusCarsListImport');
            exit();
        }
        $client = new Client();
        $res = $client->request('POST', $url);
        if ($res->getStatusCode() != HttpStatus::OK) {
            Yii::error("\nNexus fetch car list error", 'nexusCarsListImport');
            return ExitCode::SOFTWARE;
        }
        $externalCarsList = json_decode($res->getBody(), true);
        $unknownCars = [];

        //after we get the car list from nexus we iterate over to:
        // - if we have any new car will notify the user
        foreach ($externalCarsList as $car) {
            if (empty($car['id'])) {
                $unknownCars[] = $car;
                Yii::info("\n" . Yii::t('cmd-auto', 'No id found for car. No processing will be done for this car.'), 'nexusCarsListImport');
                Yii::info("\n" . Yii::t('cmd-auto', 'Car data: ' . json_encode($car)), 'nexusCarsListImport');
                continue;
            }

            $externalCar = explode('_', $car['name']);
            //identify the car in our table
            $externalCarID = Car::find()->where(['gps_car_id' => $car['id']])->one();

            if ($externalCarID === null) {
                Event::checkForEventAndCreateOrUpdateEvent(
                    [
                        'searchAttributes' => [
                            'car_id' => $car['id'],
                            'event_type' => Event::MISSING_CAR_EVENT,
                            'deleted' => 0
                        ],
                        'createAttributes' => [
                            'car_id' => $car['id'],
                            'event_type' => Event::MISSING_CAR_EVENT,
                            'deleted' => 0,
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
                            'plate_number' => $car['name'],
                        ],
                        'updateAttributes' => [
                            'car_id' => $car['id'],
                        ]
                    ]
                );
            }

            $erpCarPlateNumber = Car::find()->where(['plate_number' => $externalCar[0]])->one();

            if (!empty($erpCarPlateNumber) && empty($externalCarID)) {
                $erpCarPlateNumber->gps_car_id = $car['id'];
                if (!$erpCarPlateNumber->save()) {
                    if ($erpCarPlateNumber->hasErrors()) {
                        foreach ($erpCarPlateNumber->errors as $error) {
                            Yii::error("\n" . Yii::t('cmd-auto', $error[0]), 'nexusCarsListImport');
                        }
                    }
                }
            }
            if (empty($externalCarID)) {
                $unknownCars[] = $car;
                Yii::info("\n" . Yii::t('cmd-auto', "The NEXUS car id {carId} not found in our car list. No processing will be done for this car.", ['carId' => $car['id']]), 'nexusCarsListImport');
            }
        }

        if (empty($unknownCars)) {
            Yii::info("\n" . Yii::t('cmd-auto', "\n All cars names from the NEXUS list are CORRECT"), 'nexusCarsListImport');
        } else {
            //SEND EMAIL WITH MESSAGE THAT WE DON'T HAVE A PLATE NUMBER FOR THIS CAR
            Yii::info("\n" . Yii::t('cmd-auto', "\n List of NEXUS cars not found in our DB"), 'nexusCarsListImport');

            $unknownCarsPlateNumber = [];
            foreach ($unknownCars as $unknownCar) {
                $unknownCarsPlateNumber[] = $unknownCar['name'];
                Yii::info("\n" . Yii::t('cmd-auto', "\n List of NEXUS cars not found in our DB {unknownCar}", ['unknownCar' => $unknownCar['name']]), 'nexusCarsListImport');
            }
            $unknownCarsPlateNumberEmail = implode('<br>', $unknownCarsPlateNumber);

            foreach ($emails as $email) {
                echo "\nWill send notification to '{$email}'\n";
                Yii::info("\n" . Yii::t('cmd-auto', "Will send notification to {email}", ['email' => $email]), 'nexusCarsListImport');
                $this->sendExternalCarImportNotification($unknownCarsPlateNumberEmail, $email);
                Yii::info("\n" . Yii::t('cmd-auto', "EMAIL SENT"), 'nexusCarsListImport');
                echo "\nEMAIL SENT\n";
            }
        }

        return ExitCode::OK;
    }

    /**
     * @param $unknownCarsPlateNumber
     * @param $emailAdmin
     * @throws \yii\web\BadRequestHttpException
     * send email notifications to import nexus cars admins
     * @added 2022-06-07
     * @added_by Alex G.
     */
    public function sendExternalCarImportNotification($unknownCarsPlateNumber, $emailAdmin)
    {
        if (
            !empty(Yii::$app->params['erp_beneficiary_name'])
            && Yii::$app->params['erp_beneficiary_name'] === 'ghallard'
        ) {
            Yii::$app->mailer->compose('@app/mail/auto-import-nexus-cars-html', [
                'unknownCarsPlateNumber' => $unknownCarsPlateNumber
            ])
                ->setFrom('econfaire@ghallard.ro')
                ->setTo($emailAdmin)
                ->setCc("marius.postolache@leviatan.ro")
                ->setSubject(Yii::t('cmd-auto', "NEXUS - Cars notification"))
                ->send();
        } else {
            $sendEmail = new SendSharePointMailHelper();
            $sendEmail->subject = Yii::t('cmd-auto', "NEXUS - Cars notification");
            $sendEmail->content = [
                "contentType" => "html",
                "content" => $this->renderPartial('auto-import-nexus-cars-html', [
                    'unknownCarsPlateNumber' => $unknownCarsPlateNumber
                ]),
            ];
            $sendEmail->toRecipients = [
                [
                    "emailAddress" => [
                        "name" => 'Admin',
                        "address" => $emailAdmin,
                    ]
                ]
            ];
            $sendEmail->ccRecipients = [
                [
                    "emailAddress" => [
                        "name" => 'Marius Postolache',
                        "address" => "marius.postolache@leviatan.ro"
                    ]
                ]
            ];
            $sendEmail->sendEmail();
        }
    }
}