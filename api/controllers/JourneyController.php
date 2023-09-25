<?php

namespace api\controllers;

use api\models\Journey;
use api\models\search\JourneySearch;
use backend\modules\adm\models\User;
use backend\modules\auto\models\Car;
use backend\modules\pmp\models\Device;
use Yii;
use yii\data\ActiveDataFilter;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

/**
 * Journey controller
 */
class JourneyController extends RestController
{
    public $modelClass = 'api\models\Journey';
    public $interval;

    /**
     * @return object|null
     * @throws \yii\base\InvalidConfigException
     */
    private static function getDb()
    {
        return Yii::$app->get('ecf_auto_db');
    }

    /**
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['update']);

        $actions['index']['dataFilter'] = [
            'class' => ActiveDataFilter::class,
            'searchModel' => JourneySearch::class,
        ];
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function verbs()
    {
        return [
            'index' => ['GET']
        ];
    }

    /**
     * @return array|ActiveDataFilter
     * @throws \Exception
     */
    public function prepareDataProvider()
    {
        $searchModel = new JourneySearch();
        $params = Yii::$app->request->queryParams;
        $get = Yii::$app->request->get();
        $dataProvider = $searchModel->search($params);

        if (empty($get['token'])) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing token');
            return $this->return;
        }
        if (empty($get['uuid'])) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing uuid');
            return $this->return;
        }
        $user = User::find()->where('auth_key = :auth_key', [':auth_key' => $get['token']])->one();
        if (empty($user)) {
            Yii::$app->response->statusCode = 401;
            $this->return['status'] = 401;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }
        $device = Device::find()->where('uuid = :uuid', [':uuid' => $get['uuid']])->one();
        if (empty($device)) {
            Yii::$app->response->statusCode = 401;
            $this->return['status'] = 401;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }

        $models = $dataProvider->query->andWhere("user_id = :user_id", [':user_id' => $user['id']]);

        $filter = new ActiveDataFilter([
            'searchModel' => JourneySearch::class,
        ]);

        $filterConditions = null;
        if ($filter->load($get)) {
            $filterConditions = $filter->build();
            if ($filterConditions === false) {
                return $filter;
            }
        }

        $conditionsFilter = [];
        if (!empty($params['filter']['interval'])) {
            if (!empty($filterConditions['interval']) && $params['filter']['interval'] > 0) {
                unset($filterConditions['interval']);
            } else {
                foreach ($filterConditions as $key => $filterCondition) {
                    if (empty($filterCondition['interval'])) {
                        $conditionsFilter[$key] = $filterCondition;
                    }
                }
            }
        }

        if ($filterConditions !== null) {
            empty($conditionsFilter) ? $models->andWhere($filterConditions)->all() : $models->andWhere($conditionsFilter)->all();
        } else {
            $models->all();
        }

        if (empty($models->all())) {
            $this->return['journeys'] = [];
            $this->return['message'] = Yii::t('app', 'No journeys found');
            return $this->return;
        }

        $journey = [];
        foreach ($models->all() as $model) {
            $journey[] = [
                'id' => $model['id'],
                'user_id' => $model['user_id'],
                'status' => $model['status'],
                'interest' => empty($model['project']['name']) ? Yii::t('app', 'Interest is not set') : $model['project']['name'],
                'start' => [
                    'start_date' => explode(" ", $model['started'])[0],
                    'start_hour' => explode(" ", $model['started'])[1],
                    'start_location' => $model['startHotspot']['name'],
                ],
                'end' => [
                    'end_date' => explode(" ", $model['stopped'])[0],
                    'end_hour' => explode(" ", $model['stopped'])[1],
                    'end_location' => $model['stopHotspot']['name'],
                ],
            ];
        }
        $this->return['journeys'] = $journey;
        $message = Yii::t('app', 'Successfully sent the journeys list');
        return $this->prepareResponse($message);
    }

    /**
     * @return array
     * @throws ServerErrorHttpException
     */
    public function actionValidate()
    {
        $post = Yii::$app->request->post();

        if (empty($post['token'])) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing token');
            return $this->return;
        }
        if (empty($post['uuid'])) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing uuid');
            return $this->return;
        }
        $user = User::findOne($post['user_id']);
        if (empty($user)) {
            Yii::$app->response->statusCode = 401;
            $this->return['status'] = 401;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }
        $device = Device::find()->where('uuid = :uuid', [':uuid' => $post['uuid']])->one();
        if (empty($device)) {
            Yii::$app->response->statusCode = 401;
            $this->return['status'] = 401;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }

        $journeysData = $post['Journey'];
        $ids = ArrayHelper::getColumn($journeysData, 'id');
        $journeys = Journey::findAll($ids);
        if (Journey::loadMultiple($journeys, $post)) {
            foreach ($journeys as $journey) {
                foreach ($journeysData as $journeyData) {
                    if ($journeyData['id'] == $journey->id) {
                        if (isset($journeyData['project_id']) && isset($journeyData['type'])) {
                            $this->return['status'] = 400;
                            $this->return['message'] = Yii::t('app', 'Cannot set two scopes for a journey');
                            return $this->return;
                        }
                        if (isset($journeyData['project_id'])) {
                            $journey->project_id = $journeyData['project_id'];
                            $journey->type = null;
                        }
                        if (isset($journeyData['type'])) {
                            $journey->type = $journeyData['type'];
                            $journey->project_id = null;
                        }
                    }
                }
                $journey->updated = date('Y-m-d H:i:s');
                $journey->updated_by = $user->id;
                if (!$journey->save()) {
                    if ($journey->hasErrors()) {
                        $this->return['err'][] = $journey->errors;
                    }
                    Yii::$app->response->statusCode = 202; //The request has been accepted for processing, but the processing has not been completed.
                    $this->return['status'] = 202;
                    $this->return['message'] = Yii::t('app', 'Successfully updated the journeys but some errors occurred');
                    $this->return['err'][] = Yii::t('app', 'Failed to update the journey id {id}, rest of journeys were updated', ['id' => $journey->id]);
                    return $this->return;
                }
            }
        }
        $this->return['status'] = 200;
        $this->return['message'] = Yii::t('app', 'Successfully updated the journeys');
        return $this->return;

    }
}