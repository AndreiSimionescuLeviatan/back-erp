<?php

namespace console\controllers;

use api\modules\v1\models\ValidationOption;
use backend\modules\adm\models\User;
use backend\modules\auto\models\Car;
use backend\modules\auto\models\Journey;
use backend\modules\auto\models\Location;
use backend\modules\auto\models\LocationProject;
use backend\modules\auto\models\Project;
use backend\modules\hr\models\EmployeeLocation;
use DateTime;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class AutoValidateJourneysController extends Controller
{
    public function actionIndex()
    {
        Location::setNames();
        $superAdmin = User::getSuperAdmin();

        Yii::info("\nAuto validate journeys list cron service is running...", 'autoValidateJourneys');

        $date = new DateTime('last day of last month');
        $day = $date->format('d');

        for ($i = 1; $i <= (int)$day; $i++) {
            $carIds = Car::find()
                ->where(['deleted' => 0])
                ->select('id, user_id')
                ->all();
            $daysOlder = date('Y-m', strtotime('-1 month')) . '-' . sprintf("%02d", $i);

            foreach ($carIds as $carId) {
                $journeys = Journey::find()
                    ->where(['deleted' => 0,
                        'supplementary' => 0,
                        'merged_with_id' => 0,
                        'auto_val' => 0,
                        'car_id' => $carId->id])
                    ->andWhere(['LIKE', 'started', "%{$daysOlder}%", false])
                    ->orderBy(['started' => SORT_DESC])
                    ->all();

                foreach ($journeys as $journey) {
                    if ($journey->status == 0) {
                        $employeeLocation = EmployeeLocation::find()
                            ->where(['employee_id' => $carId->user_id])
                            ->andWhere(['location_id' => $journey->start_hotspot_id])
                            ->orWhere(['location_id' => $journey->stop_hotspot_id])
                            ->one();
                        if (!empty($employeeLocation)) {
                            $journey->status = 1;
                            $journey->type = $employeeLocation->type;
                            $journey->project_id = $employeeLocation->project_id;
                            $journey->validation_option_id = $employeeLocation->validation_option_id;
                            $journey->auto_val = 1;
                            $journey->updated = date('Y-m-d H:i:s');
                            $journey->updated_by = $superAdmin;
                            $journey->save();
                        }


                        $locationProjects = LocationProject::find()
                            ->orWhere(['location_id' => $journey->start_hotspot_id])
                            ->orWhere(['location_id' => $journey->stop_hotspot_id])
                            ->all();

                        if (!empty($locationProjects)) {
                            $validProjectsIds = [];
                            foreach ($locationProjects as $locationProject) {
                                $project = Project::find()
                                    ->where(['id' => $locationProject->project_id])
                                    ->andWhere(['<=', 'start_date', $journey->started])
                                    ->andWhere(['>=', 'stop_date', $journey->stopped])
                                    ->one();
                                if ($project !== null) {
                                    $validProjectsIds[] = $project->id;
                                }
                            }
                            if (count($validProjectsIds) === 1) {
                                $journey->status = 1;
                                $journey->project_id = $validProjectsIds[0];
                                $journey->auto_val = 1;
                                $journey->updated = date('Y-m-d H:i:s');
                                $journey->updated_by = $superAdmin;
                                $journey->save();
                            }
                        } else if (isset(Location::$names[$journey->start_hotspot_id]) && isset(Location::$names[$journey->stop_hotspot_id])
                            && str_contains(strtolower(Location::$names[$journey->start_hotspot_id]), 'acasa')
                            && str_contains(strtolower(Location::$names[$journey->stop_hotspot_id]), 'sediu')
                            || isset(Location::$names[$journey->start_hotspot_id]) && isset(Location::$names[$journey->stop_hotspot_id])
                            && str_contains(strtolower(Location::$names[$journey->stop_hotspot_id]), 'acasa')
                            && str_contains(strtolower(Location::$names[$journey->start_hotspot_id]), 'sediu') ) {
                            $journey->status = 1;
                            $journey->type = 2;
                            $journey->validation_option_id = 17;
                            $journey->auto_val = 1;
                            $journey->save();
                        } else {
                            $validatedJourney = Journey::find()
                                ->where(['deleted' => 0,
                                    'supplementary' => 0,
                                    'merged_with_id' => 0,
                                    'car_id' => $journey->car_id,
                                    'start_hotspot_id' => $journey->start_hotspot_id,
                                    'stop_hotspot_id' => $journey->stop_hotspot_id,
                                    'status' => 1])
                                ->andWhere(['not', ['validation_option_id' => null]])
                                ->orderBy(['started' => SORT_DESC])
                                ->all();
                            $validations = [];

                            if (!empty($validatedJourney)) {
                                foreach ($validatedJourney as $validJourney) {
                                    $validations[] = $validJourney->validation_option_id;
                                }
                                $validationData = ValidationOption::getValidationData($validations);

                                $journey->status = 1;
                                $journey->type = $validationData['type'];
                                $journey->validation_option_id = $validationData['validation_option_id'];
                                $journey->auto_val = 1;
                                $journey->updated = date('Y-m-d H:i:s');
                                $journey->updated_by = $superAdmin;
                                $journey->save();
                            } else {
                                $validatedJourney = Journey::find()
                                    ->where(['deleted' => 0,
                                        'supplementary' => 0,
                                        'merged_with_id' => 0,
                                        'car_id' => $journey->car_id,
                                        'start_hotspot_id' => $journey->start_hotspot_id,
                                        'stop_hotspot_id' => $journey->stop_hotspot_id,
                                        'status' => 1])
                                    ->andWhere(['not', ['project_id' => null]])
                                    ->orderBy(['started' => SORT_DESC])
                                    ->all();

                                if (!empty($validatedJourney)) {
                                    foreach ($validatedJourney as $validJourney) {
                                        $validations[] = $validJourney->project_id;
                                    }
                                    $validationData = ValidationOption::getValidationData($validations);

                                    $journey->status = 1;
                                    $journey->type = $validationData['type'];
                                    $journey->project_id = $validationData['validation_option_id'];
                                    $journey->auto_val = 1;
                                    $journey->updated = date('Y-m-d H:i:s');
                                    $journey->updated_by = $superAdmin;
                                    $journey->save();
                                }
                            }
                        }
                    }
                }
            }
        }

        echo "\nJourneys was validated\n";
        return ExitCode::OK;
    }
}