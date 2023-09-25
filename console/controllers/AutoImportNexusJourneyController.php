<?php

namespace console\controllers;

use backend\components\GeometryPolyUtil;
use backend\components\GeometrySphericalUtil;
use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use backend\modules\auto\models\Car;
use backend\modules\auto\models\CarKm;
use backend\modules\auto\models\CarOperation;
use backend\modules\auto\models\Event;
use backend\modules\auto\models\LocationCircle;
use backend\modules\auto\models\LocationPolygon;
use backend\modules\auto\models\Journey;
use backend\modules\auto\models\Location;
use backend\modules\crm\models\Company;
use common\components\HttpStatus;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Exception;

class AutoImportNexusJourneyController extends Controller
{
    /**
     * @return false|string
     * @throws GuzzleException
     * @throws \yii\web\BadRequestHttpException
     * @throws Exception
     */
    public function actionIndex($company)
    {
        $superAdmin = User::getSuperAdmin();

        Yii::info("\nImport NEXUS journeys list cron service is running...", 'nexusJourneysListImport');
        $lastInserted = [];
        $carsIDs = Car::find()->select('gps_car_id, id, plate_number')->where(['IS NOT', 'gps_car_id', null])->asArray()->all();
        foreach ($carsIDs as $carsID) {
            $lastInsertedCarJourney = Journey::find()->where(['added_by' => $superAdmin, 'car_id' => $carsID])->orderBy(['added' => SORT_DESC])->one();
            if (!empty($lastInsertedCarJourney)) {
                $lastDate = explode(' ', $lastInsertedCarJourney->started);
                if (!empty($lastDate[0])) {
                    $lastInserted[] = $lastDate[0];

                }
            }
        }

        if (!empty($lastInserted)) {
            $date = max($lastInserted);
            $endDateJourney = new DateTime($date);
            $currentDate = new DateTime(date('Y-m-d'));
            $differenceDays = $currentDate->diff($endDateJourney);

            $lastDayImport = '';
            if ($differenceDays->format("%r%a")) {
                $lastDayImport = $differenceDays;
            }
            if (!empty($lastDayImport->days)) {
                $moreDaysStart = date('Y-m-d', strtotime("-{$lastDayImport->days} day"));
            } else {
                echo Yii::t('cmd-auto', 'Journeys was already imported');
//                exit();
            }
        }
        $today = date('Y-m-d');
        $repeatForLastDays = Settings::getValue('REPEAT_IMPORT_JOURNEYS', 10);
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $url = NEXUS_API_ENTRYPOINT . '?' . NEXUS_API_FETCH_JOURNEYS . '=' . (Company::getApiKeyByCompanyId($company) === false ? '' : Company::getApiKeyByCompanyId($company));
        if (Company::getApiKeyByCompanyId($company) === false) {
            Yii::error("\n" . Yii::t('cmd-auto', 'The company id is wrong'), 'nexusJourneysListImport');
            exit();
        }
        $postData = [];
        $postData[] = "from={$yesterday} 00:00:00";
        $postData[] = "to={$yesterday} 23:59:00";

        if (!empty($carsIDs)) {
            foreach ($carsIDs as $key => $car) {
                $postData[] = "cars[{$key}]={$car['gps_car_id']}";
            }
        }

        $urlComplete = $url . '&' . implode('&', $postData);
        $client = new Client();
        $res = $client->request('POST', $urlComplete);
        if ($res->getStatusCode() != HttpStatus::OK) {
            Yii::error("\nBad response  received from NEXUS api.", 'nexusJourneysListImport');
            Yii::error("\n" . json_encode($res), 'nexusJourneysListImport');
            return ExitCode::NOHOST;
        } else {
            $journeys = json_decode($res->getBody(), true);
        }
        if (empty($journeys)) {
            Yii::info("\n" . Yii::t('cmd-auto', "No journeys to import"), 'nexusJourneysListImport');
            return ExitCode::OK;
        }

        $routes = [];
        $foundCars = [];

        foreach ($carsIDs as $car) {
            $foundCars[$car['gps_car_id']] = $car;

            // set intervals for car who was assigned to one user
            $operations = CarOperation::find()->where("car_id = {$car['id']} AND operation_type_id IN(1,2)")->orderBy(['added' => SORT_DESC])->all();

            if (empty($operations)) {
                Yii::info("\n" . Yii::t('cmd-auto', "No check-in / check-out operations found for car {plateNumber}.", ['plateNumber' => $car['plate_number']]), 'nexusJourneysListImport');
                continue;
            }
            foreach ($operations as $operation) {
                if (!isset($newOperations[$operation['car_id']])) {
                    $newOperations[$operation['car_id']] = [
                        'check_in' => [],
                        'check_out' => []
                    ];
                }
                $_operationType = $operation['operation_type_id'] == 1 ? 'check_in' : 'check_out';
                $newOperations[$operation['car_id']][$_operationType][] = $operation->attributes;
            }
            // journey start intervals
            $journeysStarts[$car['id']] = [];

            $checkinOperations = $newOperations[$car['id']]['check_in'];
            if (empty($checkinOperations)) {
                Yii::info("\n" . Yii::t('cmd-auto', "No check-in operation found for car {plateNumber}.", ['plateNumber' => $car['plate_number']]), 'nexusJourneysListImport');
                continue;
            }
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $yesterdayStart = "{$yesterday} 00:00:00";
            $yesterdayStartTimestamp = strtotime($yesterdayStart);
            $yesterdayStop = "{$yesterday} 23:59:59";
            $yesterdayStopTimestamp = strtotime($yesterdayStop);

            $existingCarJourney = Journey::find()->one();
            foreach ($checkinOperations as $checkinOperation) {
                $added = strtotime($checkinOperation['added']);
                if ($existingCarJourney === null) {
                    $journeysStarts[$car['id']][] = [
                        'id' => $checkinOperation['id'],
                        'user_id' => $checkinOperation['user_id'],
                        'car_id' => $checkinOperation['car_id'],
                        'added' => $checkinOperation['added'],
                        'added_by' => $checkinOperation['added_by'],
                    ];
                } elseif ($yesterdayStartTimestamp <= $added && $added <= $yesterdayStopTimestamp) {
                    $journeysStarts[$car['id']][] = $checkinOperation;
                } elseif ($yesterdayStartTimestamp > $added) {
                    $journeysStarts[$car['id']][] = $checkinOperation;
                    break;
                }
            }

            // journey stops intervals
            $journeysStops[$car['id']] = [];
            $checkoutOperations = $newOperations[$car['id']]['check_out'];
            if (empty($checkoutOperations)) {
                Yii::info("\n" . Yii::t('cmd-auto', "No check-out operation found for car {$car['plate_number']}."), 'nexusJourneysListImport');
                continue;
            }
            foreach ($checkoutOperations as $checkoutOperation) {
                $added = strtotime($checkoutOperation['added']);
                if ($yesterdayStartTimestamp <= $added && $added <= $yesterdayStopTimestamp) {
                    $journeysStops[$car['id']][] = $checkoutOperation;
                } elseif ($yesterdayStopTimestamp < $added) {
                    $journeysStops[$car['id']][] = $checkoutOperation;
                    break;
                }
            }
        }

        // set start interval for journey

        foreach ($journeysStarts as $carID => $carStarts) {
            if (!empty($carStarts)) {
                $employeeIntervals[$carID] = [];
                for ($i = 0; $i < count($carStarts); $i++) {
                    $operationsCheck = CarOperation::find()->where("car_id = {$carStarts[$i]['car_id']} AND user_id = {$carStarts[$i]['user_id']} AND operation_type_id = 2")->one();
                    if (!empty($journeysStops[$carID][$i])) {
                        $operationsCheckOut = CarOperation::find()->where("car_id = {$journeysStops[$carID][$i]['car_id']} AND user_id = {$journeysStops[$carID][$i]['user_id']} AND operation_type_id = 2")->one();
                    }
                    $timeOut = [];
                    if (!empty($operationsCheck)) {
                        $timeOut = strtotime($operationsCheck['added']);
                    }
                    if (!isset($journeysStops[$carID][$i])) {
                        if ($timeOut > $yesterdayStartTimestamp) {
                            $employeeIntervals[$carID][] = [
                                'employee_id' => $carStarts[$i]['user_id'],
                                'start' => strtotime($carStarts[$i]['added']) < $yesterdayStartTimestamp ? $yesterdayStart : $carStarts[$i]['added'],
                                'stop' => !empty($operationsCheckOut) && $operationsCheckOut->car_id == $carStarts[$i]['car_id'] ? $operationsCheckOut->added : $yesterdayStop
                            ];
                        }
                        $plateNumber = Car::find()->where(['id' => $carID])->one()->plate_number;
                        Yii::info("\nNo valid interval found for car " . $plateNumber, 'nexusJourneysListImport');
                        continue;
                    }
                    $employeeIntervals[$carID][] = [
                        'employee_id' => $carStarts[$i]['user_id'],
                        'start' => strtotime($carStarts[$i]['added']) < $yesterdayStartTimestamp ? $yesterdayStart : $carStarts[$i]['added'],
                        'stop' => $yesterdayStop
                    ];
                }
            }
        }
        $carKms = [];
        foreach ($journeys as $carJourneys) {
            if (empty($carJourneys['journeys'])) {
                $name = !empty($carJourneys['name']) ? $carJourneys['name'] : '-';
                Yii::info("\n" . Yii::t('cmd-auto', "\n No journeys found for car {name}", ['name' => $name]), 'nexusJourneysListImport');
                continue;
            }
            Yii::info("\n" . Yii::t('cmd-auto', "\n Journeys found for car {name}", ['name' => $carJourneys['name']]), 'nexusJourneysListImport');

            foreach ($carJourneys['journeys'] as $journey) {
                if (empty($journey['start']) || empty($journey['stop'])) {
                    Yii::info("\n" . Yii::t('cmd-auto', "Wrong journey found for car {name} \n with id {id}", [
                        'name' => $carJourneys['name'],
                        'id' => $carJourneys['id'],
                        ]), 'nexusJourneysListImport');
                    continue;
                }

                if (!empty($employeeIntervals[$foundCars[$carJourneys['id']]['id']])) {
                    foreach ($employeeIntervals[$foundCars[$carJourneys['id']]['id']] as $interval) {
                        if (strtotime($journey['start']['time']) >= strtotime($interval['start']) && strtotime($journey['start']['time']) <= strtotime($interval['stop'])) {
                            $employeeID = (int)$interval['employee_id'];
                            break;
                        } else {
                            $employeeID = $interval['employee_id'];
                        }
                    }
                } else {
                    $foundCarId = Car::find()->where(['id' => $foundCars[$carJourneys['id']]['id']])->one();
                    if (!empty($foundCarId)) {
                        $foundUser = CarOperation::find()->where(['in', 'car_id', $foundCarId->id])->orderBy(['added' => SORT_DESC])->one();
                        if (!empty($foundUser) && $foundUser->operation_type_id != 2) {
                            $employeeID = $foundUser->user_id;
                        } else {
                            $employeeID = $superAdmin;
                        }
                    }
                }

                $modelsStartLocations = Location::getLocationByCoordinates([
                    'address' => $journey['start']['location'],
                    'latitude' => $journey['start']['lat'],
                    'longitude' => $journey['start']['lon'],
                    'type' => 'START',
                ]);
                if ($modelsStartLocations === false) {
                    continue;
                }

                $modelsStopLocations = Location::getLocationByCoordinates([
                    'address' => $journey['stop']['location'],
                    'latitude' => $journey['stop']['lat'],
                    'longitude' => $journey['stop']['lon'],
                    'type' => 'STOP',
                ]);
                if ($modelsStopLocations === false) {
                    continue;
                }

                $operationsInOut[$foundCars[$carJourneys['id']]['id'] . '_' . $employeeID] = [];
                $journeyStart = $journey['start']['time'];
                $carOperations = CarOperation::find()
                    ->where(['car_id' => $foundCars[$carJourneys['id']]['id']])
                    ->orderBy(['added' => SORT_DESC])
                    ->all();

                $lastAction = CarOperation::find()->where([
                    'car_id' => $foundCars[$carJourneys['id']]['id'],
                    'user_id' => $employeeID
                ])->andWhere(['<', 'added', $journey['start']['time']])
                    ->orderBy(['added' => SORT_DESC])
                    ->select('added, operation_type_id')
                    ->one();

                $addedIn = null;
                $addedOut = null;
                foreach ($carOperations as $carOperation) {
                    if ($carOperation['operation_type_id'] == 1) {
                        $addedIn = date('Y-m-d H:i:s', strtotime($carOperation['added']));
                    }
                    if ($carOperation['operation_type_id'] == 2) {
                        $addedOut = date('Y-m-d H:i:s', strtotime($carOperation['added']));
                    }
                    if (!empty($addedIn) && !empty($addedOut)) {
                        array_push($operationsInOut[$foundCars[$carJourneys['id']]['id'] . '_' . $employeeID], $addedIn . '_' . $addedOut);
                    }
                }

                if ($journey['distance'] == 0 && $journey['start']['isVia'] == 1 && $journey['stop']['isVia'] == 1) {
                    Yii::info("\n" . Yii::t('cmd-auto', "\n Journey with distance 0"), 'nexusJourneysListImport');
                    continue;
                }

                $newRoute = [
                    'isViaStart' => $journey['start']['isVia'],
                    'isViaStop' => $journey['stop']['isVia'],
                    'car_id' => $foundCars[$carJourneys['id']]['id'],
                    'started' => $journey['start']['time'],
                    'start_hotspot_id' => $modelsStartLocations->id,
                    'stopped' => $journey['stop']['time'],
                    'stop_hotspot_id' => $modelsStopLocations->id,
                    'distance' => $journey['distance'],
                    'fuel' => $journey['fuel'],
                    'start_odo' => (float)$journey['odo'] - (float)$journey['distance'],
                    'odo' => $journey['odo'],
                    'user_id' => $superAdmin,
                    'project_id' => null,
                    'observation' => $carJourneys['name'],
                    'status' => 0,
                    'time' => $journey['time'],
                    'stand_time' => $journey['standTime'],
                    'exploit' => $journey['exploit'],
                    'speed' => $journey['speed'],
                    'mark' => $journey['mark'],
                    'added' => date('Y-m-d H:i:s'),
                    'added_by' => $superAdmin
                ];

                foreach ($operationsInOut[$foundCars[$carJourneys['id']]['id'] . '_' . $employeeID] as $operation) {
                    $dateIn = explode('_', $operation)[0];
                    $dateOut = explode('_', $operation)[1];

                    if (!empty($dateIn)) {
                        if (strtotime($dateOut) <= strtotime($journeyStart) && strtotime($journeyStart) <= strtotime($dateIn)) {
                            $newRoute['user_id'] = $employeeID;
                        } else {
                            $modelCarOperation = CarOperation::find()
                                ->where(['<', 'added', $journeyStart])
                                ->andWhere(['car_id' => $foundCars[$carJourneys['id']]['id']])
                                ->orderBy(['added' => SORT_DESC])
                                ->one();
                            if (!empty($modelCarOperation) && $modelCarOperation->operation_type_id == 1) {
                                $newRoute['user_id'] = $modelCarOperation->user_id;
                            } else {
                                $newRoute['user_id'] = $superAdmin;
                            }
                        }
                        if ($lastAction!== null && $lastAction['operation_type_id'] == 1 && strtotime($journeyStart) > strtotime($lastAction['added'])) {
                            $newRoute['user_id'] = $employeeID;
                        }
                    }
                    if ($dateOut == '') {
                        $lastCheckOut = CarOperation::find()
                            ->where(['car_id' => $foundCars[$carJourneys['id']]['id'], 'user_id' => $employeeID, 'operation_type_id' => 2])
                            ->orderBy(['added' => SORT_ASC])
                            ->one();
                        if ($lastCheckOut !== null) {
                            if (strtotime($lastCheckOut['added']) > strtotime($journey['start']['time'])) {
                                $newRoute['user_id'] = $superAdmin;
                            } else {
                                $newRoute['user_id'] = $employeeID;
                            }
                        }
                    }
                }
                if (count($carOperations) === 1) {
                    if ($carOperations[0]['operation_type_id'] == 1 && $journeyStart > $carOperations[0]['added']) {
                        $newRoute['user_id'] = $employeeID;
                    }
                }

                if (empty($routes[$foundCars[$carJourneys['id']]['id']])) {
                    $routes[$foundCars[$carJourneys['id']]['id']] = [];
                }
                $routes[$foundCars[$carJourneys['id']]['id']][] = $newRoute;
                $carKms[$foundCars[$carJourneys['id']]["id"] . '_' . explode(' ', $journey['start']['time'])[0]][] = $newRoute['odo'];
            }
        }

        $cars = Car::find()->where(['deleted' => 0])->count();
        for ($x = 1; $x <= $cars + 1; $x++) {
            $newJourney = null;
            if (isset($routes[$x])) {
                $count = count($routes[$x]) - 1;
                for ($i = 0; $i < $count; $i++) {
                    if ( $routes[$x][$i]['isViaStart'] == 0 && $routes[$x][$i]['isViaStop'] == 1 && $routes[$x][$i + 1]['isViaStart'] == 1 && $routes[$x][$i + 1]['isViaStop'] == 1
                        || $routes[$x][$i]['isViaStart'] == 0 && $routes[$x][$i]['isViaStop'] == 1 && $routes[$x][$i + 1]['isViaStart'] == 1 && $routes[$x][$i + 1]['isViaStop'] == 0
                        || $routes[$x][$i]['isViaStart'] == 0 && $routes[$x][$i]['isViaStop'] == 0 && $routes[$x][$i + 1]['isViaStart'] == 1 && $routes[$x][$i + 1]['isViaStop'] == 0
                        && $routes[$x][$i]['stop_hotspot_id'] == $routes[$x][$i + 1]['start_hotspot_id']
                        && $routes[$x][$i]['start_hotspot_id'] != $routes[$x][$i + 1]['stop_hotspot_id'] ) {
                        $startDay = explode(' ', explode('-', $routes[$x][$i]['started'])[2])[0];
                        $stopDay = explode(' ', explode('-', $routes[$x][$i + 1]['stopped'])[2])[0];
                        if ($startDay == $stopDay) {
                            $newJourney = [
                                'isViaStart' => $routes[$x][$i]['isViaStart'],
                                'isViaStop' => $routes[$x][$i + 1]['isViaStop'],
                                'car_id' => $routes[$x][$i]['car_id'],
                                'started' => $routes[$x][$i]['started'],
                                'start_hotspot_id' => $routes[$x][$i]['start_hotspot_id'],
                                'stopped' => $routes[$x][$i + 1]['stopped'],
                                'stop_hotspot_id' => $routes[$x][$i + 1]['stop_hotspot_id'],
                                'distance' => $routes[$x][$i]['distance'] + $routes[$x][$i + 1]['distance'],
                                'fuel' => $routes[$x][$i]['fuel'],
                                'start_odo' => (float)$routes[$x][$i + 1]['odo'] - ((float)$routes[$x][$i]['distance'] + (float)$routes[$x][$i + 1]['distance']),
                                'odo' => $routes[$x][$i + 1]['odo'],
                                'user_id' => $routes[$x][$i]['user_id'],
                                'project_id' => $routes[$x][$i]['project_id'],
                                'observation' => $routes[$x][$i]['observation'],
                                'status' => $routes[$x][$i]['status'],
                                'time' => strtotime($routes[$x][$i + 1]['stopped']) - strtotime($routes[$x][$i]['started']),
                                'stand_time' => $routes[$x][$i]['stand_time'] + $routes[$x][$i + 1]['stand_time'],
                                'exploit' => $routes[$x][$i]['exploit'],
                                'speed' => $routes[$x][$i]['speed'],
                                'mark' => $routes[$x][$i]['mark'],
                                'added' => $routes[$x][$i]['added'],
                                'added_by' => $routes[$x][$i]['added_by']
                            ];
                            unset($routes[$x][$i]);
                            $routes[$x][$i + 1] = $newJourney;
                        }
                    }
                }
            }
        }

        for ($x = 1; $x <= $cars + 1; $x++) {
            $newJourney = null;
            if (isset($routes[$x])) {
                $carId = null;
                foreach ($routes[$x] as $key => $value) {
                    $keys = array_keys($routes[$x]);
                    $nextKey = null;
                    foreach ($keys as $myKey) {
                        if (isset($routes[$x][$myKey]) && $myKey > $key) {
                            $nextKey = $myKey;
                            break;
                        }
                    }
                    if ($nextKey !== null && isset($routes[$x][$key])) {
                        $sec = null;
                        if ($routes[$x][$key]['isViaStart'] == 0) {
                            $startHour = explode(' ', $routes[$x][$key]['started'])[1];
                            $stopHour = explode(' ', $routes[$x][$key]['stopped'])[1];
                            $sec = strtotime($stopHour) - strtotime($startHour);
                        }
                        $standTime = strtotime($routes[$x][$nextKey]['started']) - strtotime($routes[$x][$key]['stopped']);
                        $carId = $routes[$x][$key]['car_id'];
                        if ($routes[$x][$key]['stop_hotspot_id'] === $routes[$x][$nextKey]['start_hotspot_id']) {
                            if ( $sec < 600 && $standTime < 1800
                                || $sec == null && $standTime < 1800 ) {
                                $startDay = explode(' ', explode('-', $routes[$x][$key]['started'])[2])[0];
                                $stopDay = explode(' ', explode('-', $routes[$x][$nextKey]['stopped'])[2])[0];
                                if ($startDay == $stopDay) {
                                    $newJourney = [
                                        'isViaStart' => $routes[$x][$key]['isViaStart'],
                                        'isViaStop' => $routes[$x][$key]['isViaStop'],
                                        'car_id' => $routes[$x][$key]['car_id'],
                                        'started' => $routes[$x][$key]['started'],
                                        'start_hotspot_id' => $routes[$x][$key]['start_hotspot_id'],
                                        'stopped' => $routes[$x][$nextKey]['stopped'],
                                        'stop_hotspot_id' => $routes[$x][$nextKey]['stop_hotspot_id'],
                                        'distance' => $routes[$x][$key]['distance'] + $routes[$x][$nextKey]['distance'],
                                        'fuel' => $routes[$x][$key]['fuel'],
                                        'start_odo' => (float)$routes[$x][$nextKey]['odo'] - ((float)$routes[$x][$key]['distance'] + (float)$routes[$x][$nextKey]['distance']),
                                        'odo' => $routes[$x][$nextKey]['odo'],
                                        'user_id' => $routes[$x][$key]['user_id'],
                                        'project_id' => $routes[$x][$key]['project_id'],
                                        'observation' => $routes[$x][$key]['observation'],
                                        'status' => $routes[$x][$key]['status'],
                                        'time' => strtotime($routes[$x][$nextKey]['stopped']) - strtotime($routes[$x][$key]['started']),
                                        'stand_time' => $routes[$x][$key]['stand_time'] + $routes[$x][$nextKey]['stand_time'],
                                        'exploit' => $routes[$x][$key]['exploit'],
                                        'speed' => $routes[$x][$key]['speed'],
                                        'mark' => $routes[$x][$key]['mark'],
                                        'added' => $routes[$x][$key]['added'],
                                        'added_by' => $routes[$x][$key]['added_by']
                                    ];
                                    unset($routes[$x][$key]);
                                    $routes[$x][$nextKey] = $newJourney;
                                }
                            }
                        }
                    }
                    $key = array_key_last($routes[$x]);
                    if (isset($routes[$x][$key]) && $routes[$x][$key]['start_hotspot_id'] === $routes[$x][$key]['stop_hotspot_id']) {
                        unset($routes[$x][$key]);
                    }
                }
                foreach ($routes[$x] as $key => $route) {
                    if ($route['start_hotspot_id'] === $route['stop_hotspot_id'] && $route['distance'] == 0) {
                        unset($routes[$x][$key]);
                    }
                }
                if ($carId === null) {
                    continue;
                }
                $carModel = Car::findOneByAttributes(['id' => $carId]);
                Event::checkForEventAndCreateOrUpdateEvent(
                    [
                        'searchAttributes' => [
                            'car_id' => $carModel->id,
                            'event_type' => Event::JOURNEYS_IMPORTED
                        ],
                        'createAttributes' => [
                            'car_id' => $carModel->id,
                            'event_type' => Event::JOURNEYS_IMPORTED,
                            'deleted' => 0
                        ],
                        'updateAttributes' => [
                            'car_id' => $carModel->id,
                        ]
                    ],
                    [
                        'searchAttributes' => [
                            'car_id' => $carModel->id
                        ],
                        'createAttributes' => [
                            'car_id' => $carModel->id,
                            'plate_number' => $carModel->plate_number,
                            'count_journeys_imported' => count($routes[$x])
                        ],
                        'updateAttributes' => [
                            'count_journeys_imported' => count($routes[$x])
                        ]
                    ]
                );
            }
        }

        $this->saveJourneys($routes, $carKms, $company, $superAdmin);
        echo "\nJourneys was imported\n";

        return ExitCode::OK;
    }

    /**
     * @param $routes
     */
    public function saveJourneys($routes, $carKms, $company, $superAdmin)
    {
        foreach ($routes as $carRoutes) {
            foreach ($carRoutes as $route) {
                $date = date("Y-m-d");
                $existingDateImport = Journey::find()->where("`added` LIKE '%{$date}%'")->andWhere(['car_id' => $route['car_id']])->one();

                $existingJourney = Journey::find()->where([
                    'car_id' => $route['car_id'],
                    'started' => $route['started'],
                    'stopped' => $route['stopped'],
                    'start_hotspot_id' => $route['start_hotspot_id'],
                    'stop_hotspot_id' => $route['stop_hotspot_id'],
                ])->asArray()->one();

                $locationStart = Location::findOneByAttributes([
                    'id' => $route['start_hotspot_id']
                ]);
                $locationStop = Location::findOneByAttributes([
                    'id' => $route['stop_hotspot_id']
                ]);
                if ($locationStart !== null) {
                    $locationStart->visits += 1;
                    $locationStart->save();
                }
                if ($locationStop !== null) {
                    $locationStop->visits += 1;
                    $locationStop->save();
                }

                $existingStarted = Journey::findAllByAttributes([
                    'started' => $route['started'],
                    'car_id' => $route['car_id']
                ]);
                $existingStopped = Journey::findAllByAttributes([
                    'stopped' => $route['stopped'],
                    'car_id' => $route['car_id']
                ]);

                if (!empty($existingJourney)) {
                    Yii::info("\n" . Yii::t('cmd-auto', "\n Existing journey with car id: {id}", ['id' => $route['car_id']]), 'nexusJourneysListImport');
                    continue;
                }

                if (!empty($existingStarted) || !empty($existingStopped)) {
                    Yii::info("\n" . Yii::t('cmd-auto', "\n Existing journey with car id started or stopped: {id}, {started}, {stopped}", [
                        'id' => $route['car_id'],
                        'started' => $route['started'],
                        'stopped' => $route['stopped']
                        ]), 'nexusJourneysListImport');
                    continue;
                }

                $model = new Journey();
                $model->car_id = $route['car_id'];
                $model->started = $route['started'];
                $model->start_hotspot_id = $route['start_hotspot_id'];
                $model->stopped = $route['stopped'];
                $model->stop_hotspot_id = $route['stop_hotspot_id'];
                $model->distance = round($route['distance']);
                $model->fuel = $route['fuel'];
                $model->start_odo = round($route['odo'] - $route['distance']);
                $model->odo = round($route['odo']);
                $model->user_id = $route['user_id'];
                $model->project_id = $route['project_id'];
                $model->observation = $route['observation'];
                $model->status = $route['status'];
                $model->time = $route['time'];
                $model->stand_time = $route['stand_time'];
                $model->exploit = $route['exploit'];
                $model->speed = $route['speed'];
                $model->mark = $route['mark'];
                $model->added = $route['added'];
                $model->added_by = $route['added_by'];
                if (!$model->save()) {
                    if ($model->hasErrors()) {
                        foreach ($model->errors as $error) {
                            Yii::error("\n" . Yii::t('cmd-auto', $error[0]), 'nexusJourneysListImport');
                        }
                    }
                }
                if (isset($carKms[$model->car_id . '_' . explode(' ', $model->started)[0]])) {
                    $lastCarKm = CarKm::find()->where([
                        'deleted' => 0,
                        'car_id' => $model->car_id,
                    ])->andWhere(['LIKE', 'added', explode(' ', $model->started)[0]])->one();
                    if ($lastCarKm === null) {
                        $carKm = new CarKm();
                        $carKm->company_id = $company;
                        $carKm->car_id = $model->car_id;
                        $carKm->km = round(max($carKms[$model->car_id . '_' . explode(' ', $model->started)[0]]));
                        $carKm->source = CarKm::SOURCE_NEXUS;
                        $carKm->deleted = 0;
                        $carKm->added = $model->started;
                        $carKm->added_by = $superAdmin;
                        $carKm->save();
                    } else if ($lastCarKm->source != 2) {
                        $checkCarKm = CarKm::find()->where([
                            'deleted' => 0,
                            'car_id' => $model->car_id,
                            'source' => 2
                        ])->andWhere(['LIKE', 'added', explode(' ', $model->started)[0]])->one();
                        if ($checkCarKm === null) {
                            $carKm = new CarKm();
                            $carKm->company_id = $company;
                            $carKm->car_id = $model->car_id;
                            $carKm->km = round(max($carKms[$model->car_id . '_' . explode(' ', $model->started)[0]]));
                            $carKm->source = CarKm::SOURCE_NEXUS;
                            $carKm->deleted = 0;
                            $carKm->added = $model->started;
                            $carKm->added_by = $superAdmin;
                            $carKm->save();
                        }
                    }
                }

                if (!empty($existingDateImport)) {
                    Yii::info("\n" . Yii::t('cmd-auto', "For this date {date} for car_id {id} was inserted journeys.", ['date' => $date, 'id' => $route['car_id']]), 'nexusJourneysListImport');
                }
            }
        }
    }

    /**
     * @param $content
     * @param User $receiver
     * @return bool
     * @throws GraphException
     */
    protected
    function sendGraphMail($content, $receiver)
    {
        try {
            $guzzle = new Client();
            $url = 'https://login.microsoftonline.com/' . TENANT_ID . '/oauth2/token';
            $user_token = json_decode($guzzle->post($url, [
                'form_params' => [
                    'client_id' => CLIENT_ID,
                    'client_secret' => CLIENT_SECRET,
                    'resource' => 'https://graph.microsoft.com/',
                    'grant_type' => 'password',
                    'username' => SHARE_POINT_USERNAME,
                    'password' => SHARE_POINT_PASS,
                ],
            ])->getBody()->getContents());
            $user_accessToken = $user_token->access_token;
            $graph = new Graph();
            $graph->setAccessToken($user_accessToken);
            Yii::info("Received token from GRAPH.", 'qtyChangesNotifications');
        } catch (GuzzleException $exc) {
            Yii::error("Authenticating user error: {$exc->getMessage()}.", 'qtyChangesNotifications');
            Yii::error("Error Code: {$exc->getCode()}", 'qtyChangesNotifications');
            return false;
        }

        try {
            $user = $graph->createRequest("get", "/me")
                ->addHeaders(array("Content-Type" => "application/json"))
                ->setReturnType(\Microsoft\Graph\Model\User::class)
                ->setTimeout("100")
                ->execute();
            Yii::debug("AUTOMATE is now authenticated on GRAPH.", 'qtyChangesNotifications');
        } catch (GuzzleException $exc) {
            Yii::error("Authenticating AUTOMATE error: {$exc->getMessage()}.", 'qtyChangesNotifications');
            Yii::error("Error Code: {$exc->getCode()}", 'qtyChangesNotifications');
            return false;
        }

        $mailBody = array(
            "Message" => array(
                "subject" => "Liste cantități modificate în ultima ora",
                "body" => array(
                    "contentType" => "html",
                    "content" => $content
                ),
                "from" => array(
                    "emailAddress" => array(
                        "name" => $user->getDisplayName(),
                        "address" => $user->getMail()
                    )
                ),
                "toRecipients" => array(
                    array(
                        "emailAddress" => array(
                            "name" => $receiver->fullName(),
                            "address" => $receiver->email

                        )
                    )
                )
            )
        );

        try {
            if (
                !empty(Yii::$app->params['erp_beneficiary_name'])
                && Yii::$app->params['erp_beneficiary_name'] === 'ghallard'
            ) {
                Yii::$app->mailer->compose($content)
                    ->setFrom('econfaire@ghallard.ro')
                    ->setTo($receiver->email)
                    ->setSubject("Liste cantități modificate în ultima ora")
                    ->send();
                return true;
            } else {
                $email = $graph->createRequest("POST", "/me/sendMail")
                    ->attachBody($mailBody)
                    ->execute();
                if ((int)$email->getStatus() === 202) {
                    Yii::debug("Notified the user '{$receiver->email}", 'qtyChangesNotifications');
                    return true;
                }
            }
            Yii::error("Received an unexpected code '{$email->getStatus()}' when sending email", 'qtyChangesNotifications');
            return false;
        } catch (GuzzleException $exc) {
            Yii::error("Authenticating user error: {$exc->getMessage()}.", 'qtyChangesNotifications');
            Yii::error("Error Code: {$exc->getCode()}", 'qtyChangesNotifications');
            return false;
        }
    }
}