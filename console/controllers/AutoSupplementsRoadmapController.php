<?php


namespace console\controllers;

use backend\modules\auto\models\Car;
use backend\modules\auto\models\CarKm;
use backend\modules\auto\models\CarLocation;
use backend\modules\auto\models\Journey;
use backend\modules\auto\models\Location;
use backend\modules\auto\models\LocationDistance;
use backend\modules\auto\models\Roadmap;
use backend\modules\auto\models\RoadMapJourney;
use backend\modules\auto\models\TravelOrder;
use backend\modules\auto\models\TravelOrderLocation;
use backend\modules\auto\models\WorkPointLocation;
use backend\modules\hr\models\Employee;
use DateTime;
use yii\console\Controller;
use Yii;

class AutoSupplementsRoadmapController extends Controller
{
    private static $supplemetaryJourneyData = [];

    public function actionIndex($companyID, $year, $month)
    {
        !isset($companyID) ? Yii::info(Yii::t('cmd-auto', "Missing company id parameter"), 'roadMapGenerate') : '';
        !isset($year) ? $year = date('Y', strtotime('last month')) : '';
        !isset($month) ? $month = date('m', strtotime('last month')) : '';

        $roadmaps = Roadmap::find()
            ->where(['company_id' => $companyID])
            ->andWhere(['year' => $year])
            ->andWhere(['month' => $month])->all();
        $companyID == 1 ? $companyID = 2 : '';
        $officeCompany = Location::findOneByAttributes([
            'location_type_id' => Location::TYPE_COMPANY,
            'company_id' => $companyID
        ]);
        if ($officeCompany === null) {
            Yii::info(Yii::t('cmd-auto', "Company office location not found"), 'roadMapGenerate');
            return;
        }
        $officeCompanyID = $officeCompany->id;
        foreach ($roadmaps as $roadmap) {
            var_dump($roadmap['car_id']);
            $roadmapMonth = str_pad($month, 2, '0', STR_PAD_LEFT);
            $lastDay = date('t', strtotime($roadmap['year'] . '-' . $roadmapMonth . '-01'));
            $isJourneys = Journey::find()
                ->where(['car_id' => $roadmap['car_id']])
                ->andWhere(['>=', 'started', $roadmap['year'] . '-' . $roadmapMonth . '-01'])
                ->andWhere(['<=', 'started', $roadmap['year'] . '-' . $roadmapMonth . '-' . $lastDay])
                ->all();
            if (!empty($isJourneys)) {
                Roadmap::$roadmap = $roadmap;
                Journey::$officeLocation = $officeCompanyID;
                $car = Car::findOneByAttributes(['id' => $roadmap['car_id']]);
                if ($car !== null) {
                    $workPoint = WorkPointLocation::find()
                        ->select("location_id")
                        ->where(['work_point_location.work_point_id' => $car->work_point_id])
                        ->leftJoin(Location::tableName(), "location.id = work_point_location.location_id")
                        ->andWhere(['location.location_type_id' => Location::TYPE_COMPANY])
                        ->one();
                    if (!empty($workPoint)) {
                        $location = Location::findOneByAttributes(['id' => $workPoint['location_id']]);
                        if ($location !== null) {
                            Journey::$officeLocation = $location->id;
                        }
                    }
                }
                Journey::setDistanceAndTimeJourneyLocation(Journey::$officeLocation, $roadmap);
                $intervals = Employee::setMonthDaysWorkTime($roadmap['holder_id'], $year, $month);
                $dailyKms = CarKm::getDailyKms($roadmap['car_id'], $year, $month);
                if (empty($dailyKms)) {
                    continue;
                }

                foreach ($dailyKms as $date => $day) {
                    if ($day['km'] < Roadmap::MINIMUM_KM_ROADMAP_JOURNEY) {
                        $nextDay = date('Y-m-d', strtotime($date . ' +1 day'));
                        $prevDay = date('Y-m-d', strtotime($date . ' -1 day'));
                        if (isset($dailyKms[$nextDay])) {
                            $dailyKms[$date]['km'] = 0;
                            $dailyKms[$nextDay]['km'] += $day['km'];
                            $dailyKms[$nextDay]['start'] = $day['start'];
                        } elseif (isset($dailyKms[$prevDay])) {
                            $dailyKms[$date]['km'] = 0;
                            $dailyKms[$prevDay]['km'] += $day['km'];
                            $dailyKms[$prevDay]['stop'] = $day['stop'];
                        }
                    }
                }
                $totalUnAssignedKm = $firstDayKm = 0;
                foreach ($dailyKms as $date => $day) {
                    if ($firstDayKm == 0) {
                        $firstDayKm = $day['start'];
                    }
                    if (isset($intervals[$date])) {
                        continue;
                    }
                    $totalUnAssignedKm += $day['km'];
                }
                $deductibility = Car::findOneByAttributes(['id' => $roadmap['car_id']])['deductibility'];
                if (!empty($deductibility)) {
                    if ($deductibility == 100) {
                        $sql = "UPDATE road_map SET deductibility = 100 WHERE id = " . $roadmap['id'];
                        Roadmap::execute($sql);
                    } else {
                        $rand = rand(60, 80);
                        $totalUnAssignedKm = $totalUnAssignedKm * $rand / 100;
                        $sql = "UPDATE road_map SET deductibility = " . $rand . " WHERE id = " . $roadmap['id'];
                        Roadmap::execute($sql);
                    }
                }
                $dailyDetails = [];
                foreach ($intervals as $interval) {
                    $startTimestamp = strtotime($interval['start']);
                    $stopTimestamp = strtotime($interval['stop']);
                    $details = [
                        'date' => $interval['date'],
                        'start_time' => date('H:i:s', $startTimestamp),
                        'start_timestamp' => $startTimestamp,
                        'stop_time' => date('H:i:s', $stopTimestamp),
                        'stop_timestamp' => $stopTimestamp
                    ];
                    if (!isset($dailyKms[$interval['date']])) {
                        continue;
                    }

                    $details['start_km'] = $dailyKms[$interval['date']]['start'];
                    $details['stop_km'] = $dailyKms[$interval['date']]['stop'];
                    $details['km'] = $dailyKms[$interval['date']]['km'];

                    $dailyDetails[$interval['date']] = $details;
                }
                $car = Car::findOneByAttributes(['id' => $roadmap['car_id']]);
                if ($car === null) {
                    continue;
                }
                if (
                    Journey::$officeLocation != $officeCompanyID
                    && $car->office_return == Car::RETURN_OFFICE_YES
                ) {
                    $month = str_pad($month, 2, '0', STR_PAD_LEFT);
                    $days = date('t', strtotime($year . '-' . $month . '-01'));
                    $countSaturday = 1;
                    $countSunday = 1;
                    $journey = Journey::verifyIsJourneyTimeAndDistance($officeCompanyID, Journey::$officeLocation);
                    $time = LocationDistance::arithmeticMean($journey, "time");
                    $distance = LocationDistance::arithmeticMean($journey, "distance");
                    $distanceRoundTrip = $distance * 2;
                    $countWeekends = Journey::countWeekendsOfMonth($year, $month);
                    $numberWeekendsForReturn = 1;
                    while (
                        $totalUnAssignedKm > $distanceRoundTrip * $numberWeekendsForReturn
                        && $numberWeekendsForReturn <= $countWeekends
                    ) {
                        $numberWeekendsForReturn++;
                    }
                    $numberWeekendsForReturn--;
                    if ($numberWeekendsForReturn > 0) {
                        for ($i = 1; $i <= $days; $i++) {
                            $date = $i < 10 ? $year . '-' . $month . '-0' . $i : $year . '-' . $month . '-' . $i;
                            $startTimestamp = strtotime($date . ' 08:00:00');
                            $stopTimestamp = $startTimestamp + $time;
                            if ($i == 1 && date('w', strtotime($date)) == 0) {
                                continue;
                            }
                            if (date('w', strtotime($date)) == 6) {
                                if ($countSaturday <= $numberWeekendsForReturn) {
                                    $projectID = Journey::getProjectID($roadmap['holder_id']);
                                    $carKm = CarKm::findOneByAttributes(['car_id' => Roadmap::$roadmap['car_id'], 'date' => $date])['km'];
                                    $dailyDetails[$date]['date'] = $date;
                                    $dailyDetails[$date]['start_time'] = date('H:i:s', $startTimestamp);
                                    $dailyDetails[$date]['start_timestamp'] = $startTimestamp;
                                    $dailyDetails[$date]['stop_time'] = date('H:i:s', $stopTimestamp);
                                    $dailyDetails[$date]['stop_timestamp'] = $stopTimestamp;
                                    $dailyDetails[$date]['start_km'] = $carKm;
                                    $dailyDetails[$date]['stop_km'] = $carKm + $distance;
                                    $dailyDetails[$date]['km'] = $distance;
                                    $dailyDetails[$date]['journals'][] = self::addDailyDetails(false, true, Journey::$officeLocation, $officeCompanyID, $date, date('H:i:s', $startTimestamp), $startTimestamp, date('H:i:s', $stopTimestamp), $stopTimestamp, $dailyDetails[$date]['start_km'], $dailyDetails[$date]['start_km'] + $distance, $distance, $projectID, null, null);
                                }
                                $countSaturday++;
                            } else if (date('w', strtotime($date)) == 0) {
                                if ($countSunday <= $numberWeekendsForReturn) {
                                    $projectID = Journey::getProjectID($roadmap['holder_id']);
                                    $carKm = CarKm::findOneByAttributes(['car_id' => Roadmap::$roadmap['car_id'], 'date' => $date])['km'];
                                    $dailyDetails[$date]['date'] = $date;
                                    $dailyDetails[$date]['start_time'] = date('H:i:s', $startTimestamp);
                                    $dailyDetails[$date]['start_timestamp'] = $startTimestamp;
                                    $dailyDetails[$date]['stop_time'] = date('H:i:s', $stopTimestamp);
                                    $dailyDetails[$date]['stop_timestamp'] = $stopTimestamp;
                                    $dailyDetails[$date]['start_km'] = $carKm;
                                    $dailyDetails[$date]['stop_km'] = $carKm + $distance;
                                    $dailyDetails[$date]['km'] = $distance;
                                    $dailyDetails[$date]['journals'][] = self::addDailyDetails(false, true, $officeCompanyID, Journey::$officeLocation, $date, date('H:i:s', $startTimestamp), $startTimestamp, date('H:i:s', $stopTimestamp), $stopTimestamp, $dailyDetails[$date]['start_km'], $dailyDetails[$date]['start_km'] + $distance, $distance, $projectID, null, null);
                                }
                                $countSunday++;
                            }
                        }
                    }
                }

                $travelOrders = TravelOrder::find()
                    ->where(['car_id' => $roadmap['car_id']])
                    ->andWhere(['between', 'start_journey', $year . '-' . $month . '-01', $year . '-' . $month . '-31'])
                    ->all();
                $daysKms = 0;
                foreach ($travelOrders as $travelOrder) {
                    $travelOrderLocations = TravelOrderLocation::findAllByAttributes(['travel_order_id' => $travelOrder['id']]);
                    if (!empty($travelOrderLocations)) {
                        $current_date = date('Y-m-d', strtotime($travelOrder['start_journey'] . ' +1 day'));
                        while ($current_date < $travelOrder['stop_journey']) {
                            $date = $current_date;
                            if (isset($dailyDetails[$date])) {
                                $dailyDetails[$date]['journals'] = [];
                                $daysKms += $dailyDetails[$date]['km'];
                                $dailyDetails[$date]['km'] = 0;
                                $dailyDetails[$date]['travel_order'] = true;
                            }
                            $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
                        }

                        $date = $travelOrder['start_journey'];
                        $startTimestamp = strtotime($travelOrder['start_journey']);
                        $startLocation = Journey::$officeLocation;
                        foreach ($travelOrderLocations as $travelOrderLocation) {
                            if ($travelOrderLocation['location_id'] == Journey::$officeLocation) {
                                continue;
                            }
                            $stopLocation = $travelOrderLocation['location_id'];
                            $journeys = Journey::verifyIsJourneyTimeAndDistance($startLocation, $stopLocation);
                            if (empty($journeys)) {
                                continue;
                            }
                            $time = LocationDistance::arithmeticMean($journeys, "time");
                            $distance = LocationDistance::arithmeticMean($journeys, "distance");
                            $stopTimestamp = $startTimestamp + $time;
                            if (Journey::$officeLocation !== $travelOrderLocation['location_id']) {
                                if (!isset($dailyDetails[$date])) {
                                    $stopKm = CarKm::findOneByAttributes(['car_id' => Roadmap::$roadmap['car_id'], 'date' => $date])['km'];
                                    $dailyDetails[$date]['date'] = $date;
                                    $dailyDetails[$date]['start_time'] = date('H:i:s', $startTimestamp);
                                    $dailyDetails[$date]['start_timestamp'] = $startTimestamp;
                                    $dailyDetails[$date]['stop_time'] = date('H:i:s', $stopTimestamp);
                                    $dailyDetails[$date]['stop_timestamp'] = $stopTimestamp;
                                    $dailyDetails[$date]['start_km'] = $stopKm - $distance;
                                    $dailyDetails[$date]['stop_km'] = $stopKm;
                                    $dailyDetails[$date]['km'] = $distance;
                                    $dailyDetails[$date]['travel_order'] = true;
                                }
                                $dailyDetails[$date]['journals'][] = self::addDailyDetails(false, true, $startLocation, $travelOrderLocation['location_id'], $date, date('H:i:s', $startTimestamp), $startTimestamp, date('H:i:s', $stopTimestamp), $stopTimestamp, $dailyDetails[$date]['start_km'], $dailyDetails[$date]['start_km'] + $distance, $distance, $travelOrder['project_id'], $travelOrder['type'], $travelOrder['validation_option_id']);
                            }
                            $startLocation = $travelOrderLocation['location_id'];
                        }
                        if ($travelOrderLocations[count($travelOrderLocations) - 1]['location_id'] !== Journey::$officeLocation) {
                            $stopLocation = Journey::$officeLocation;
                            $startLocation = $travelOrderLocations[count($travelOrderLocations) - 1]['location_id'];
                            $startTimestamp = $dailyDetails[$date]['journals'][count($dailyDetails[$date]['journals']) - 1]['stop_timestamp'];
                            $journeys = Journey::verifyIsJourneyTimeAndDistance($startLocation, $stopLocation);
                            if (!empty($journeys)) {
                                $time = LocationDistance::arithmeticMean($journeys, "time");
                                $distance = LocationDistance::arithmeticMean($journeys, "distance");
                                $startKm = $dailyDetails[$date]['journals'][count($dailyDetails[$date]['journals']) - 1]['stop_km'];
                                $stopTimestamp = $startTimestamp + $time;
                                $date = $travelOrder['stop_journey'];
                                if (!isset($dailyDetails[$date])) {
                                    $dailyDetails[$date]['date'] = $date;
                                    $dailyDetails[$date]['start_time'] = date('H:i:s', $startTimestamp);
                                    $dailyDetails[$date]['start_timestamp'] = $startTimestamp;
                                    $dailyDetails[$date]['stop_time'] = date('H:i:s', $stopTimestamp);
                                    $dailyDetails[$date]['stop_timestamp'] = $stopTimestamp;
                                    $dailyDetails[$date]['start_km'] = $startKm;
                                    $dailyDetails[$date]['stop_km'] = $startKm + $distance;
                                    $dailyDetails[$date]['km'] = $distance;
                                    $dailyDetails[$date]['travel_order'] = true;
                                }
                                $dailyDetails[$date]['journals'][] = self::addDailyDetails(false, true, $startLocation, Journey::$officeLocation, $date, date('H:i:s', $startTimestamp), $startTimestamp, date('H:i:s', $stopTimestamp), $stopTimestamp, $dailyDetails[$date]['start_km'], $dailyDetails[$date]['stop_km'], $distance, $travelOrder['project_id'], $travelOrder['type'], $travelOrder['validation_option_id']);
                            }
                        }
                    }
                }
                $countTravelOrder = 0;
                ksort($dailyDetails);
                foreach ($dailyDetails as $dailyDetail) {
                    if (!isset($dailyDetail['travel_order'])) {
                        $countTravelOrder++;
                    }
                }
                if ($daysKms != 0) {
                    $daysKms = round($daysKms / $countTravelOrder);
                    foreach ($dailyDetails as $date => $dailyDetail) {
                        if (!isset($dailyDetail['travel_order'])) {
                            $dailyDetails[$date]['km'] += $daysKms;
                            $dailyDetails[$date]['stop_km'] = $dailyDetails[$date]['start_km'] + $dailyDetails[$date]['km'];
                        }
                    }
                }

                Car::$roadmapJourneys = [];
                Car::setValidJourneys($roadmap['car_id'], $year, $month);

                $journeys = Car::$roadmapJourneys;
                for ($i = 0; $i < count($journeys); $i++) {
                    $date = date('Y-m-d', strtotime($journeys[$i]['started']));
                    if (!isset($dailyDetails[$date])) {
                        continue;
                    }
                    if (
                        $journeys[$i]['start_hotspot_id'] == $journeys[$i]['stop_hotspot_id']
                        && $journeys[$i]['start_hotspot_id'] == Journey::$officeLocation
                    ) {
                        $startTimestamp = strtotime($journeys[$i]['started']);
                        $stopTimestamp = strtotime($journeys[$i]['stopped']);
                        $dailyDetails[$date]['journals'][] = self::addDailyDetails($journeys[$i]['id'], false, $journeys[$i]['start_hotspot_id'], $journeys[$i]['stop_hotspot_id'], $date, date('H:i:s', $startTimestamp), $startTimestamp, date('H:i:s', $stopTimestamp), $stopTimestamp, $journeys[$i]['odo'] - $journeys[$i]['distance'], $journeys[$i]['odo'], $journeys[$i]['distance'], $journeys[$i]['project_id'], $journeys[$i]['type'], $journeys[$i]['validation_option_id']);
                        continue;
                    }
                    if (!isset($dailyDetails[$date]['journals'])) {
                        $dailyDetails[$date]['journals'] = [];
                    }
                    if (isset($journeys[$i + 1])
                        && $journeys[$i]['stop_hotspot_id'] != Journey::$officeLocation
                        && $journeys[$i]['stop_hotspot_id'] == $journeys[$i + 1]['start_hotspot_id']
                        && explode(' ', $journeys[$i]['started'])[0] == explode(' ', $journeys[$i + 1]['started'])[0]
                    ) {
                        if ($journeys[$i]['start_hotspot_id'] != Journey::$officeLocation) {
                            $distanceFirstJourney = self::findDistanceJourneys($journeys[$i]['start_hotspot_id']);
                            $timeFirstJourney = self::findTimeJourneys($journeys[$i]['start_hotspot_id']);
                            $startTimestamp = strtotime($journeys[$i]['started']) - $timeFirstJourney;
                            $stopTimestamp = strtotime($journeys[$i]['started']);
                            if ($distanceFirstJourney > 0) {
                                $dailyDetails[$date]['journals'][] = self::addDailyDetails(false, true, Journey::$officeLocation, $journeys[$i]['start_hotspot_id'], $date, date('H:i:s', $startTimestamp), $startTimestamp, date('H:i:s', $stopTimestamp), $stopTimestamp, $journeys[$i]['odo'] - $journeys[$i]['distance'] - $distanceFirstJourney, $journeys[$i]['odo'] - $journeys[$i]['distance'], $distanceFirstJourney, $journeys[$i]['project_id'], $journeys[$i]['type'], $journeys[$i]['validation_option_id']);
                            }
                        }
                        while (isset($journeys[$i + 1])
                            && $journeys[$i]['stop_hotspot_id'] == $journeys[$i + 1]['start_hotspot_id']
                            && explode(' ', $journeys[$i]['started'])[0] == explode(' ', $journeys[$i + 1]['started'])[0]
                        ) {
                            $dailyDetails[$date]['journals'][] = self::addDailyDetails($journeys[$i]['id'], false, $journeys[$i]['start_hotspot_id'], $journeys[$i]['stop_hotspot_id'], $date, date('H:i:s', strtotime($journeys[$i]['started'])), strtotime($journeys[$i]['started']), date('H:i:s', strtotime($journeys[$i]['stopped'])), strtotime($journeys[$i]['stopped']), $journeys[$i]['odo'] - $journeys[$i]['distance'], $journeys[$i]['odo'], $journeys[$i]['distance'], $journeys[$i]['project_id'], $journeys[$i]['type'], $journeys[$i]['validation_option_id']);
                            $i++;
                        }
                        $dailyDetails[$date]['journals'][] = self::addDailyDetails($journeys[$i]['id'], false, $journeys[$i]['start_hotspot_id'], $journeys[$i]['stop_hotspot_id'], $date, date('H:i:s', strtotime($journeys[$i]['started'])), strtotime($journeys[$i]['started']), date('H:i:s', strtotime($journeys[$i]['stopped'])), strtotime($journeys[$i]['stopped']), $journeys[$i]['odo'] - $journeys[$i]['distance'], $journeys[$i]['odo'], $journeys[$i]['distance'], $journeys[$i]['project_id'], $journeys[$i]['type'], $journeys[$i]['validation_option_id']);

                        if ($journeys[$i]['stop_hotspot_id'] != Journey::$officeLocation) {
                            $distanceLastJourney = self::findDistanceJourneys($journeys[$i]['stop_hotspot_id']);
                            $timeLastJourney = self::findTimeJourneys($journeys[$i]['stop_hotspot_id']);
                            $startTimestamp = strtotime($journeys[$i]['stopped']);
                            $stopTimestamp = strtotime($journeys[$i]['stopped']) + $timeLastJourney;
                            if ($distanceLastJourney > 0) {
                                $dailyDetails[$date]['journals'][] = self::addDailyDetails(false, true, $journeys[$i]['stop_hotspot_id'], Journey::$officeLocation, $date, date('H:i:s', $startTimestamp), $startTimestamp, date('H:i:s', $stopTimestamp), $stopTimestamp, $journeys[$i]['odo'], $journeys[$i]['odo'] + $distanceLastJourney, $distanceLastJourney, $journeys[$i]['project_id'], $journeys[$i]['type'], $journeys[$i]['validation_option_id']);
                            }
                        }

                    } else if ($journeys[$i]['start_hotspot_id'] == Journey::$officeLocation) {
                        $startTimestamp = strtotime($journeys[$i]['started']);
                        $stopTimestamp = strtotime($journeys[$i]['stopped']);
                        $dailyDetails[$date]['journals'][] = self::addDailyDetails($journeys[$i]['id'], false, $journeys[$i]['start_hotspot_id'], $journeys[$i]['stop_hotspot_id'], $date, date('H:i:s', $startTimestamp), $startTimestamp, date('H:i:s', $stopTimestamp), $stopTimestamp, $journeys[$i]['odo'] - $journeys[$i]['distance'], $journeys[$i]['odo'], $journeys[$i]['distance'], null, null, null);
                        $stopSecondJourneyTimestamp = $stopTimestamp + $journeys[$i]['time'];
                        $dailyDetails[$date]['journals'][] = self::addDailyDetails(false, true, $journeys[$i]['stop_hotspot_id'], $journeys[$i]['start_hotspot_id'], $date, date('H:i:s', $stopTimestamp), $stopTimestamp, date('H:i:s', $stopSecondJourneyTimestamp), $stopSecondJourneyTimestamp, $journeys[$i]['odo'], $journeys[$i]['odo'] + $journeys[$i]['distance'], $journeys[$i]['distance'], $journeys[$i]['project_id'], $journeys[$i]['type'], $journeys[$i]['validation_option_id']);
                    } else if ($journeys[$i]['stop_hotspot_id'] == Journey::$officeLocation) {
                        $startTimestamp = strtotime($journeys[$i]['started']) - $journeys[$i]['time'];
                        $stopTimestamp = strtotime($journeys[$i]['started']);
                        $startKm = empty($dailyDetails[$date]['journals']) ? $dailyDetails[$date]['start_km'] : $dailyDetails[$date]['journals'][count($dailyDetails[$date]['journals']) - 1]['start_km'];
                        $dailyDetails[$date]['journals'][] = self::addDailyDetails(false, true, $journeys[$i]['stop_hotspot_id'], $journeys[$i]['start_hotspot_id'], $date, date('H:i:s', $startTimestamp), $startTimestamp, date('H:i:s', $stopTimestamp), $stopTimestamp, $startKm, $startKm + $journeys[$i]['distance'], $journeys[$i]['distance'], $journeys[$i]['project_id'], $journeys[$i]['type'], $journeys[$i]['validation_option_id']);
                        $startTimestamp = strtotime($journeys[$i]['started']);
                        $stopTimestamp = strtotime($journeys[$i]['stopped']);
                        $dailyDetails[$date]['journals'][] = self::addDailyDetails($journeys[$i]['id'], false, $journeys[$i]['start_hotspot_id'], $journeys[$i]['stop_hotspot_id'], $date, date('H:i:s', $startTimestamp), $startTimestamp, date('H:i:s', $stopTimestamp), $stopTimestamp, $journeys[$i]['odo'] - $journeys[$i]['distance'], $journeys[$i]['odo'], $journeys[$i]['distance'], null, null, null);
                    } else if ($journeys[$i]['start_hotspot_id'] != Journey::$officeLocation
                        && $journeys[$i]['stop_hotspot_id'] != Journey::$officeLocation
                    ) {
                        $distanceFirstJourney = self::findDistanceJourneys($journeys[$i]['start_hotspot_id']);
                        $timeFirstJourney = self::findTimeJourneys($journeys[$i]['start_hotspot_id']);
                        $distanceSecondJourney = self::findDistanceJourneys($journeys[$i]['stop_hotspot_id']);
                        $timeSecondJourney = self::findTimeJourneys($journeys[$i]['stop_hotspot_id']);
                        if ($distanceFirstJourney > 0) {
                            $dailyDetails[$date]['start_timestamp'] <= strtotime($journeys[$i]['started']) - $timeFirstJourney ? $startTimestamp = strtotime($journeys[$i]['started']) - $timeFirstJourney : $startTimestamp = $dailyDetails[$date]['start_timestamp'];
                            $dailyDetails[$date]['stop_timestamp'] >= strtotime($journeys[$i]['stopped']) + $timeSecondJourney ? $stopTimestamp = strtotime($journeys[$i]['started']) : $stopTimestamp = $dailyDetails[$date]['stop_timestamp'];
                            $dailyDetails[$date]['journals'][] = self::addDailyDetails(false, true, Journey::$officeLocation, $journeys[$i]['start_hotspot_id'], $date, date('H:i:s', $startTimestamp), $startTimestamp, date('H:i:s', $stopTimestamp), $stopTimestamp, $journeys[$i]['odo'] - $journeys[$i]['distance'] - $distanceFirstJourney, $journeys[$i]['odo'] - $journeys[$i]['distance'], $distanceFirstJourney, $journeys[$i]['project_id'], $journeys[$i]['type'], $journeys[$i]['validation_option_id']);
                        }
                        $dailyDetails[$date]['journals'][] = self::addDailyDetails($journeys[$i]['id'], false, $journeys[$i]['start_hotspot_id'], $journeys[$i]['stop_hotspot_id'], $date, date('H:i:s', strtotime($journeys[$i]['started'])), strtotime($journeys[$i]['started']), date('H:i:s', strtotime($journeys[$i]['stopped'])), strtotime($journeys[$i]['stopped']), $journeys[$i]['odo'] - $journeys[$i]['distance'], $journeys[$i]['odo'], $journeys[$i]['distance'], $journeys[$i]['project_id'], $journeys[$i]['type'], $journeys[$i]['validation_option_id']);
                        if ($distanceSecondJourney > 0) {
                            $startTimestamp = strtotime($journeys[$i]['stopped']);
                            $stopTimestamp = strtotime($journeys[$i]['stopped']) + $timeSecondJourney < $dailyDetails[$date]['stop_timestamp'] ? strtotime($journeys[$i]['stopped']) + $timeSecondJourney : $dailyDetails[$date]['stop_timestamp'];
                            $dailyDetails[$date]['journals'][] = self::addDailyDetails(false, true, $journeys[$i]['stop_hotspot_id'], Journey::$officeLocation, $date, date('H:i:s', $startTimestamp), $startTimestamp, date('H:i:s', $stopTimestamp), $stopTimestamp, $journeys[$i]['odo'], $journeys[$i]['odo'] + $distanceSecondJourney, $distanceSecondJourney, $journeys[$i]['project_id'], $journeys[$i]['type'], $journeys[$i]['validation_option_id']);
                        }
                    } else {
                        $dailyDetails[$date]['journals'][] = self::addDailyDetails($journeys[$i]['id'], false, $journeys[$i]['start_hotspot_id'], $journeys[$i]['stop_hotspot_id'], $date, date('H:i:s', strtotime($journeys[$i]['started'])), strtotime($journeys[$i]['started']), date('H:i:s', strtotime($journeys[$i]['stopped'])), strtotime($journeys[$i]['stopped']), $journeys[$i]['odo'] - $journeys[$i]['distance'], $journeys[$i]['odo'], $journeys[$i]['distance'], $journeys[$i]['project_id'], $journeys[$i]['type'], $journeys[$i]['validation_option_id']);
                    }
                }
                $newIntervals = [];
                ksort($dailyDetails);
                foreach ($dailyDetails as $details) {
                    if (empty($details['journals'])) {
                        $newIntervals[] = $details;
                        continue;
                    }
                    if ($details['km'] == 0) {
                        continue;
                    }
                    $currentInterval = $details;
                    foreach ($details['journals'] as $key => $journal) {
                        if ($key == 0) {
                            if ($journal['start_timestamp'] > $currentInterval['start_timestamp'] && ($journal['start_km'] - $currentInterval['start_km']) > 0) {
                                $newInterval = [
                                    'date' => $currentInterval['date'],
                                    'start_time' => date('H:i:s', $currentInterval['start_timestamp']),
                                    'start_timestamp' => $currentInterval['start_timestamp'],
                                    'stop_time' => date('H:i:s', $journal['start_timestamp'] - 1),
                                    'stop_timestamp' => strtotime(date('H:i:s', $journal['start_timestamp'] - 1)),
                                    'start_km' => $currentInterval['start_km'],
                                    'stop_km' => $journal['start_km'],
                                    'km' => $journal['start_km'] - $currentInterval['start_km'],
                                    'validated_project' => $journal['validated_project'],
                                    'validated_type' => $journal['validated_type'],
                                    'validated-validation_option_id' => $journal['validated-validation_option_id']
                                ];
                                $newIntervals[] = $newInterval;
                            }
                            $newIntervals[] = $journal;
                            $currentInterval = $journal;

                            continue;
                        }

                        if ($journal['start_timestamp'] > $currentInterval['stop_timestamp'] && ($journal['start_km'] - $currentInterval['stop_km']) > 0) {
                            $newInterval = [
                                'date' => $currentInterval['date'],
                                'start_time' => date('H:i:s', $currentInterval['stop_timestamp']),
                                'start_timestamp' => $currentInterval['stop_timestamp'],
                                'stop_time' => date('H:i:s', $journal['start_timestamp'] - 1),
                                'stop_timestamp' => strtotime(date('H:i:s', $journal['start_timestamp'] - 1)) - 1,
                                'start_km' => $currentInterval['stop_km'],
                                'stop_km' => $journal['start_km'],
                                'km' => $journal['start_km'] - $currentInterval['stop_km'],
                                'validated_project' => $journal['validated_project'],
                                'validated_type' => $journal['validated_type'],
                                'validated-validation_option_id' => $journal['validated-validation_option_id']
                            ];

                            $newIntervals[] = $newInterval;
                        }
                        $newIntervals[] = $journal;
                        $currentInterval = $journal;
                    }
                }
                $goodIntervals = [];
                foreach ($newIntervals as $key => $interval) {
                    if (
                        !isset($interval['supplementary'])
                        && isset($newIntervals[$key - 1]['supplementary'])
                        && isset($newIntervals[$key + 1]['supplementary'])
                        && $newIntervals[$key - 1]['stop_hotspot_id'] == $newIntervals[$key + 1]['start_hotspot_id']
                        && $newIntervals[$key - 1]['date'] == $newIntervals[$key + 1]['date']
                    ) {
                        continue;
                    }
                    $goodIntervals[] = $interval;
                }
                $locationsDistances = [];
                foreach ($goodIntervals as $key => $interval) {
                    if (!empty($interval['journey_id'])) {
                        continue;
                    }

                    $km = round($interval['km'] / 2);
                    if (!isset($locationsDistances["'{$km}'"])) {
                        $locationsDistances["'{$km}'"] = [];
                    }
                    $locationsDistances["'{$km}'"][] = $key;

                    ksort($locationsDistances);
                }

                /**
                 * get total distance traveled
                 * get distance traveled on working days
                 * get distance traveled on working days
                 */
                $sumKmIntervals = 0;
                $totalKmWithoutJourneysKm = $totalUnAssignedKm;
                foreach ($goodIntervals as $interval) {
                    if (isset($interval['journey_id'])) {
                        $totalKmWithoutJourneysKm -= $interval['km'];
                        continue;
                    }
                    if (isset($interval['supplementary']) && $interval['supplementary']) {
                        $totalKmWithoutJourneysKm -= $interval['km'];
                        continue;
                    }
                    $sumKmIntervals += $interval['km'];
                }
                foreach ($goodIntervals as $key => $interval) {
                    if (isset($interval['journey_id'])) {
                        continue;
                    }
                    if (isset($interval['supplementary']) && $interval['supplementary']) {
                        continue;
                    }
                    $goodIntervals[$key]['percentFromTotalKm'] = $interval['km'] * 100 / $sumKmIntervals;
                    $goodIntervals[$key]['total_unassigned'] = $totalUnAssignedKm;
                    $goodIntervals[$key]['km_unassigned'] = round($totalKmWithoutJourneysKm * $goodIntervals[$key]['percentFromTotalKm'] / 100, 2);
                }
                if ($deductibility != 100) {
                    $lastCarKmThisMonth = (int)CarKm::getLastCarKmThisMonth($roadmap['car_id'], $year, $month);
                    $firstDayKm = round($lastCarKmThisMonth) - round($totalUnAssignedKm);
                }
                $intervals = [];
                foreach ($goodIntervals as $key => $interval) {
                    if ($key == 0) {
                        $interval['start_km'] = $firstDayKm;
                    } else {
                        $interval['start_km'] = $intervals[$key - 1]['stop_km'];
                    }
                    if (!isset($interval['journey_id'])) {
                        $interval['km'] = $interval['km_unassigned'];
                    }
                    $interval['stop_km'] = $interval['start_km'] + $interval['km'];

                    $intervals[] = $interval;
                }
                $locations = [];
                foreach (Journey::$distanceAndTimeJourneyLocation as $location) {
                    if (!isset($locations[$location['distance']])) {
                        $locations[$location['distance']] = $location;
                        continue;
                    }
                    if ($locations[$location['distance']]['seconds_time_journey'] < $location['seconds_time_journey']) {
                        $locations[$location['distance']] = $location;
                    }
                }
                for ($key = 0; $key < count($intervals); $key++) {
                    $interval = $intervals[$key];

                    if (isset($interval['journey_id'])) {
                        continue;
                    }

                    if ($interval['km'] < Roadmap::MINIMUM_KM_ROADMAP_JOURNEY) {
                        if (isset($intervals[$key + 1])) {
                            $intervals[$key + 1]['km'] += $interval['km'];
                            $intervals[$key + 1]['start_km'] = $interval['start_km'];
                            $intervals[$key + 1]['start_time'] = $interval['start_time'];
                            $intervals[$key + 1]['start_timestamp'] = $interval['start_timestamp'];
                        } elseif (isset($intervals[$key - 1])) {
                            $intervals[$key - 1]['km'] += $interval['km'];
                            $intervals[$key - 1]['stop_km'] = $interval['stop_km'];
                            $intervals[$key - 1]['stop_time'] = $interval['stop_time'];
                            $intervals[$key - 1]['stop_timestamp'] = $interval['stop_timestamp'];
                        }

                        unset($intervals[$key]);
                        $intervals = array_values($intervals);
                        $key--;
                    }
                }
                $newIntervals = [];
                $journeysSupplementary = [];
                Journey::$allJourneysRoadmap = [];
                $intervalCounter = 0;
                foreach ($intervals as $key => $interval) {
                    if (!empty($interval['journey_id']) || isset($interval['travel_order'])) {
                        continue;
                    }
                    if (isset($interval['supplementary']) && $interval['supplementary']) {
                        self::$supplemetaryJourneyData['start_km'] = $interval['start_km'];
                        self::$supplemetaryJourneyData['stop_km'] = $interval['stop_km'];
                        self::$supplemetaryJourneyData['start_time'] = $interval['start_time'];
                        self::$supplemetaryJourneyData['start_timestamp'] = $interval['start_timestamp'];
                        self::$supplemetaryJourneyData['stop_time'] = $interval['stop_time'];
                        self::$supplemetaryJourneyData['stop_timestamp'] = $interval['stop_timestamp'];
                        self::$supplemetaryJourneyData['start_hotspot_id'] = $interval['start_hotspot_id'];
                        self::$supplemetaryJourneyData['stop_hotspot_id'] = $interval['stop_hotspot_id'];
                        self::$supplemetaryJourneyData['supplementary'] = true;
                        self::$supplemetaryJourneyData['date'] = $interval['date'];
                        self::$supplemetaryJourneyData['km'] = $interval['km'];
                        self::$supplemetaryJourneyData['validated_project'] = $interval['validated_project'];
                        self::$supplemetaryJourneyData['validated_type'] = $interval['validated_type'];
                        self::$supplemetaryJourneyData['validated-validation_option_id'] = $interval['validated-validation_option_id'];
                        $journeysSupplementary[] = self::addJourneysSupplemetary();
                        continue;
                    }
                    $allValues = [];
                    $W = round($interval['km'] / 2);
                    $r = $wt = [];
                    $cc = 0;
                    $locations = Location::removeLocationsWithIsNotInProject($locations, $interval['date'], $roadmap['car_id']);
                    foreach ($locations as $distance => $location) {
                        if ($distance > $W) {
                            continue;
                        }
                        $r[] = $distance;
                        $wt[] = $distance;
                        $cc += $distance;
                    }
                    $W > 0 && $cc > 0 ? $repeated = $W / $cc + 1 : $repeated = 1;

                    $this->knapSackValues($r, $repeated, $wt, $W, $allValues);
                    $allValuesGrouped = [];
                    if (count($allValues) === 0) {
                        continue;
                    }
                    foreach ($allValues as $value) {
                        if (!isset($allValuesGrouped[$value])) {
                            $allValuesGrouped[$value] = 0;
                        }
                        $allValuesGrouped[$value]++;
                    }
                    rsort($r);
                    $waitTime = 60;
                    $waitTime += $waitTime * (rand(1, 10) / 100);
                    if (isset($journeysSupplementary[count($journeysSupplementary) - 1])
                        && isset ($intervals[$intervalCounter - 1])
                        && empty($intervals[$intervalCounter - 1]['journey_id'])
                    ) {
                        $interval['start_km'] = $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'];
                    }
                    if (count($allValues) == 1) {
                        $distance = $allValues[0];
                        $journeyTimestamp = self::getTimeJourney($locations[$distance]['stop_hotspot_id']);
                        if ($journeyTimestamp === null) {
                            continue;
                        }
                        $carLocation = CarLocation::findOneByAttributes(['location_id' => $locations[$distance]['stop_hotspot_id']]);
                        if (empty($carLocation)) {
                            $carLocation['project_id'] = null;
                            $carLocation['type'] = null;
                            $carLocation['validation_option_id'] = null;
                        }
                        $interval['start_km'] = self::verifyPreviousIsValidated($intervals, $key, $journeysSupplementary);
                        $newLocationTimestampStopped = $interval['start_timestamp'] + $journeyTimestamp;
                        $startTimestamp = strtotime($interval['date'] . " " . $interval['start_time']);
                        self::$supplemetaryJourneyData['start_km'] = $interval['start_km'];
                        self::$supplemetaryJourneyData['stop_km'] = $interval['start_km'] + $distance;
                        self::$supplemetaryJourneyData['start_time'] = $interval['start_time'];
                        self::$supplemetaryJourneyData['start_timestamp'] = $startTimestamp;
                        self::$supplemetaryJourneyData['stop_time'] = date('H:i:s', $newLocationTimestampStopped);
                        self::$supplemetaryJourneyData['stop_timestamp'] = $newLocationTimestampStopped;
                        self::$supplemetaryJourneyData['start_hotspot_id'] = Journey::$officeLocation;
                        self::$supplemetaryJourneyData['stop_hotspot_id'] = $locations[$distance]['stop_hotspot_id'];
                        self::$supplemetaryJourneyData['supplementary'] = true;
                        self::$supplemetaryJourneyData['date'] = $interval['date'];
                        self::$supplemetaryJourneyData['km'] = $distance;
                        self::$supplemetaryJourneyData['validated_project'] = $carLocation['project_id'];
                        self::$supplemetaryJourneyData['validated_type'] = $carLocation['type'];
                        self::$supplemetaryJourneyData['validated-validation_option_id'] = $carLocation['validation_option_id'];
                        $journeysSupplementary[] = self::addJourneysSupplemetary();
                        $journeysStartWithWaitingTime = $newLocationTimestampStopped + round($waitTime);
                        $journeysStopWithWaitingTime = $journeysStartWithWaitingTime + $journeyTimestamp;
                        if ($journeysStopWithWaitingTime > $interval['stop_timestamp']) {
                            $journeysStopWithWaitingTime = $interval['stop_timestamp'];
                        }
                        self::$supplemetaryJourneyData['start_km'] = $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'];
                        self::$supplemetaryJourneyData['stop_km'] = $interval['stop_km'];
                        self::$supplemetaryJourneyData['start_time'] = date('H:i:s', $journeysStartWithWaitingTime);
                        self::$supplemetaryJourneyData['start_timestamp'] = $journeysStartWithWaitingTime;
                        self::$supplemetaryJourneyData['stop_time'] = date('H:i:s', $journeysStopWithWaitingTime);
                        self::$supplemetaryJourneyData['stop_timestamp'] = $journeysStopWithWaitingTime;
                        self::$supplemetaryJourneyData['start_hotspot_id'] = $locations[$distance]['stop_hotspot_id'];
                        self::$supplemetaryJourneyData['stop_hotspot_id'] = Journey::$officeLocation;
                        self::$supplemetaryJourneyData['supplementary'] = true;
                        self::$supplemetaryJourneyData['date'] = $interval['date'];
                        self::$supplemetaryJourneyData['km'] = $interval['stop_km'] - $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'];
                        self::$supplemetaryJourneyData['validated_project'] = $carLocation['project_id'];
                        self::$supplemetaryJourneyData['validated_type'] = $carLocation['type'];
                        self::$supplemetaryJourneyData['validated-validation_option_id'] = $carLocation['validation_option_id'];
                        $journeysSupplementary[] = self::addJourneysSupplemetary();

                    } else {
                        $count = 0;
                        foreach ($allValues as $distance) {
                            $journeyTimestamp = self::getTimeJourney($locations[$distance]['stop_hotspot_id']);
                            if ($journeyTimestamp === null) {
                                continue;
                            }
                            $carLocation = CarLocation::findOneByAttributes(['location_id' => $locations[$distance]['stop_hotspot_id']]);
                            if (empty($carLocation)) {
                                $carLocation['project_id'] = null;
                                $carLocation['type'] = null;
                                $carLocation['validation_option_id'] = null;
                            }
                            if (empty($carLocation)) {
                                $carLocation['project_id'] = null;
                                $carLocation['type'] = null;
                                $carLocation['validation_option_id'] = null;
                            }
                            if ($count == 0) {
                                $newLocationTimestampStopped = $interval['start_timestamp'] + $journeyTimestamp;
                                $timeStampStart = strtotime($interval['date'] . " " . $interval['start_time']);
                                isset($intervals[$key - 1]) ? $interval['start_km'] = self::verifyPreviousIsValidated($intervals, $key, $journeysSupplementary) : '';
                                self::$supplemetaryJourneyData['start_km'] = $interval['start_km'];
                                self::$supplemetaryJourneyData['stop_km'] = $interval['start_km'] + $distance;
                                self::$supplemetaryJourneyData['start_time'] = $interval['start_time'];
                                self::$supplemetaryJourneyData['start_timestamp'] = $timeStampStart;
                                self::$supplemetaryJourneyData['stop_time'] = \date('H:i:s', $newLocationTimestampStopped);
                                self::$supplemetaryJourneyData['stop_timestamp'] = strtotime($interval['date']) + $newLocationTimestampStopped;
                                self::$supplemetaryJourneyData['start_hotspot_id'] = Journey::$officeLocation;
                                self::$supplemetaryJourneyData['stop_hotspot_id'] = $locations[$distance]['stop_hotspot_id'];
                                self::$supplemetaryJourneyData['supplementary'] = true;
                                self::$supplemetaryJourneyData['date'] = $interval['date'];
                                self::$supplemetaryJourneyData['km'] = $distance;
                                self::$supplemetaryJourneyData['validated_project'] = $carLocation['project_id'];
                                self::$supplemetaryJourneyData['validated_type'] = $carLocation['type'];
                                self::$supplemetaryJourneyData['validated-validation_option_id'] = $carLocation['validation_option_id'];
                                $journeysSupplementary[] = self::addJourneysSupplemetary();
                                $journeysStartWithWaitingTime = \date('H:i:s', strtotime($journeysSupplementary[count($journeysSupplementary) - 1]['stop_time']) + $waitTime);
                                $journeysStopWithWaitingTime = \date('H:i:s', strtotime($journeysSupplementary[count($journeysSupplementary) - 1]['stop_time']) + $waitTime + $journeyTimestamp);
                                if ($journeysStopWithWaitingTime > $interval['stop_timestamp']) {
                                    $journeysStopWithWaitingTime = $interval['stop_timestamp'];
                                }
                                self::$supplemetaryJourneyData['start_km'] = $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'];
                                self::$supplemetaryJourneyData['stop_km'] = $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'] + $distance;
                                self::$supplemetaryJourneyData['start_time'] = $journeysStartWithWaitingTime;
                                self::$supplemetaryJourneyData['start_timestamp'] = strtotime($interval['date'] . " " . $journeysStartWithWaitingTime);
                                self::$supplemetaryJourneyData['stop_time'] = $journeysStopWithWaitingTime;
                                self::$supplemetaryJourneyData['stop_timestamp'] = strtotime($interval['date'] . " " . $journeysStopWithWaitingTime);
                                self::$supplemetaryJourneyData['start_hotspot_id'] = $locations[$distance]['stop_hotspot_id'];
                                self::$supplemetaryJourneyData['stop_hotspot_id'] = Journey::$officeLocation;
                                self::$supplemetaryJourneyData['supplementary'] = true;
                                self::$supplemetaryJourneyData['date'] = $interval['date'];
                                self::$supplemetaryJourneyData['km'] = $distance;
                                self::$supplemetaryJourneyData['validated_project'] = $carLocation['project_id'];
                                self::$supplemetaryJourneyData['validated_type'] = $carLocation['type'];
                                self::$supplemetaryJourneyData['validated-validation_option_id'] = $carLocation['validation_option_id'];
                                $journeysSupplementary[] = self::addJourneysSupplemetary();
                                ++$count;
                            } else if (count($allValues) == $count + 1) {
                                $journeysStartWithWaitingTime = \date('H:i:s', strtotime($journeysSupplementary[count($journeysSupplementary) - 1]['stop_time']) + $waitTime);
                                $journeysStopWithWaitingTime = \date('H:i:s', strtotime($journeysSupplementary[count($journeysSupplementary) - 1]['stop_time']) + $waitTime + $journeyTimestamp);
                                isset($intervals[$key - 1]) ? $interval['start_km'] = self::verifyPreviousIsValidated($intervals, $key, $journeysSupplementary) : '';
                                self::$supplemetaryJourneyData['start_km'] = $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'];
                                self::$supplemetaryJourneyData['stop_km'] = $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'] + $distance;
                                self::$supplemetaryJourneyData['start_time'] = $journeysStartWithWaitingTime;
                                self::$supplemetaryJourneyData['start_timestamp'] = strtotime($interval['date'] . " " . $journeysStartWithWaitingTime);
                                self::$supplemetaryJourneyData['stop_time'] = $journeysStopWithWaitingTime;
                                self::$supplemetaryJourneyData['stop_timestamp'] = strtotime($interval['date'] . " " . $journeysStopWithWaitingTime);
                                self::$supplemetaryJourneyData['start_hotspot_id'] = Journey::$officeLocation;
                                self::$supplemetaryJourneyData['stop_hotspot_id'] = $locations[$distance]['stop_hotspot_id'];
                                self::$supplemetaryJourneyData['supplementary'] = true;
                                self::$supplemetaryJourneyData['date'] = $interval['date'];
                                self::$supplemetaryJourneyData['km'] = $distance;
                                self::$supplemetaryJourneyData['validated_project'] = $carLocation['project_id'];
                                self::$supplemetaryJourneyData['validated_type'] = $carLocation['type'];
                                self::$supplemetaryJourneyData['validated-validation_option_id'] = $carLocation['validation_option_id'];
                                $journeysSupplementary[] = self::addJourneysSupplemetary();
                                $newLocationTimestampStopped = $journeysSupplementary[count($journeysSupplementary) - 1]['stop_timestamp'];
                                $journeysStartWithWaitingTime = \date('H:i:s', $newLocationTimestampStopped + $waitTime);
                                $journeysStopWithWaitingTime = \date('H:i:s', strtotime($journeysSupplementary[count($journeysSupplementary) - 1]['stop_time']) + $waitTime + $journeyTimestamp);
                                if ($journeysStopWithWaitingTime > $interval['stop_timestamp']) {
                                    $journeysStopWithWaitingTime = $interval['stop_timestamp'];
                                }
                                self::$supplemetaryJourneyData['start_km'] = $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'];
                                self::$supplemetaryJourneyData['stop_km'] = $interval['stop_km'];
                                self::$supplemetaryJourneyData['start_time'] = $journeysStartWithWaitingTime;
                                self::$supplemetaryJourneyData['start_timestamp'] = strtotime($interval['date'] . " " . $journeysStartWithWaitingTime);
                                self::$supplemetaryJourneyData['stop_time'] = $journeysStopWithWaitingTime;
                                self::$supplemetaryJourneyData['stop_timestamp'] = strtotime($interval['date'] . " " . $journeysStopWithWaitingTime);
                                self::$supplemetaryJourneyData['start_hotspot_id'] = $locations[$distance]['stop_hotspot_id'];
                                self::$supplemetaryJourneyData['stop_hotspot_id'] = Journey::$officeLocation;
                                self::$supplemetaryJourneyData['supplementary'] = true;
                                self::$supplemetaryJourneyData['date'] = $interval['date'];
                                self::$supplemetaryJourneyData['km'] = $interval['stop_km'] - $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'];
                                self::$supplemetaryJourneyData['validated_project'] = $carLocation['project_id'];
                                self::$supplemetaryJourneyData['validated_type'] = $carLocation['type'];
                                self::$supplemetaryJourneyData['validated-validation_option_id'] = $carLocation['validation_option_id'];
                                $journeysSupplementary[] = self::addJourneysSupplemetary();

                            } else {
                                $journeysStartWithWaitingTime = \date('H:i:s', strtotime($journeysSupplementary[count($journeysSupplementary) - 1]['stop_time']) + $waitTime);
                                $journeysStopWithWaitingTime = \date('H:i:s', strtotime($journeysSupplementary[count($journeysSupplementary) - 1]['stop_time']) + $waitTime + $journeyTimestamp);
                                self::$supplemetaryJourneyData['start_km'] = $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'];
                                self::$supplemetaryJourneyData['stop_km'] = $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'] + $distance;
                                self::$supplemetaryJourneyData['start_time'] = $journeysStartWithWaitingTime;
                                self::$supplemetaryJourneyData['start_timestamp'] = strtotime($interval['date'] . " " . $journeysStartWithWaitingTime);
                                self::$supplemetaryJourneyData['stop_time'] = $journeysStopWithWaitingTime;
                                self::$supplemetaryJourneyData['stop_timestamp'] = strtotime($interval['date'] . " " . $journeysStopWithWaitingTime);
                                self::$supplemetaryJourneyData['start_hotspot_id'] = Journey::$officeLocation;
                                self::$supplemetaryJourneyData['stop_hotspot_id'] = $locations[$distance]['stop_hotspot_id'];
                                self::$supplemetaryJourneyData['supplementary'] = true;
                                self::$supplemetaryJourneyData['date'] = $interval['date'];
                                self::$supplemetaryJourneyData['km'] = $distance;
                                self::$supplemetaryJourneyData['validated_project'] = $carLocation['project_id'];
                                self::$supplemetaryJourneyData['validated_type'] = $carLocation['type'];
                                self::$supplemetaryJourneyData['validated-validation_option_id'] = $carLocation['validation_option_id'];
                                $journeysSupplementary[] = self::addJourneysSupplemetary();
                                $journeysStartWithWaitingTime = \date('H:i:s', strtotime($journeysSupplementary[count($journeysSupplementary) - 1]['stop_time']) + $waitTime);
                                $journeysStopWithWaitingTime = \date('H:i:s', strtotime($journeysSupplementary[count($journeysSupplementary) - 1]['stop_time']) + $waitTime + $journeyTimestamp);
                                if ($journeysStopWithWaitingTime > $interval['stop_timestamp']) {
                                    $journeysStopWithWaitingTime = $interval['stop_timestamp'];
                                }
                                self::$supplemetaryJourneyData['start_km'] = $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'];
                                self::$supplemetaryJourneyData['stop_km'] = $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'] + $distance;
                                self::$supplemetaryJourneyData['start_time'] = $journeysStartWithWaitingTime;
                                self::$supplemetaryJourneyData['start_timestamp'] = strtotime($interval['date'] . " " . $journeysStartWithWaitingTime);
                                self::$supplemetaryJourneyData['stop_time'] = $journeysStopWithWaitingTime;
                                self::$supplemetaryJourneyData['stop_timestamp'] = strtotime($interval['date'] . " " . $journeysStopWithWaitingTime);
                                self::$supplemetaryJourneyData['start_hotspot_id'] = $locations[$distance]['stop_hotspot_id'];
                                self::$supplemetaryJourneyData['stop_hotspot_id'] = Journey::$officeLocation;
                                self::$supplemetaryJourneyData['supplementary'] = true;
                                self::$supplemetaryJourneyData['date'] = $interval['date'];
                                self::$supplemetaryJourneyData['km'] = $interval['stop_km'] - $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'];
                                self::$supplemetaryJourneyData['validated_project'] = $carLocation['project_id'];
                                self::$supplemetaryJourneyData['validated_type'] = $carLocation['type'];
                                self::$supplemetaryJourneyData['validated-validation_option_id'] = $carLocation['validation_option_id'];
                                $journeysSupplementary[] = self::addJourneysSupplemetary();
                                ++$count;
                            }
                        }
                    }
                }
                $journeysValidated = [];
                foreach ($intervals as $key => $interval) {
                    if (!isset($interval['journey_id']) || !$interval['journey_id']) {
                        continue;
                    }
                    $journeysValidated[] = [
                        'journey_id' => $interval['journey_id'],
                        'start_km' => $interval['start_km'],
                        'stop_km' => $interval['stop_km'],
                        'start_time' => $interval['start_time'],
                        'start_timestamp' => $interval['start_timestamp'],
                        'stop_time' => $interval['stop_time'],
                        'stop_timestamp' => $interval['stop_timestamp'],
                        'supplementary' => false,
                        'date' => $interval['date'],
                        'km' => $interval['km']
                    ];
                    $timeStamp = strtotime($interval['date'] . " " . $interval['start_time']);
                    Journey::$allJourneysRoadmap[$timeStamp] = [
                        'journey_id' => $interval['journey_id'],
                        'start_km' => $interval['start_km'],
                        'stop_km' => $interval['stop_km'],
                        'start_time' => $interval['start_time'],
                        'start_timestamp' => $interval['start_timestamp'],
                        'stop_time' => $interval['stop_time'],
                        'stop_timestamp' => $interval['stop_timestamp'],
                        'supplementary' => false,
                        'date' => $interval['date'],
                        'km' => $interval['km']
                    ];
                }

                $combinedJourneys = Journey::sortJourneys($journeysSupplementary, $journeysValidated);
                $journeys = Journey::addKmToNullJourneys($combinedJourneys);
                if (!empty($journeys)) {
                    $journeysSupplementary = self::changeJourneysSupplementary($journeysSupplementary, $journeysValidated, $journeys, 'changed_km')[0];
                    $journeysValidated = self::changeJourneysSupplementary($journeysSupplementary, $journeysValidated, $journeys, 'changed_km')[1];
                }

                $combinedJourneys = Journey::sortJourneys($journeysSupplementary, $journeysValidated);
                $journeysNonconsecutive = Journey::checkIfConsecutiveJourneys($combinedJourneys);
                if (!empty($journeysNonconsecutive)) {
                    $journeysSupplementary = self::changeJourneysSupplementary($journeysSupplementary, $journeysValidated, $journeysNonconsecutive, 'nonconsecutive')[0];
                    $journeysValidated = self::changeJourneysSupplementary($journeysSupplementary, $journeysValidated, $journeysNonconsecutive, 'nonconsecutive')[1];
                }
                $combinedJourneys = Journey::sortJourneys($journeysSupplementary, $journeysValidated);
                $nullKmBetweenJourneys = Journey::checkIfNullKmBetweenJourneys($combinedJourneys);
                if (!empty($nullKmBetweenJourneys)) {
                    $journeysSupplementary = self::changeJourneysSupplementary($journeysSupplementary, $journeysValidated, $nullKmBetweenJourneys, 'null_km')[0];
                    $journeysValidated = self::changeJourneysSupplementary($journeysSupplementary, $journeysValidated, $nullKmBetweenJourneys, 'null_km')[1];
                }

                count($journeysSupplementary) > 0 ? self::addInDBAdditionalJourneys($journeysSupplementary, $roadmap) : null;
                count($journeysValidated) > 0 ? self::updatedValidatedJourneys($journeysValidated) : null;
            }
        }
    }

    public static function addInDBAdditionalJourneys($journeysToAdd, $roadmap)
    {
        foreach ($journeysToAdd as $journeyToAdd) {
            if (!$journeyToAdd['supplementary']) {
                continue;
            }
            $modelJourney = new Journey();
            $modelJourney->car_id = $roadmap['car_id'];
            $modelJourney->start_hotspot_id = $journeyToAdd['start_hotspot_id'];
            $modelJourney->stop_hotspot_id = $journeyToAdd['stop_hotspot_id'];
            $modelJourney->distance = $journeyToAdd['km'];
            $modelJourney->start_odo_supplement = round($journeyToAdd['start_km']);
            $modelJourney->odo_supplement = $journeyToAdd['stop_km'];
            $modelJourney->started = $journeyToAdd['date'] . " " . $journeyToAdd['start_time'];
            $modelJourney->stopped = $journeyToAdd['date'] . " " . $journeyToAdd['stop_time'];;
            $modelJourney->time = null;
            $modelJourney->stand_time = null;
            $modelJourney->exploit = null;
            $modelJourney->speed = null;
            $modelJourney->status = 1;
            $modelJourney->user_id = $roadmap['holder_id'];
            $modelJourney->project_id = $journeyToAdd['validated_project'] == 0 ? null : $journeyToAdd['validated_project'];
            $modelJourney->type = $journeyToAdd['validated_type'];
            $modelJourney->validation_option_id = $journeyToAdd['validated-validation_option_id'];
            $modelJourney->merged_with_id = 0;
            $modelJourney->observation = Car::find()->select('plate_number')->where(['id' => $roadmap['car_id']])->one()['plate_number'];
            $modelJourney->supplementary = 1;
            $modelJourney->deleted = 0;
            $modelJourney->added = date('Y-m-d H:i:s');
            $modelJourney->added_by = 149;
            $modelJourney->save();

            if (!$modelJourney->save()) {
                if ($modelJourney->hasErrors()) {
                    foreach ($modelJourney->errors as $error) {
                        echo $error[0];
                    }
                }
            }

            $modelJourneysRoadmap = new RoadMapJourney();
            $modelJourneysRoadmap->road_map_id = $roadmap['id'];
            $modelJourneysRoadmap->car_id = $modelJourney->car_id;
            $modelJourneysRoadmap->journey_id = $modelJourney->id;
            $modelJourneysRoadmap->deleted = 0;
            $modelJourneysRoadmap->added = date('Y-m-d H:i:s');
            $modelJourneysRoadmap->added_by = 149;
            $modelJourneysRoadmap->save();

            if (!$modelJourneysRoadmap->save()) {
                if ($modelJourneysRoadmap->hasErrors()) {
                    foreach ($modelJourneysRoadmap->errors as $error) {
                        echo $error[0];
                    }
                }
            }
        }
    }

    public function knapSackValues($r, $maxTimes, $n, $W, &$list)
    {
        if ($W < 0) {
            $list = [];
            return -2147483648;
        }

        if ($n < 0 || $W == 0) {
            $list = [];
            return 0;
        }
        $allValues = [];
        for ($i = 0; $i < $maxTimes; ++$i) {
            $allValues = array_merge($allValues, $r);
        }
        sort($allValues);

        $foundLengths = [];
        $sum = 0;

        for ($i = 0; $i < count($allValues); ++$i) {
            if ($sum + $allValues[$i] <= $W) {
                $sum += $allValues[$i];
                $foundLengths[] = $allValues[$i];
            }
            break;
        }
        if ($sum < $W * 0.95) {
            $sum = 0;
            $foundLengths = [];
            for ($i = count($allValues) - 1; $i >= 0; $i--) {
                if ($sum + $allValues[$i] > $W) {
                    continue;
                }
                $foundLengths[] = $allValues[$i];
                $sum += $allValues[$i];
            }
        }
        $list = $foundLengths;
        return $sum;
    }

    public static function updatedValidatedJourneys($journeysValidated)
    {
        foreach ($journeysValidated as $journey) {
            $findJourney = Journey::findOneByAttributes(['id' => $journey['journey_id']]);
            $findJourney->start_odo_supplement = round($journey['start_km']);
            $findJourney->odo_supplement = $journey['stop_km'];
            $findJourney->distance = $journey['km'];
            $findJourney->updated = date('Y-m-d H:i:s');
            $findJourney->updated_by = 149;
            $findJourney->update();
        }
    }

    public static function addDailyDetails($id, $supplementary, $startHotspotId, $stopHotspotId, $date, $startTime, $startTimestamp, $stopTime, $stopTimestamp, $startKm, $stopKm, $km, $validatedProject, $validatedType, $validatedValidationOptionId)
    {
        return [
            'journey_id' => $id,
            'supplementary' => $supplementary,
            'start_hotspot_id' => $startHotspotId,
            'stop_hotspot_id' => $stopHotspotId,
            'date' => $date,
            'start_time' => $startTime,
            'start_timestamp' => $startTimestamp,
            'stop_time' => $stopTime,
            'stop_timestamp' => $stopTimestamp,
            'start_km' => $startKm,
            'stop_km' => $stopKm,
            'km' => $km,
            'validated_project' => $validatedProject,
            'validated_type' => $validatedType,
            'validated-validation_option_id' => $validatedValidationOptionId
        ];

    }

    public static function addJourneysSupplemetary()
    {

        $timeStamp = strtotime(self::$supplemetaryJourneyData['date'] . ' ' . self::$supplemetaryJourneyData['start_time']);
        Journey::$allJourneysRoadmap[$timeStamp] = [
            'start_time' => self::$supplemetaryJourneyData['start_time'],
            'start_timestamp' => self::$supplemetaryJourneyData['start_timestamp'],
            'stop_time' => self::$supplemetaryJourneyData['stop_time'],
            'stop_timestamp' => self::$supplemetaryJourneyData['stop_timestamp'],
            'start_km' => self::$supplemetaryJourneyData['start_km'],
            'stop_km' => self::$supplemetaryJourneyData['stop_km'],
            'start_hotspot_id' => self::$supplemetaryJourneyData['start_hotspot_id'],
            'stop_hotspot_id' => self::$supplemetaryJourneyData['stop_hotspot_id'],
            'supplementary' => self::$supplemetaryJourneyData['supplementary'],
            'date' => self::$supplemetaryJourneyData['date'],
            'km' => self::$supplemetaryJourneyData['km'],
            'validated_project' => self::$supplemetaryJourneyData['validated_project'],
            'validated_type' => self::$supplemetaryJourneyData['validated_type'],
            'validated-validation_option_id' => self::$supplemetaryJourneyData['validated-validation_option_id']
        ];
        return [
            'start_time' => self::$supplemetaryJourneyData['start_time'],
            'start_timestamp' => self::$supplemetaryJourneyData['start_timestamp'],
            'stop_time' => self::$supplemetaryJourneyData['stop_time'],
            'stop_timestamp' => self::$supplemetaryJourneyData['stop_timestamp'],
            'start_km' => self::$supplemetaryJourneyData['start_km'],
            'stop_km' => self::$supplemetaryJourneyData['stop_km'],
            'start_hotspot_id' => self::$supplemetaryJourneyData['start_hotspot_id'],
            'stop_hotspot_id' => self::$supplemetaryJourneyData['stop_hotspot_id'],
            'supplementary' => self::$supplemetaryJourneyData['supplementary'],
            'date' => self::$supplemetaryJourneyData['date'],
            'km' => self::$supplemetaryJourneyData['km'],
            'validated_project' => self::$supplemetaryJourneyData['validated_project'],
            'validated_type' => self::$supplemetaryJourneyData['validated_type'],
            'validated-validation_option_id' => self::$supplemetaryJourneyData['validated-validation_option_id']

        ];

    }

    public static function findDistanceJourneys($locationID)
    {
        $journeys = Journey::find()->where(['OR', ['start_hotspot_id' => Journey::$officeLocation, 'stop_hotspot_id' => $locationID], ['stop_hotspot_id' => Journey::$officeLocation, 'start_hotspot_id' => $locationID]])->all();
        if (empty($journeys)) {
            $journeys = LocationDistance::find()->where(['OR', ['location_parent_id' => Journey::$officeLocation, 'location_child_id' => $locationID], ['location_child_id' => Journey::$officeLocation, 'location_parent_id' => $locationID]])->all();
        }
        $distance = 0;
        if (!empty($journeys)) {
            $variable = 0;
            foreach ($journeys as $journey) {
                ++$variable;
                $distance += $journey['distance'];
            }
            $distance = round($distance / $variable);
        }
        return $distance;
    }

    public static function findTimeJourneys($locationID)
    {
        $journeys = Journey::find()->where(['OR', ['start_hotspot_id' => Journey::$officeLocation, 'stop_hotspot_id' => $locationID], ['stop_hotspot_id' => Journey::$officeLocation, 'start_hotspot_id' => $locationID]])->all();

        if (empty($journeys)) {
            $journeys = LocationDistance::find()->where(['OR', ['location_parent_id' => Journey::$officeLocation, 'location_child_id' => $locationID], ['location_child_id' => Journey::$officeLocation, 'location_parent_id' => $locationID]])->all();
        }
        $time = 0;
        if (!empty($journeys)) {
            $variable = 0;
            foreach ($journeys as $journey) {
                ++$variable;
                $time += $journey['time'];
            }
            $time = round($time / $variable);
        }
        return $time;
    }

    public static function getTimeJourney($id)
    {
        $journeys = Journey::find()->where(['OR', ['start_hotspot_id' => Journey::$officeLocation, 'stop_hotspot_id' => $id], ['stop_hotspot_id' => Journey::$officeLocation, 'start_hotspot_id' => $id]])->all();
        if (!empty($journeys)) {
            return LocationDistance::arithmeticMean($journeys, 'time');
        }
        $journey = LocationDistance::find()
            ->where(['OR', ['location_parent_id' => Journey::$officeLocation, 'location_child_id' => $id], ['location_child_id' => Journey::$officeLocation, 'location_parent_id' => $id]])
            ->one();
        if ($journey !== null) {
            return $journey['time'];
        }
        return null;
    }

    public static function verifyPreviousIsValidated($intervals, $key, $journeysSupplementary)
    {
        while (isset($intervals[$key - 1]['km']) && $intervals[$key - 1]['km'] == 0) {
            $key--;
        }
        if (
            empty($intervals[$key - 1]['journey_id'])
            || $intervals[$key - 1]['journey_id'] == false
            || !isset($intervals[$key - 1]['journey_id'])
        ) {
            if (isset($journeysSupplementary[count($journeysSupplementary) - 1])) {
                $timeStampSupplementary = strtotime($journeysSupplementary[count($journeysSupplementary) - 1]['date'] . ' ' . $journeysSupplementary[count($journeysSupplementary) - 1]['start_time']);
                $intervals[$key]['start_km'] = $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'];
                $intervals[$key]['stop_km'] = $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'];
                $intervals[$key]['start_time'] = $journeysSupplementary[count($journeysSupplementary) - 1]['stop_time'];
                $intervals[$key]['start_timestamp'] = $timeStampSupplementary;
                return $journeysSupplementary[count($journeysSupplementary) - 1]['stop_km'];
            }
        }
        return $intervals[$key]['start_km'];
    }

    public static function changeJourneysSupplementary($journeysSupplementary, $journeysValidated, $journeys, $changed)
    {
        foreach ($journeys as $journey) {
            if (isset($journey[$changed])) {
                $isJourneySupplementary = false;
                foreach ($journeysSupplementary as $key => $journeySupplementary) {
                    $timeStampSupplementary = strtotime($journeySupplementary['date'] . ' ' . $journeySupplementary['start_time']);
                    $timeStampJourney = strtotime($journey['date'] . ' ' . $journey['start_time']);
                    if ($timeStampJourney == $timeStampSupplementary) {
                        $isJourneySupplementary = true;
                        $journeysSupplementary[$key]['km'] = $journey['km'];
                        $journeysSupplementary[$key]['stop_km'] = $journey['stop_km'];
                        $journeysSupplementary[$key]['start_km'] = $journey['start_km'];
                    }
                }
                if (!$isJourneySupplementary) {
                    foreach ($journeysValidated as $key => $journeyValidated) {
                        $timeStampValidated = strtotime($journeyValidated['date'] . ' ' . $journeyValidated['start_time']);
                        $timeStampJourney = strtotime($journey['date'] . ' ' . $journey['start_time']);
                        if ($timeStampJourney == $timeStampValidated) {
                            $journeysValidated[$key]['km'] = $journey['km'];
                            $journeysValidated[$key]['start_km'] = $journey['start_km'];
                            $journeysValidated[$key]['stop_km'] = $journey['stop_km'];
                        }
                    }
                }
            }
        }
        return [$journeysSupplementary, $journeysValidated];
    }
}