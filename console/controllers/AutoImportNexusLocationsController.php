<?php

namespace console\controllers;

use backend\components\GeometrySphericalUtil;
use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use backend\modules\auto\models\Car;
use backend\modules\auto\models\Event;
use backend\modules\auto\models\Location;
use backend\modules\auto\models\LocationCircle;
use backend\modules\auto\models\LocationType;
use backend\modules\crm\models\Company;
use common\components\HttpStatus;
use common\components\SendSharePointMailHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\console\ExitCode;

class AutoImportNexusLocationsController extends Controller
{
    /**
     * @param $company
     * @return int
     * @throws GuzzleException
     * @throws \yii\web\BadRequestHttpException
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function actionIndex($company)
    {
        $this->setViewPath('@app/mail');
        $superAdmin = User::getSuperAdmin();

        Yii::info("\nImport NEXUS locations list cron service is running...", 'nexusLocationsListImport');

        $emails = Car::getCarParkAdminEmailsList();

        $carsIDs = Car::find()->select('gps_car_id, id, plate_number')->where(['IS NOT', 'gps_car_id', null])->asArray()->all();

        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $url = NEXUS_API_ENTRYPOINT . '?' . NEXUS_API_FETCH_JOURNEYS . '=' . (Company::getApiKeyByCompanyId($company) === false ? '' : Company::getApiKeyByCompanyId($company));
        if (Company::getApiKeyByCompanyId($company) === false) {
            Yii::error("\n" . Yii::t('cmd-auto', 'The company id is wrong'), 'nexusLocationsListImport');
            exit();
        }
        $postData = [];
        $newLocations = [];
        $postData[] = "from={$yesterday} 00:00:00";
        $postData[] = "to={$yesterday} 23:59:59";

        if (!empty($carsIDs)) {
            foreach ($carsIDs as $key => $car) {
                $postData[] = "cars[{$key}]={$car['gps_car_id']}";
            }
        }

        $client = new Client();
        $urlComplete = $url . '&' . implode('&', $postData);
        $res = $client->request('POST', $urlComplete);
        if ($res->getStatusCode() != HttpStatus::OK) {
            Yii::error("\nBad response  received from NEXUS api.", 'nexusLocationsListImport');
            Yii::error("\n" . json_encode($res), 'nexusLocationsListImport');
            return ExitCode::NOHOST;
        } else {
            $journeys = json_decode($res->getBody(), true);
        }
        if (empty($journeys)) {
            Yii::info("\n" . Yii::t('cmd-auto', 'No journeys received from Nexus. Please try again later, or contact them.'), 'nexusLocationsListImport');
            return ExitCode::OK;
        } else {
            foreach ($journeys as $carJourneys) {
                if (empty($carJourneys['journeys'])) {
                    $name = !empty($carJourneys['name']) ? $carJourneys['name'] : '-';
                    Yii::info("\n" . Yii::t('cmd-auto', "\n No journeys found for car {name}", ['name' => $name]), 'nexusLocationsListImport');
                    continue;
                }

                $carID = Car::find()->where(['gps_car_id' => $carJourneys['id']])->one();
                $locationIds = [];

                foreach ($carJourneys['journeys'] as $journey) {
                    $lastLocationId = Location::find()->where(['deleted' => 0])->orderBy(['id' => SORT_DESC])->one();

                    $modelsStartLocations = Location::getLocationByCoordinates([
                        'address' => $journey['start']['location'],
                        'latitude' => $journey['start']['lat'],
                        'longitude' => $journey['start']['lon'],
                        'type' => 'START',
                    ]);
                    if ($modelsStartLocations !== false) {
                        continue;
                    }

                    $modelStartLocation = new Location();
                    $modelStartLocation->name = empty($lastLocationId) ? 'HotSpot-1' : 'HotSpot-' . $lastLocationId->id += 1;
                    $modelStartLocation->first_car_id = !empty($carID) ? $carID->id : null;
                    $modelStartLocation->address = Location::reverseAddressWords($journey['start']['location']);
                    $modelStartLocation->description = !empty($carID) ? $carID->plate_number : null;
                    $modelStartLocation->latitude = $journey['start']['lat'];
                    $modelStartLocation->longitude = $journey['start']['lon'];
                    $modelStartLocation->perimeter_shape_id = LocationType::CIRCLE_TYPE;
                    $modelStartLocation->visits = 1;
                    $modelStartLocation->added = date('Y-m-d H:i:s');
                    $modelStartLocation->added_by = $superAdmin;
                    $newLocations[] = $modelStartLocation->address;
                    if (!$modelStartLocation->save()) {
                        if ($modelStartLocation->hasErrors()) {
                            foreach ($modelStartLocation->errors as $error) {
                                Yii::error("\n" . Yii::t('cmd-auto', $error[0]), 'nexusLocationsListImport');
                            }
                        }
                    }
                    $locationIds[] = $modelStartLocation->id;
                    LocationCircle::addNewLocationCircle($modelStartLocation);

                    $modelsStopLocations = Location::getLocationByCoordinates([
                        'address' => $journey['stop']['location'],
                        'latitude' => $journey['stop']['lat'],
                        'longitude' => $journey['stop']['lon'],
                        'type' => 'STOP',
                    ]);
                    if ($modelsStopLocations !== false) {
                        continue;
                    }

                    $modelStopLocation = new Location();
                    $modelStopLocation->name = empty($lastLocationId) ? 'HotSpot-1' : 'HotSpot-' . $lastLocationId->id += 1;
                    $modelStopLocation->first_car_id = !empty($carID) ? $carID->id : null;
                    $modelStopLocation->address = Location::reverseAddressWords($journey['stop']['location']);
                    $modelStopLocation->description = !empty($carID) ? $carID->plate_number : null;
                    $modelStopLocation->latitude = $journey['stop']['lat'];
                    $modelStopLocation->longitude = $journey['stop']['lon'];
                    $modelStopLocation->perimeter_shape_id = LocationType::CIRCLE_TYPE;
                    $modelStopLocation->visits = 1;
                    $modelStopLocation->added = date('Y-m-d H:i:s');
                    $modelStopLocation->added_by = $superAdmin;
                    $newLocations[] = $modelStopLocation->address;
                    if (!$modelStopLocation->save()) {
                        if ($modelStopLocation->hasErrors()) {
                            foreach ($modelStopLocation->errors as $error) {
                                Yii::error("\n" . Yii::t('cmd-auto', $error[0]), 'nexusLocationsListImport');
                            }
                        }
                    }
                    $locationIds[] = $modelStopLocation->id;
                    LocationCircle::addNewLocationCircle($modelStopLocation);
                }
                if (count($locationIds) === 0) {
                    continue;
                }
                Event::checkForEventAndCreateOrUpdateEvent(
                    [
                        'searchAttributes' => [
                            'car_id' => $carID->id,
                            'event_type' => Event::NEW_LOCATION
                        ],
                        'createAttributes' => [
                            'car_id' => $carID->id,
                            'event_type' => Event::NEW_LOCATION,
                            'deleted' => 0
                        ],
                        'updateAttributes' => [
                            'car_id' => $carID->id,
                        ]
                    ],
                    [
                        'searchAttributes' => [
                            'car_id' => $carID->id,
                        ],
                        'createAttributes' => [
                            'car_id' => $carID->id,
                            'plate_number' => $carID->plate_number,
                            'count_new_locations_imported' => count($locationIds),
                            'new_locations_imported_ids' => implode(', ', $locationIds)
                        ],
                        'updateAttributes' => [
                            'count_new_locations_imported' => count($locationIds),
                            'new_locations_imported_ids' => implode(', ', $locationIds)
                        ]
                    ]
                );
            }
            if (!empty($newLocations)) {
                $unknownLocations = implode('<br>', $newLocations);
                foreach ($emails as $email) {
                    echo "\nWill send notification to '{$email}'\n";
                    Yii::info("\n" . Yii::t('cmd-auto', "Will send notification to {email}", ['email' => $email]), 'nexusLocationsListImport');
                    $this->sendExternalLocationImportNotification($unknownLocations, $email);
                    Yii::info("\n" . Yii::t('cmd-auto', "EMAIL SENT"), 'nexusLocationsListImport');
                    echo "\nEMAIL SENT\n";
                }
            }
        }
        return ExitCode::OK;
    }

    /**
     * send email notifications if exist new locations
     * @added 2022-06-07
     * @added_by Alex G.
     */
    public function sendExternalLocationImportNotification($unknownLocations, $emailAdmin)
    {
        if (
            !empty(Yii::$app->params['erp_beneficiary_name'])
            && Yii::$app->params['erp_beneficiary_name'] === 'ghallard'
        ) {
            Yii::$app->mailer->compose('@app/mail/auto-import-nexus-locations-html', [
                'unknownLocations' => $unknownLocations
            ])
                ->setFrom('econfaire@ghallard.ro')
                ->setTo($emailAdmin)
                ->setCc("marius.postolache@leviatan.ro")
                ->setSubject(Yii::t('cmd-auto', "NEXUS - Locations notification"))
                ->send();
        } else {
            $sendEmail = new SendSharePointMailHelper();
            $sendEmail->subject = Yii::t('cmd-auto', "NEXUS - Locations notification");
            $sendEmail->content = [
                "contentType" => "html",
                "content" => $this->renderPartial('auto-import-nexus-locations-html', [
                    'unknownLocations' => $unknownLocations
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
            $sendEmail->ccRecipients = [
                [
                    "emailAddress" => [
                        "name" => "Admin",
                        "address" => "marius.postolache@leviatan.ro"
                    ]
                ],
            ];
            $sendEmail->sendEmail();
        }
    }
}