<?php

namespace api\modules\v1\controllers;

use api\models\Project;
use api\models\search\JourneySearch;
use api\modules\v1\models\Car;
use api\modules\v1\models\Journey;
use api\modules\v1\models\Location;
use api\modules\v1\models\LocationName;
use api\modules\v1\models\ValidationOption;
use common\components\DateTimeHelper;
use common\components\HttpStatus;
use DateTime;
use Yii;
use yii\data\ActiveDataFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\ServerErrorHttpException;

/**
 * Journey controller
 */
class JourneyController extends RestV1Controller
{
    public $modelClass = 'api\modules\v1\models\Journey';
//    public $interval;
    public $skipValues = [
        "added",
        "added_by",
        "updated",
        "updated_by",
        "company_id",
        "contract_number",
        "acquisition_type",
        "deleted",
        "first_car",
        "first_car_id",
        "gps_car_id",
        "brand_id",
        "model_id",
        "nexus_location_id",
    ];

    /**
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['view']);
//        $actions['index']['dataFilter'] = [
//            'class' => ActiveDataFilter::class,
//            'searchModel' => JourneySearch::class,
//        ];
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }

    function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * @return array|ActiveDataFilter
     * @throws \Exception
     */
    public function prepareDataProvider()
    {
        $journeys = [];
        $projects = [];
        $journeysUsedCars = [];//the car used in user journeys. used to create app filter
        $searchModel = new JourneySearch();
        $params = Yii::$app->request->queryParams;
        $getFilters = Yii::$app->request->get('filter');
        $dataProvider = $searchModel->search($params);
        $limit = 10;

        if (
            isset($params['limit'])
            && !empty($params['limit'])
        ) {
            $limit = $params['limit'];
        }

        $attributes = [
            'user_id' => Yii::$app->user->id,
            'merged_with_id' => 0,
            'supplementary' => 0
        ];

        if (isset($getFilters['project_id'])) {
            $attributes['project_id'] = $getFilters['project_id'];
        }

        if (isset($getFilters['car_id'])) {
            $attributes['car_id'] = $getFilters['car_id'];
        }

        if (isset($getFilters['type'])) {
            if (
                $getFilters['type'] === 'administrative'
                || $getFilters['type'] === 'work'
            ) {
                $attributes['validation_option_id'] = $getFilters['scope'];
            } else if ($getFilters['type'] === 'project') {
                $attributes['project_id'] = $getFilters['scope'];
            }
        }

        if ($getFilters['status'] != Journey::STATUS_FOR_DELETED) {
            $attributes['status'] = $getFilters['status'];
            $attributes['deleted'] = Journey::DELETED_NO;
        } else {
            $attributes['deleted'] = Journey::DELETED_YES;
        }

        $this->return['all_journeys_count'] = Journey::countByAttributes($attributes);
        $this->return['validation_option_home_work_id'] = ValidationOption::VALIDATION_OPTION_HOME_WORK_ID;
        $models = $dataProvider->query->where($attributes)->limit($limit)->orderBy(['started' => (int)$getFilters['sort']]);

        if (empty($models)) {
            $this->return['journeys'] = [];
            $this->return['message'] = Yii::t('api-auto', 'No journeys found');
            return $this->return;
        }

        Project::setNamesAuto();
        ValidationOption::getValidationOption();

        foreach ($models->all() as $model) {
            $journeysUsedCars[] = $model->car_id;

            $tmpJourney = [
                'id' => $model['id'],
                'user_id' => $model['user_id'],
                'status' => $model['status'],
                'type' => $model['type'],
                'start_date' => Journey::formatDate($model['started'], true),
                'start_hour' => explode(' ', $model['started'])[1],
                'start_location' => LocationName::getLocationName($model['startHotspot']['id']) !== null ? LocationName::getLocationName($model['startHotspot']['id'])
                    : $model['startHotspot']->getName(),
                'start_location_id' => $model['startHotspot']['id'],
                'end_date' => Journey::formatDate($model['stopped'], true),
                'end_hour' => explode(' ', $model['stopped'])[1],
                'end_location' => LocationName::getLocationName($model['stopHotspot']['id']) !== null ? LocationName::getLocationName($model['stopHotspot']['id'])
                    : $model['stopHotspot']->getName(),
                'end_location_id' => $model['stopHotspot']['id'],
                'distance' => $model['distance'],
                'duration' => DateTimeHelper::getDuration($model['time']),
                'car_id' => $model['car_id'],
            ];

            if ((float)explode('M', ini_get('memory_limit'))[0] - (float)explode('MB', $this->formatBytes(memory_get_peak_usage()))[0] < 10) {
                return Yii::t('api-auto', 'The maximum memory allocated on the server has been reached, please contact an administrator');
            }

            if (!empty($model['type'])) {
                $tmpJourney['interest'] = Journey::getScopeByType($model['type'], $model['validation_option_id']);
            }

            if (
                !empty($model['project_id'])
                && !empty(Project::$namesAuto[$model['project_id']])
            ) {
                $tmpJourney['interest'] = Journey::getScopeByProjectID($model['project_id']);
                $projects[strtolower(Project::$namesAuto[$model['project_id']])] = [
                    "id" => $model['project_id'],
                    "name" => Project::$namesAuto[$model['project_id']],
                    'type' => 'project'
                ];
            }

            if (
                $model['validation_option_id'] !== null
                && isset(ValidationOption::$validationOptionWork[$model['validation_option_id']])
            ) {
                $projects[strtolower(ValidationOption::$validationOptionWork[$model['validation_option_id']])] = [
                    'id' => $model['validation_option_id'],
                    'name' => ValidationOption::$validationOptionWork[$model['validation_option_id']],
                    'type' => 'work'
                ];
            }

            if (
                $model['validation_option_id'] !== null
                && isset(ValidationOption::$validationOptionAdministrative[$model['validation_option_id']])
            ) {
                $projects[strtolower(ValidationOption::$validationOptionAdministrative[$model['validation_option_id']])] = [
                    'id' => $model['validation_option_id'],
                    'name' => ValidationOption::$validationOptionAdministrative[$model['validation_option_id']],
                    'type' => 'administrative'
                ];
            }

            if ($getFilters['sort'] == Journey::SORT_DESC) {
                $journeys[] = $tmpJourney;
            } else {
                $journeys[strtotime($model['started'])] = $tmpJourney;
            }

            if (!in_array($model['car_id'], $journeysUsedCars)) {
                $journeysUsedCars[] = $model['car_id'];
            }
        }

        if ((float)explode('M', ini_get('memory_limit'))[0] - (float)explode('MB', $this->formatBytes(memory_get_peak_usage()))[0] < 10) {
            return Yii::t('api-auto', 'The maximum memory allocated on the server has been reached, please contact an administrator');
        }

        $newMergedJourneys = [];
        $mergedWithIds = Journey::find()->where('user_id = :user_id AND merged_with_id != 0', [':user_id' => Yii::$app->user->id])->asArray()
            ->distinct()->select('merged_with_id')->all();
        foreach ($mergedWithIds as $mergedWithId) {
            $newMergedJourneys[] = $mergedWithId['merged_with_id'];
        }

        if (
            !empty($getFilters)
            && !empty($getFilters['week'])
        ) {
            $filteredJourneys = $searchModel->filterJourneysByWeekDay($getFilters['week'], $journeys);
            if ($filteredJourneys !== null) {
                $journeys = $filteredJourneys;
            }
        }

        $daysFilterName['monday'] = Yii::t('api-auto', 'Monday');
        $daysFilterName['tuesday'] = Yii::t('api-auto', 'Tuesday');
        $daysFilterName['wednesday'] = Yii::t('api-auto', 'Wednesday');
        $daysFilterName['thursday'] = Yii::t('api-auto', 'Thursday');
        $daysFilterName['friday'] = Yii::t('api-auto', 'Friday');
        $daysFilterName['saturday'] = Yii::t('api-auto', 'Saturday');
        $daysFilterName['sunday'] = Yii::t('api-auto', 'Sunday');

        $intervalsFilterName[0] = Yii::t('api-auto', 'All time interval');
        $intervalsFilterName[7] = Yii::t('api-auto', 'Last week');
        $intervalsFilterName[30] = Yii::t('api-auto', 'Last month');
        $intervalsFilterName[60] = Yii::t('api-auto', 'Last 2 months');
        $intervalsFilterName[90] = Yii::t('api-auto', 'Last 3 months');

        $scopeButtonsText[1] = ucfirst(Yii::t('api-auto', 'administrative'));
        $scopeButtonsText[2] = ucfirst(Yii::t('api-auto', 'office'));
        $scopeTexts['administrativ'] = 'Activități în interes personal (ex: Cumpărături etc.)';
        $scopeTexts['serviciu'] = 'Activități în interes de serviciu care nu au legatură cu un proiect (ex: Șantiere, minister etc.)';

        $this->return['days_filter'] = $daysFilterName;
        $this->return['intervals_filter'] = $intervalsFilterName;
        $this->return['all_projects'] = Project::$namesAuto;
        if (!isset($getFilters['scope'])) {
            $this->return['projects'] = $projects;
        }
        $this->return['journeys'] = $journeys;
        $this->return['scope_buttons'] = $scopeButtonsText;
        $this->return['scope_texts'] = $scopeTexts;
        $this->return['administrative_options'] = ValidationOption::$validationOptionAdministrative;
        $this->return['work_options'] = ValidationOption::$validationOptionWork;
        $this->return['new_created_journey'] = $newMergedJourneys;
        $this->return['journeys_used_cars'] = Car::find()
            ->select('car.id AS car_id, car.plate_number AS car_plate_number, brand.name  AS car_brand, model.name  AS car_model')
            ->joinWith(['brand', 'brandModel model'])
            ->where(['car.deleted' => '0', 'car.id' => $journeysUsedCars])
            ->asArray()->all();
        $message = Yii::t('api-auto', 'Successfully sent the journeys list');

        if ((float)explode('M', ini_get('memory_limit'))[0] - (float)explode('MB', $this->formatBytes(memory_get_peak_usage()))[0] < 10) {
            return Yii::t('api-auto', 'The maximum memory allocated on the server has been reached, please contact an administrator');
        }

        return $this->prepareResponse($message);
    }

    /**
     * @return array
     * @throws ServerErrorHttpException
     */
    public function actionValidate()
    {
        $post = Yii::$app->request->post();

        $journeysData = $post['Journey'];
        $ids = ArrayHelper::getColumn($journeysData, 'id');
        $journeys = Journey::findAll($ids);
        if (Journey::loadMultiple($journeys, $post)) {
            foreach ($journeys as $journey) {
                foreach ($journeysData as $journeyData) {
                    if ($journeyData['id'] == $journey->id) {
                        if ( isset($journeyData['project_id'])
                            && isset($journeyData['type']) ) {
                            $this->return['status'] = HttpStatus::BAD_REQUEST;
                            $this->return['message'] = Yii::t('api-auto', 'Cannot set two scopes for a journey');
                            return $this->return;
                        }
                        if (isset($journeyData['project_id'])) {
                            $journey->project_id = $journeyData['project_id'];
                            $journey->type = null;
                            $journey->validation_option_id = null;
                        }
                        if (isset($journeyData['type'])) {
                            $journey->type = $journeyData['type'];
                            $journey->validation_option_id = $journeyData['type_id'];
                            $journey->project_id = null;
                        }
                    }
                }
                $journey->updated = date('Y-m-d H:i:s');
                $journey->updated_by = Yii::$app->user->id;
                if (!$journey->save()) {
                    if ($journey->hasErrors()) {
                        $this->return['err'][] = $journey->errors;
                    }
                    $this->return['status'] = HttpStatus::ACCEPTED;
                    Yii::$app->response->statusCode = $this->return['status']; //The request has been accepted for processing, but the processing has not been completed.
                    $this->return['message'] = Yii::t('api-auto', 'Successfully updated the journeys but some errors occurred');
                    $this->return['err'][] = Yii::t('api-auto', 'Failed to update the journey id {id}, rest of journeys were updated', ['id' => $journey->id]);
                    return $this->return;
                }
            }
        }
        $this->return['status'] = HttpStatus::OK;
        $this->return['message'] = Yii::t('api-auto', 'Successfully updated the journeys');
        return $this->return;

    }

    /**
     * @param $id
     * @return array
     * @throws \Exception
     * @deprecated Replaced by journey/details
     * @todo  Remove after new app version release
     */
    public function actionView($id)
    {

        $journey = [];
        $journeyModel = Journey::find()
            ->where('journey.id = :id', [':id' => $id])
            ->with(['car', 'car.brand', 'car.brandModel', 'startHotspot', 'stopHotspot'])
            ->asArray()
            ->one();
        if ( empty($journeyModel['car']) ||
            empty($journeyModel['car']['brand']) ||
            empty($journeyModel['car']['brandModel']) ||
            empty($journeyModel['startHotspot']) ||
            empty($journeyModel['stopHotspot']) ) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'] ;
            $this->return['message'] = Yii::t('api-auto', 'Incomplete or invalid journey data');
            return $this->return;
        }

        $this->return['journey'] = Journey::buildJourneyData($journeyModel, $this->skipValues);;
        $this->return['_journey'] = $journeyModel;
        return $this->return;
    }

    /**
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function actionDetails($id)
    {
        ValidationOption::getValidationOption();
        $journey = [];
        $journeyModel = Journey::find()
            ->where('journey.id = :id', [':id' => $id])
            ->with(['car', 'car.brand', 'car.brandModel', 'startHotspot', 'stopHotspot'])
            ->asArray()
            ->one();
        if ( empty($journeyModel['car']) ||
            empty($journeyModel['car']['brand']) ||
            empty($journeyModel['car']['brandModel']) ||
            empty($journeyModel['startHotspot']) ||
            empty($journeyModel['stopHotspot']) ) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', 'Incomplete or invalid journey data');
            return $this->return;
        }

        $this->return['journey'] = Journey::buildJourneyData($journeyModel, $this->skipValues);
        $this->return['_journey'] = $journeyModel;
        $this->return['projects'] = Project::find()->select("id, name")->where("deleted = 0")->asArray()->all();
        return $this->return;
    }

    /**
     * @return array
     * @throws ServerErrorHttpException
     */
    public function actionDeleteJourney()
    {
        $post = Yii::$app->request->post();
        if (!empty($post)) {
            $journeysToDelete = Journey::find()->where(['in', 'id', explode(',', $post['journeysIds'])])->all();
        }
        if (!empty($journeysToDelete)) {
            foreach ($journeysToDelete as $journey) {
                $journey->deleted = 1;
                $journey->updated = date('Y-m-d H:i:s');
                $journey->updated_by = Yii::$app->user->id;

                if (!$journey->save()) {
                    if ($journey->hasErrors()) {
                        $this->return['err'][] = $journey->errors;
                    }
                    $this->return['status'] = HttpStatus::ACCEPTED;
                    Yii::$app->response->statusCode = $this->return['status']; //The request has been accepted for processing, but the processing has not been completed.
                    $this->return['message'] = Yii::t('api-auto', 'Successfully deleted the journeys but some errors occurred');
                    $this->return['err'][] = Yii::t('api-auto', 'Failed to delete the journey id {id}, rest of journeys were deleted', ['id' => $journey->id]);
                    return $this->return;
                }
            }
            $this->return['status'] = HttpStatus::OK;
            $this->return['message'] = Yii::t('api-auto', 'Successfully deleted!');
        }
        return $this->return;
    }

    public function actionActivate()
    {
        $post = Yii::$app->request->post();
        if (empty($post)) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', 'No data received');
            return $this->return;
        }

        $journeysToActivate = Journey::find()->where(['in', 'id', explode(',', $post['journeysIds'])])->all();
        if (empty($journeysToActivate)) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', 'Journeys not found');
            return $this->return;
        }

        foreach ($journeysToActivate as $journey) {
            $journey->deleted = 0;
            $journey->updated = date('Y-m-d H:i:s');
            $journey->updated_by = Yii::$app->user->id;
            if (!$journey->save()) {
                if ($journey->hasErrors()) {
                    $this->return['err'][] = $journey->errors;
                }
                $this->return['status'] = HttpStatus::ACCEPTED;
                Yii::$app->response->statusCode = $this->return['status']; //The request has been accepted for processing, but the processing has not been completed.
                $this->return['message'] = Yii::t('api-auto', 'Successfully activated the journeys but some errors occurred');
                $this->return['err'][] = Yii::t('api-auto', 'Failed to activate the journey id {id}, rest of journeys were activated', ['id' => $journey->id]);
                return $this->return;
            }
        }
        $this->return['status'] = HttpStatus::OK;
        $this->return['message'] = Yii::t('api-auto', 'Successfully activated!');
        return $this->return;
    }

    public function actionGetLocations($userId, $status)
    {
        $locations = [];

        $journeys = Journey::getByUserIdAndStatus($userId, $status);
        foreach ($journeys as $journey) {
            $locations += $journey->getLocations();
        }

        $this->return['status'] = HttpStatus::OK;
        $this->return['message'] = Yii::t('api-auto', 'Successfully get the locations!');
        $this->return['locations'] = $locations;
        return $this->return;
    }

    /**
     * @throws \yii\db\StaleObjectException
     */
    public function actionMergeJourneys()
    {
        $post = Yii::$app->request->post();
        $searchModel = new JourneySearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);
        $newJourneys = [];
        $journeyIds = explode(',', $post["journeysIds"]);

        for ($i = 0; $i < count($journeyIds); $i++) {
            if (empty($post)) {
                $this->return['status'] = HttpStatus::BAD_REQUEST;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-auto', 'No data received');
                return $this->return;
            }
            if (!isset($post['status'])) {
                $this->return['status'] = HttpStatus::BAD_REQUEST;;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-auto', 'No status received');
                return $this->return;
            }
            $journey = Journey::findOneByAttributes(['id' => $journeyIds[$i], 'status' => $post['status']]);
            if (!empty($journey)) {
                if ($journey['deleted'] == Journey::DELETED_YES) {
                    $this->return['status'] = HttpStatus::BAD_REQUEST;
                    Yii::$app->response->statusCode = $this->return['status'];
                    $this->return['message'] = Yii::t('api-auto', 'Something went wrong! Deleted must be 0');
                    return $this->return;
                }
                if ( $i == 0
                    && $journey->id == (int)$journeyIds[$i] ) {
                    $newJourneys[$i . '_start_hotspot_id'] = $journey->start_hotspot_id;
                    $newJourneys[$i . '_reverse_start_hotspot_id'] = $journey->stop_hotspot_id;
                    $newJourneys[$i . '_date_hour'] = $journey->started;
                    $newJourneys[$i . '_stop_date_hour'] = $journey->stopped;
                    $newJourneys[$i . '_duration'] = $journey->time;
                    $newJourneys[$i . '_car_id'] = $journey->car_id;
                    $newJourneys[$i . '_fuel'] = $journey->fuel;
                    $newJourneys[$i . '_odo'] = $journey->odo;
                    $newJourneys[$i . '_stand_time'] = $journey->stand_time;
                    $newJourneys[$i . '_user_id'] = $journey->user_id;
                    $newJourneys['total_distance'] = 0;
                    $newJourneys['total_time'] = 0;
                }
                if ( $i == count($journeyIds) - 1
                    && $journey->id == (int)$journeyIds[count($journeyIds) - 1] ) {
                    $newJourneys[$i . '_stop_hotspot_id'] = $journey->stop_hotspot_id;
                    $newJourneys[$i . '_reverse_start_hotspot_id'] = $journey->start_hotspot_id;
                    $newJourneys[$i . '_date_hour'] = $journey->stopped;
                    $newJourneys[$i . '_start_date_hour'] = $journey->started;
                    $newJourneys[$i . '_duration'] = $journey->time;
                    $newJourneys[$i . '_car_id'] = $journey->car_id;
                    $newJourneys[$i . '_fuel'] = $journey->fuel;
                    $newJourneys[$i . '_odo'] = $journey->odo;
                    $newJourneys[$i . '_stand_time'] = $journey->stand_time;
                    $newJourneys[$i . '_exploit'] = $journey->exploit;
                    $newJourneys[$i . '_mark'] = $journey->mark;
                    $newJourneys[$i . '_observation'] = $journey->observation;
                    $newJourneys[$i . '_speed'] = $journey->speed;
                }
                if (!empty($newJourneys)) {
                    $newJourneys['total_distance'] += $journey->distance;
                    $newJourneys['total_time'] += $journey->time;
                }
            }
            if ( $i == count($journeyIds) - 1
                && $newJourneys['0_car_id'] != $newJourneys[count($journeyIds) - 1 . '_car_id'] ) {
                $this->return['status'] = HttpStatus::BAD_REQUEST;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-auto', 'Journeys must have the same car');
                return $this->return;
            }
            $journey->deleted = Journey::DELETED_YES;
            $journey->updated = date('Y-m-d H:i:s');
            $journey->updated_by = Yii::$app->user->id;
            if (!$journey->save()) {
                if ($journey->hasErrors()) {
                    $this->return['err'][] = $journey->errors;
                }
                $this->return['status'] = HttpStatus::ACCEPTED;
                Yii::$app->response->statusCode = $this->return['status']; //The request has been accepted for processing, but the processing has not been completed.
                $this->return['message'] = Yii::t('api-auto', 'Successfully activated the journeys but some errors occurred');
                $this->return['err'][] = Yii::t('api-auto', 'Failed to activate the journey id {id}, rest of journeys were activated', ['id' => $journey->id]);
                return $this->return;
            }
        }
        $started = $newJourneys['0_date_hour'];
        $stopped = $newJourneys[count($journeyIds) - 1 . '_date_hour'];
        $startLocation = $newJourneys['0_start_hotspot_id'];
        $stopLocation = $newJourneys[count($journeyIds) - 1 . '_stop_hotspot_id'];
        if ($post['sort'] == Journey::SORT_DESC) {
            $started = $newJourneys[count($journeyIds) - 1 . '_start_date_hour'];
            $stopped = $newJourneys['0_stop_date_hour'];
            $startLocation = $newJourneys[count($journeyIds) - 1 . '_reverse_start_hotspot_id'];
            $stopLocation = $newJourneys['0_reverse_start_hotspot_id'];
        }
        $duration = self::returnDiffTimeStamp($started, $stopped);
        if ($newJourneys['0_odo'] > $newJourneys[count($journeyIds) - 1 . '_odo']) {
            $odo = $newJourneys['0_odo'];
        } else {
            $odo = $newJourneys[count($journeyIds) - 1 . '_odo'];
        }
        $attributes = [
            'car_id' => $newJourneys['0_car_id'],
            'start_hotspot_id' => $startLocation,
            'stop_hotspot_id' => $stopLocation,
            'distance' => (float)$newJourneys['total_distance'],
            'fuel' => (float)$newJourneys['0_fuel'] + (float)$newJourneys[count($journeyIds) - 1 . '_fuel'],
            'odo' => $odo,
            'start_odo' => (float)$odo - $newJourneys['total_distance'] ,
            'started' => $started,
            'stopped' => $stopped,
            'time' => (float)$duration,
            'stand_time' => (float)$newJourneys['0_stand_time'] + (float)$newJourneys[count($journeyIds) - 1 . '_stand_time'],
            'exploit' => $newJourneys[count($journeyIds) - 1 . '_exploit'],
            'speed' => $newJourneys[count($journeyIds) - 1 . '_speed'],
            'status' => 0,
            'user_id' => $newJourneys['0_user_id'],
            'project_id' => null,
            'type' => null,
            'merged_with_id' => 0,
            'observation' => $newJourneys[count($journeyIds) - 1 . '_observation'],
            'deleted' => Journey::DELETED_NO,
            'added' => date('Y-m-d H:i:s'),
            'added_by' => Yii::$app->user->id
        ];
        $newJourney = Journey::createByAttributes($attributes);
        for ($i = 0; $i < count($journeyIds); $i++) {
            $journey = Journey::find()->where('id = :id AND deleted = 1', [':id' => (int)$journeyIds[$i]])->one();
            $journey->merged_with_id = $newJourney->id;
            if (!$journey->save()) {
                if ($journey->hasErrors()) {
                    $this->return['err'][] = $journey->errors;
                }
                $this->return['status'] = HttpStatus::ACCEPTED;
                Yii::$app->response->statusCode = $this->return['status']; //The request has been accepted for processing, but the processing has not been completed.
                $this->return['message'] = Yii::t('api-auto', 'Successfully activated the journeys but some errors occurred');
                $this->return['err'][] = Yii::t('api-auto', 'Failed to activate the journey id {id}, rest of journeys were activated', ['id' => $journey->id]);
                return $this->return;
            }
        }
        $newMergedJourneys = [];
        $mergedWithIds = Journey::find()->where('user_id = :user_id AND merged_with_id != 0', [':user_id' => Yii::$app->user->id])->asArray()
            ->distinct()->select('merged_with_id')->all();
        foreach ($mergedWithIds as $mergedWithId) {
            $newMergedJourneys[] = $mergedWithId['merged_with_id'];
        }
        $models = $dataProvider->query->andWhere("user_id = :user_id AND merged_with_id = 0", [':user_id' => $newJourneys['0_user_id']]);
        $journey = [];
        $count = 0;
        foreach ($models->orderBy(['started' => $post['sort']])->all() as $model) {
            $count++;
            if ($model['status'] == $post['status']) {
                $startLocation = Location::findOneByAttributes(['id' => $model['start_hotspot_id']]);
                $endLocation = Location::findOneByAttributes(['id' => $model['stop_hotspot_id']]);
                $timeStamp = null;
                if ($post['sort'] == Journey::SORT_ASC) {
                    $timeStamp = strtotime($model['started']);
                }
                $journey[$timeStamp !== null ? $timeStamp : $count] = [
                    'id' => $model['id'],
                    'user_id' => $model['user_id'],
                    'status' => $model['status'],
                    'type' => $model['type'],
                    'start_date' => Journey::formatDate($model['started'], true),
                    'start_hour' => explode(" ", $model['started'])[1],
                    'start_location' => !empty($startLocation['address']) ? $startLocation['address'] : $startLocation['name'],
                    'interest' => empty($model['project']['name']) ? Yii::t('api-auto', 'Interest is not set') : $model['project']['name'],
                    'end_date' => Journey::formatDate($model['stopped'], true),
                    'end_hour' => explode(" ", $model['stopped'])[1],
                    'end_location' => !empty($endLocation['name']) ? $endLocation['name'] : $endLocation['address'],
                    'distance' => $model['distance'],
                    'duration' => DateTimeHelper::getDuration($model['time']),
                    'car_id' => $model['car_id']
                ];
            }
        }

        $this->return['status'] = HttpStatus::OK;
        $this->return['message'] = Yii::t('api-auto', 'Successfully merged journeys!');
        $this->return['journeys'] = $journey;
        $this->return['new_created_journey'] = $newMergedJourneys;
        return $this->return;
    }

    public function actionUpdateHotspotName()
    {
        $post = Yii::$app->request->post();
        $journey = [];

        if (empty($post['id'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', 'No location ID received');
            return $this->return;
        }

        if (empty($post['journey_id'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', 'No journey ID received');
            return $this->return;
        }

        if (empty($post['name'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', 'No journey name received');
            return $this->return;
        }

        $journeyModel = Journey::findOneByAttributes(['id' => $post['journey_id'], 'user_id' => Yii::$app->user->id, 'deleted' => 0]);

        if (!empty($journeyModel)) {
            $locationModel = Location::findOneByAttributes(['deleted' => 0, 'id' => $journeyModel[$post['hotspot'] . '_id']]);
            if (!empty($locationModel)) {
                $locationName = LocationName::findOneByAttributes([
                    'location_id' => $journeyModel[$post['hotspot'] . '_id'],
                    'user_id' => Yii::$app->user->id
                ]);
                if (empty($locationName)) {
                    $locationName = new LocationName();
                    $locationName->added = date('Y-m-d H:i:s');
                    $locationName->added_by = Yii::$app->user->id;
                } else {
                    $locationName->updated = date('Y-m-d H:i:s');
                    $locationName->updated_by = Yii::$app->user->id;
                }
                $locationName->location_new_name = $post['name'];
                $locationName->location_id = $post['id'];
                $locationName->user_id = Yii::$app->user->id;
                $locationName->save();
            }
        }

        $this->return['journey'] = Journey::buildJourneyData($journeyModel, $this->skipValues);
        $this->return['_journey'] = $journeyModel;
        return $this->return;
    }

    public static function returnDiffTimeStamp($start, $stop)
    {
        // functie de adaugare 2 timpi si returnarea diferenta acestora
        $sum = strtotime('00:00:00');
        $started = explode(' ', $start);
        $stopped = explode(' ', $stop);
        $startTime = strtotime($started[1]) - $sum;
        $stopTime = strtotime($stopped[1]) - $sum;
        $time = $stopTime - $startTime;
        $h = intval($time / 3600);
        $h < 10 ? $h = "0" . $h : '';
        $time = $time - ($h * 3600);
        $m = intval($time / 60);
        $m < 10 ? $m = "0" . $m : '';
        $s = $time - ($m * 60);
        $s < 10 ? $s = "0" . $s : '';
        return ($h * 3600) + ($m * 60) + $s;
    }
}