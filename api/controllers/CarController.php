<?php

namespace api\controllers;

use api\models\Accessory;
use api\models\Car;
use api\models\CarDocument;
use api\models\CarOperation;
use api\models\CarZone;
use api\models\Journey;
use api\models\Zone;
use api\models\ZoneOption;
use backend\components\ImageHelper;
use backend\modules\adm\models\User;
use backend\modules\auto\models\CarAccessory;
use backend\modules\auto\models\Fuel;
use backend\modules\crm\models\Brand;
use backend\modules\crm\models\BrandModel;
use backend\modules\crm\models\Company;
use common\components\SendSharePointMailHelper;
use backend\modules\pmp\models\Device;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Mpdf\Output\Destination;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseFileHelper;
use yii\helpers\FileHelper;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Car controller
 */
class CarController extends RestController
{
    public $modelClass = 'api\models\Car';

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

        // disable the "delete", "create", "index" actions
        unset($actions['delete'], $actions['view']);

        return $actions;
    }

    /**
     * @return array
     * @throws HttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAvailableCars()
    {
        Company::setNames();
        Brand::setNames();
        BrandModel::setNames();
        Fuel::setNames();

        $token = Yii::$app->request->get('token');
        if (empty($token)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing token');
            return $this->return;
        }
        $uuid = Yii::$app->request->get('uuid');
        if (empty($uuid)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing uuid');
            return $this->return;
        }

        $user = User::find()->where('auth_key = :auth_key', [':auth_key' => $token])->one();
        if (empty($user)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }

        $device = Device::find()->where('uuid = :uuid', [':uuid' => $uuid])->one();
        if (empty($device)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }

        $getPreviousCar = Yii::$app->request->get('previous_car');
        if (!empty($getPreviousCar)) {
            Car::setCarStatus(0, $getPreviousCar, $user->id, null);
        }

        $companies = $brands = $models = $carsFiltered = [];
        $cars = Car::find()
            ->select(['id', 'plate_number', 'vin', 'fabrication_year', 'fuel_id', 'color', 'company_id', 'brand_id', 'model_id', 'user_id'])
            ->where("deleted = 0 AND status = 0 AND (user_id IS NULL OR user_id = '')")
            ->all();

        foreach ($cars as $key => $car) {
            $companySet = $brandSet = $modelSet = true;
            if (!empty($_GET['company_id']) && empty($_GET['brand_id']) && empty($_GET['model_id'])) {
                $brandSet = $modelSet = false;
                if ($car['company_id'] == $_GET['company_id']) {
                    $brands[$car['brand_id']] = [
                        'id' => $car['brand_id'],
                        'name' => Brand::$names[$car['brand_id']]
                    ];
                    $models[$car['model_id']] = [
                        'id' => $car['model_id'],
                        'name' => BrandModel::$names[$car['model_id']]
                    ];
                    $brandSet = $modelSet = true;
                }
            }

            if (empty($_GET['company_id']) && !empty($_GET['brand_id']) && empty($_GET['model_id'])) {
                $companySet = $modelSet = false;
                if ($car['brand_id'] == $_GET['brand_id']) {
                    $companies[$car['company_id']] = [
                        'id' => $car['company_id'],
                        'name' => Company::$names[$car['company_id']]
                    ];
                    $models[$car['model_id']] = [
                        'id' => $car['model_id'],
                        'name' => BrandModel::$names[$car['model_id']]
                    ];
                    $companySet = $modelSet = true;
                }
            }

            if (empty($_GET['company_id']) && empty($_GET['brand_id']) && !empty($_GET['model_id'])) {
                $companySet = $brandSet = false;
                if ($car['model_id'] == $_GET['model_id']) {
                    $companies[$car['company_id']] = [
                        'id' => $car['company_id'],
                        'name' => Company::$names[$car['company_id']]
                    ];
                    $brands[$car['brand_id']] = [
                        'id' => $car['brand_id'],
                        'name' => Brand::$names[$car['brand_id']]
                    ];
                    $companySet = $brandSet = true;
                }
            }

            if (!empty($_GET['company_id']) && !empty($_GET['brand_id']) && empty($_GET['model_id'])) {
                $brandSet = $modelSet = $companySet = false;
                if ($car['company_id'] == $_GET['company_id'] && $car['brand_id'] == $_GET['brand_id']) {
                    $models[$car['model_id']] = [
                        'id' => $car['model_id'],
                        'name' => BrandModel::$names[$car['model_id']]
                    ];
                    $modelSet = true;
                }
                if ($car['company_id'] == $_GET['company_id']) {
                    $brands[$car['brand_id']] = [
                        'id' => $car['brand_id'],
                        'name' => Brand::$names[$car['brand_id']]
                    ];
                    $brandSet = true;
                }
                if ($car['brand_id'] == $_GET['brand_id']) {
                    $companies[$car['company_id']] = [
                        'id' => $car['company_id'],
                        'name' => Company::$names[$car['company_id']]
                    ];
                    $companySet = true;
                }
            }

            if (!empty($_GET['brand_id']) && !empty($_GET['model_id']) && empty($_GET['company_id'])) {
                $companySet = $brandSet = $modelSet = false;
                if ($car['brand_id'] == $_GET['brand_id'] && $car['model_id'] == $_GET['model_id']) {
                    $companies[$car['company_id']] = [
                        'id' => $car['company_id'],
                        'name' => Company::$names[$car['company_id']]
                    ];
                    $companySet = true;
                }
                if ($car['model_id'] == $_GET['model_id']) {
                    $brands[$car['brand_id']] = [
                        'id' => $car['brand_id'],
                        'name' => Brand::$names[$car['brand_id']]
                    ];
                    $brandSet = true;
                }
                if ($car['brand_id'] == $_GET['brand_id']) {
                    $models[$car['model_id']] = [
                        'id' => $car['model_id'],
                        'name' => BrandModel::$names[$car['model_id']]
                    ];
                    $modelSet = true;
                }
            }

            if (!empty($_GET['company_id']) && !empty($_GET['brand_id']) && !empty($_GET['model_id'])) {
                $brandSet = $modelSet = $companySet = false;
                if ($car['company_id'] == $_GET['company_id'] && $car['brand_id'] == $_GET['brand_id']) {
                    $models[$car['model_id']] = [
                        'id' => $car['model_id'],
                        'name' => BrandModel::$names[$car['model_id']]
                    ];
                    $modelSet = true;
                }
                if ($car['company_id'] == $_GET['company_id'] && $car['model_id'] == $_GET['model_id']) {
                    $brands[$car['brand_id']] = [
                        'id' => $car['brand_id'],
                        'name' => Brand::$names[$car['brand_id']]
                    ];
                    $brandSet = true;
                }
                if ($car['brand_id'] == $_GET['brand_id'] && $car['model_id'] == $_GET['model_id']) {
                    $companies[$car['company_id']] = [
                        'id' => $car['company_id'],
                        'name' => Company::$names[$car['company_id']]
                    ];
                    $companySet = true;
                }
            }

            if ($companySet) {
                $companies[$car['company_id']] = [
                    'id' => $car['company_id'],
                    'name' => Company::$names[$car['company_id']]
                ];
            }
            if ($brandSet) {
                $brands[$car['brand_id']] = [
                    'id' => $car['brand_id'],
                    'name' => Brand::$names[$car['brand_id']]
                ];
            }
            if ($modelSet) {
                $models[$car['model_id']] = [
                    'id' => $car['model_id'],
                    'name' => BrandModel::$names[$car['model_id']]
                ];
            }
            if (!empty($_GET['company_id'])) {
                if ($_GET['company_id'] != $car['company_id']) {
                    ArrayHelper::remove($car, $key);
                    continue;
                }
            }
            if (!empty($_GET['brand_id'])) {
                if ($_GET['brand_id'] != $car['brand_id']) {
                    ArrayHelper::remove($car, $key);
                    continue;
                }
            }
            if (!empty($_GET['model_id'])) {
                if ($_GET['model_id'] != $car['model_id']) {
                    ArrayHelper::remove($car, $key);
                    continue;
                }
            }
            $carsFiltered[] = [
                'id' => $car['id'],
                'plate_number' => $car['plate_number'],
                'company' => Company::$names[$car['company_id']],
                'brand' => Brand::$names[$car['brand_id']],
                'model' => BrandModel::$names[$car['model_id']],
                'vin' => $car['vin'],
                'fabrication_year' => $car['fabrication_year'],
                'fuel' => Fuel::$names[$car['fuel_id']],
                'color' => $car['color'],
            ];
        }

        $companies = array_merge([[
            'id' => '0',
            'name' => Yii::t('app', 'All companies')
        ]], array_values($companies));

        $brands = array_merge([[
            'id' => '0',
            'name' => Yii::t('app', 'All brands')
        ]], array_values($brands));

        $models = array_merge([[
            'id' => '0',
            'name' => Yii::t('app', 'All models')
        ]], array_values($models));

        return [
            "companies" => $companies,
            "brands" => $brands,
            "models" => $models,
            "cars" => $carsFiltered
        ];
    }

    /**
     * @param $id
     * @return array|array[]
     * @throws HttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     * @deprecated To be removed when switching app to F7
     */
    public function actionDetails($id)
    {
        $token = Yii::$app->request->get('token');
        if (empty($token)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing token');
            return $this->return;
        }
        $uuid = Yii::$app->request->get('uuid');
        if (empty($uuid)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing uuid');
            return $this->return;
        }
        $user = User::find()->where('auth_key = :auth_key', [':auth_key' => $token])->one();
        if (empty($user)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }
        $device = Device::find()->where('uuid = :uuid', [':uuid' => $uuid])->one();
        if (empty($device)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }

        $model = Car::find()->where("id = :id", [':id' => $id])->with('company', 'carDetail', 'brand', 'brandModel', 'fuel', 'carZone', 'carZone.zone', 'carZone.zoneOption')->one();
        if (empty($model)) {
            throw new HttpException(404, Yii::t('app', 'No car available.'));
        }

        if ($model['status'] != 0 && $model['updated_by'] != $user->id) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'This car is unavailable.');
            return $this->return;
        }

        $existCarId = CarZone::find()->where('car_id = :car_id', ['car_id' => $model->id])->one();
        if (empty($existCarId)) {
            foreach (Zone::getZonesIds() as $zonesId) {
                $modelCarZoneNewCar = new CarZone();
                $modelCarZoneNewCar->car_id = $id;
                $modelCarZoneNewCar->zone_id = $zonesId;
                $modelCarZoneNewCar->zone_option_id = ZoneOption::find()->where(['zone_id' => $zonesId, 'value' => 1])->one()->id;
                $modelCarZoneNewCar->added = date('Y-m-d H:i:s');
                $modelCarZoneNewCar->added_by = $user->id;
                $modelCarZoneNewCar->load(Yii::$app->getRequest()->getBodyParams(), '');
                if ($modelCarZoneNewCar->save()) {
                    $response = Yii::$app->getResponse();
                    $response->setStatusCode(200);
                } elseif (!$modelCarZoneNewCar->hasErrors()) {
                    throw new ServerErrorHttpException(Yii::t('app', 'Failed to create the car zone for this car.'));
                } else {
                    throw new ServerErrorHttpException($modelCarZoneNewCar->errors[0][0]);
                }
            }
        }

        Car::setCarStatus(1, $id, $user->id, $user->id);
        $zoneModel = Zone::find()->where("deleted = 0")->with([
            'carZone' => function (ActiveQuery $query) use ($id) {
                $query->andWhere('car_id = :car_id', [':car_id' => $id])->orderBy(['added' => SORT_DESC]);
            }, 'carZone.zoneOption'
        ])->asArray()->all();

        if (!empty($zoneModel)) {
            foreach ($zoneModel as $key => $carZoneOptionApi) {
                $carZones = $carZoneOptionApi['carZone'];
                $details[$carZoneOptionApi['id']] = [
                    'id' => $carZoneOptionApi['id'],
                    'field' => $carZoneOptionApi['field'],
                    'label' => $carZoneOptionApi['label'],
                    'observation' => !empty($carZones['observations']) ? $carZones['observations'] : '',
                    'prev_zone_image' => !empty($carZones['zone_photo']) ? ImageHelper::convertImageFileToBase64($carZones['zone_photo']) : null,
                    'zone_option_id' => !empty($carZones['zone_option_id']) ? $carZones['zone_option_id'] : null,
                    'prev_status' =>
                        [
                            'text' => !empty($carZones['zoneOption']) && !empty($carZones['zoneOption']['text']) ? $carZones['zoneOption']['text'] : '',
                            'badge' => !empty($carZones['zoneOption']) && $carZones['zoneOption']['text'] == 'OK' ? 'success' : 'warning'
                        ],
                    'options' => ZoneOption::getZoneOptionValues($carZones['zoneOption']['zone_id'], $carZones['zoneOption']['text']),
                ];
            }
        }

        $car = [
            'id' => $model['id'],
            'plate_number' => $model['plate_number'],
            'company' => $model['company']['name'],
            'brand' => $model['brand']['name'],
            'model' => $model['brandModel']['name'],
            'vin' => $model['vin'],
            'fabrication_year' => $model['fabrication_year'],
            'fuel' => $model['fuel']['name'],
            'color' => $model['color'],
            'details' => $details
        ];

        return [
            "car" => $car
        ];
    }

    /**
     * @param $id
     * @return array|array[]
     * @throws HttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDetailsNew($id)
    {
        $token = Yii::$app->request->get('token');
        if (empty($token)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing token');
            return $this->return;
        }
        $uuid = Yii::$app->request->get('uuid');
        if (empty($uuid)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing uuid');
            return $this->return;
        }
        $user = User::find()->where('auth_key = :auth_key', [':auth_key' => $token])->one();
        if (empty($user)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }
        $device = Device::find()->where('uuid = :uuid', [':uuid' => $uuid])->one();
        if (empty($device)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }

        $model = Car::find()
            ->where("id = :id", [':id' => $id])
            ->with('company', 'carDetail', 'brand', 'brandModel', 'fuel', 'carZone', 'carZone.zone', 'carZone.zoneOption')
            ->one();

        if (empty($model)) {
            throw new HttpException(404, Yii::t('app', 'No car available.'));
        }

        if ($model['status'] != 0 && $model['updated_by'] != $user->id) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'This car is unavailable.');
            return $this->return;
        }

        $existCarId = CarZone::find()->where('car_id = :car_id', ['car_id' => $model->id])->one();
        if (empty($existCarId)) {
            foreach (Zone::getZonesIds() as $zonesId) {
                $modelCarZoneNewCar = new CarZone();
                $modelCarZoneNewCar->car_id = $id;
                $modelCarZoneNewCar->zone_id = $zonesId;
                $modelCarZoneNewCar->zone_option_id = ZoneOption::find()->where(['zone_id' => $zonesId, 'value' => 1])->one()->id;
                $modelCarZoneNewCar->added = date('Y-m-d H:i:s');
                $modelCarZoneNewCar->added_by = $user->id;
                $modelCarZoneNewCar->load(Yii::$app->getRequest()->getBodyParams(), '');
                if (!$modelCarZoneNewCar->save()) {
                    if (!$modelCarZoneNewCar->hasErrors()) {
                        throw new ServerErrorHttpException(Yii::t('app', 'Failed to create the car zone for this car.'));
                    } else {
                        throw new ServerErrorHttpException($modelCarZoneNewCar->errors[0][0]);
                    }
                }
            }
        }

        Car::setCarStatus(1, $id, $user->id, $user->id);
        $zoneModel = Zone::find()->where("deleted = 0")->with([
            'carZone' => function (ActiveQuery $query) use ($id) {
                $query->andWhere('car_id = :car_id', [':car_id' => $id])->orderBy(['added' => SORT_DESC]);
            }, 'carZone.zoneOption'
        ])->asArray()->all();

        if (!empty($zoneModel)) {
            foreach ($zoneModel as $key => $carZoneOptionApi) {
                $carZones = $carZoneOptionApi['carZone'];
                $this->return['zonesDetails'][] = [
                    'id' => $carZoneOptionApi['id'],
                    'field' => $carZoneOptionApi['field'],
                    'label' => $carZoneOptionApi['label'],
                    'observation' => !empty($carZones['observations']) ? $carZones['observations'] : '',
                    'prev_zone_image' => !empty($carZones['zone_photo']) ? ImageHelper::convertImageFileToBase64($carZones['zone_photo']) : null,
                    'zone_option_id' => !empty($carZones['zone_option_id']) ? $carZones['zone_option_id'] : null,
                    'prev_status' =>
                        [
                            'text' => !empty($carZones['zoneOption']) && !empty($carZones['zoneOption']['text']) ? $carZones['zoneOption']['text'] : '',
                            'badge' => !empty($carZones['zoneOption']) && $carZones['zoneOption']['text'] == 'OK' ? 'green' : 'red'
                        ],
                    'options' => ZoneOption::getZoneOptionValues($carZones['zoneOption']['zone_id'], $carZones['zoneOption']['text']),
                ];
            }
        }
        $this->return['car'] = [
            'id' => $model['id'],
            'plate_number' => $model['plate_number'],
            'company' => $model['company']['name'],
            'brand' => $model['brand']['name'],
            'model' => $model['brandModel']['name'],
            'vin' => $model['vin'],
            'fabrication_year' => $model['fabrication_year'],
            'fuel' => $model['fuel']['name'],
            'color' => $model['color'],
        ];
        $this->return['message'] = Yii::t('app', 'Successfully taken car data');
        return $this->return;
    }

    /**
     * @param $id
     * @return array|array[]
     * @throws HttpException
     */
    public function actionDocuments($id)
    {
        $model = Car::find()->where("id = :id", [':id' => $id])->with('company', 'carDetail', 'carDocuments', 'brand', 'fuel')->one();
        if (empty($model)) {
            throw new HttpException(404, Yii::t('app', 'No car available.'));
        }

        $token = Yii::$app->request->get('token');
        if (empty($token)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing token');
            return $this->return;
        }
        $uuid = Yii::$app->request->get('uuid');
        if (empty($uuid)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing uuid');
            return $this->return;
        }
        $user = User::find()->where('auth_key = :auth_key', [':auth_key' => $token])->one();
        if (empty($user)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }
        $device = Device::find()->where('uuid = :uuid', [':uuid' => $uuid])->one();
        if (empty($device)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }

        $carDocStatus = $model->carDocuments;
        $car = [
            'id' => $model['id'],
            'plate_number' => $model['plate_number'],
            'company' => $model['company']['name'],
            'brand' => $model['brand']['name'],
            'model' => $model['brandModel']['name'],
            'vin' => $model['vin'],
            'fabrication_year' => $model['fabrication_year'],
            'fuel' => $model['fuel']['name'],
            'color' => $model['color'],
            'documents' =>
                [
                    [
                        'field' => 'rca',
                        'label' => 'RCA',
                        'status' => CarDocument::getCarDocumentStatus($carDocStatus->rca_valid_until),
                    ],
                    [
                        'field' => 'casco',
                        'label' => 'CASCO',
                        'status' => CarDocument::getCarDocumentStatus($carDocStatus->casco_valid_until),
                    ],
                    [
                        'field' => 'itp',
                        'label' => 'ITP',
                        'status' => CarDocument::getCarDocumentStatus($carDocStatus->itp_valid_until),
                    ],
                    [
                        'field' => 'vignette',
                        'label' => 'ROVINIETĂ',
                        'status' => CarDocument::getCarDocumentStatus($carDocStatus->vignette_valid_until),
                    ]
                ]
        ];

        return [
            "car" => $car
        ];
    }

    /**
     * @return array
     * @throws ServerErrorHttpException
     */
    public function actionPreviewPv()
    {
        $post = Yii::$app->request->post();

        try {
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
            $user = User::find()->where('id = :user_id', [':user_id' => $post['user_id']])->one();
            if (empty($user)) {
                Yii::$app->response->statusCode = 401;
                $this->return['status'] = 401;
                $this->return['message'] = Yii::t('app', 'Bad request');
                return $this->return;
            }
            if (empty($user['auth_key']) || $user['auth_key'] !== $post['token']) {
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

            $car = Car::find()->where('id = :carId', [':carId' => $post['car_id']])->one();
            if (empty($car)) {
                Yii::$app->response->statusCode = 400;
                $this->return['status'] = 400;
                $this->return['message'] = Yii::t('app', 'No car id received');
            }

            $carId = $car->id;

            if (empty($post['handing_car'])) {
                Yii::$app->response->statusCode = 400;
                $this->return['status'] = 400;
                $this->return['message'] = Yii::t('app', 'No operation type received. Please contact an administrator!');
                return $this->return;
            }
            $handingCar = $post['handing_car'];
            if (!in_array($handingCar, ['check_in', 'check_out'])) {
                Yii::$app->response->statusCode = 400;
                $this->return['status'] = 400;
                $this->return['message'] = Yii::t('app', 'Received operation type is not correct. Please contact an administrator!');
                return $this->return;
            }

            $model = $this->findModel($carId);
            $zoneModels = Zone::find()->where("deleted = 0")->with([
                'carZone' => function (ActiveQuery $query) use ($carId) {
                    $query->andWhere('car_id = :car_id', [':car_id' => $carId])->orderBy(['added' => SORT_DESC]);
                }, 'carZone.zoneOption'
            ])->asArray()->all();
            $adjustedZoneModels = $post['car_zone'];


            if (empty($zoneModels)) {
                throw new NotFoundHttpException(Yii::t('app', "The car for witch the protocol is generated could not be found. Please contact an administrator!"));
            }

            // accessories for car
            if (!empty($model)) {
                $accessories = CarAccessory::find()->
                select("car_accessory.accessory_id as id, accessory.name as name, car_accessory.accessory_qty as count, car_accessory.observation as observations")->
                join('LEFT JOIN',
                    'accessory',
                    'accessory.id = car_accessory.accessory_id')->
                where("car_accessory.deleted = 0 AND car_id = {$model->id}")->asArray()->all();
            }

            $updatedAccessories = $post['accessories'];
            $date = explode(' ', $model['updated'])[0];
            $html = $this->renderPartial('preview-pv-pdf', [
                'model' => $model,
                'user' => $user,
                'zoneModel' => $zoneModels,
                'adjustedZoneModels' => $adjustedZoneModels,
                'handingCar' => $handingCar,
                'date' => $date,
                'postZoneOption' => !empty($post['car_zone']) ? $post['car_zone'] : [],
                'accessories' => $accessories,
                'updatedAccessories' => $updatedAccessories
            ]);
        } catch (Exception $exc) {
            throw new ServerErrorHttpException(Yii::t('app', 'Bad request') . ' ' . $exc->getMessage());
        }
        return mb_convert_encoding($html, 'UTF-8', 'UTF-8');
    }

    /**
     * @return array|void
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdateCarStatus()
    {
        $post = Yii::$app->request->post();

        if (empty($post['handing_car'])) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'No operation type received. Please contact an administrator!');
            return $this->return;
        }
        $handingCar = $post['handing_car'];
        if (!in_array($handingCar, ['check_in', 'check_out'])) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Received operation type is not correct. Please contact an administrator!');
            return $this->return;
        }

        if (empty($post['car_id'])) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing car details');
            return $this->return;
        }
        $carId = $post['car_id'];

        if (empty($post['signature'])) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'No signature received. If you are sure that you signed, please contact an administrator!');
            return $this->return;
        }
        $signature = $post['signature'];

        if (empty($post['token'])) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing token');
            return $this->return;
        }
        $token = $post['token'];

        if (empty($post['uuid'])) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing uuid');
            return $this->return;
        }
        $uuid = $post['uuid'];

        $user = User::find()->where('auth_key = :auth_key', [':auth_key' => $token])->one();
        if (empty($user)) {
            Yii::$app->response->statusCode = 401;
            $this->return['status'] = 401;
            $this->return['message'] = Yii::t('app', 'Unauthorized');
            return $this->return;
        }

        $device = Device::find()->where('uuid = :uuid', [':uuid' => $uuid])->one();
        if (empty($device)) {
            Yii::$app->response->statusCode = 401;
            $this->return['status'] = 401;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }

        $car = Car::find()->where('id = :car_id AND deleted = 0', [':car_id' => $carId])->one();
        if (empty($car)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'No data found for requested car. Please contact an administrator!');
            return $this->return;
        }

        $signatureDir = Yii::getAlias('@backend/web/images/signatures');
        try {
            if (!is_dir($signatureDir)) {
                FileHelper::createDirectory($signatureDir);
            }
            if (preg_match('/^data:image\/(\w+);base64,/', $signature, $type)) {
                $image = substr($signature, strpos($signature, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif
                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    Yii::$app->response->statusCode = 400;
                    $this->return['status'] = 400;
                    $this->return['message'] = Yii::t('app', 'Invalid signature format!');
                    return $this->return;
                }
                $signaturePath = $signatureDir . "/41_" . uniqid() . ".{$type}";
                $image = str_replace(' ', '+', $image);
                $image = base64_decode($image);
                if ($image === false) {
                    Yii::$app->response->statusCode = 400;
                    $this->return['status'] = 400;
                    $this->return['message'] = Yii::t('app', 'Could not decode the signature. Please contact an administrator!');
                    return $this->return;
                }
            } else {
                Yii::$app->response->statusCode = 400;
                $this->return['status'] = 400;
                $this->return['message'] = Yii::t('app', 'Did not match data URI with image data. Please contact an administrator!');
                return $this->return;
            }
            if (!file_put_contents($signaturePath, $image)) {
                Yii::$app->response->statusCode = 500;
                $this->return['status'] = 500;
                $this->return['message'] = Yii::t('app', 'The signature could not be saved. Please contact an administrator!');
                return $this->return;
            }
        } catch (\Exception $exc) {
            $msg = "Error received while saving signature: {$exc->getMessage()} \n";
            $msg .= "Please contact an administrator!";
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = $msg;
            return $this->return;
        }

        if (empty($post['car_zone'])) {
            $transaction = Yii::$app->ecf_auto_db->beginTransaction();
            try {
                $carUser = $handingCar === 'check_in' ? $user->id : null;

                //save car status to operation_car
                $carOperation = new CarOperation();
                $carOperation->user_id = $user->id;
                $carOperation->car_id = $car->id;
                $carOperation->operation_type_id = $handingCar === 'check_in' ? 1 : 2;
                $carOperation->added = date('Y-m-d H:i:s');
                $carOperation->added_by = $user->id;
                if (!$carOperation->save()) {
                    if ($carOperation->hasErrors()) {
                        foreach ($carOperation->errors as $error) {
                            throw new HttpException(409, $error[0]);
                        }
                    }
                    throw new HttpException(500, Yii::t('app', 'Failed to save car operation. Please contact an administrator!'));
                }

                $msgForEmail = $this->generatePv($car->id, $signature, $handingCar);
                Car::setCarStatus(0, $car->id, $user->id, $carUser);
                $transaction->commit();
            } catch (HttpException $exc) {
                $transaction->rollBack();
                Yii::$app->response->statusCode = $exc->statusCode;
                $this->return['status'] = $exc->statusCode;
                $this->return['message'] = Yii::t('app', $exc->getMessage());
                return $this->return;
            }
            $msg = $handingCar === 'check_in' ?
                Yii::t('app', 'Car successfully taken.') . ' ' .
                Yii::t('app', 'No changes zone option for this car.') :
                Yii::t('app', 'Car successfully handed over.') . ' ' .
                Yii::t('app', 'No changes zone option for this car.');
            Yii::$app->response->statusCode = 200;
            $this->return['status'] = 200;
            $this->return['message'] = $msg . ' ' . $msgForEmail;
            return $this->return;
        }

        $transaction = Yii::$app->ecf_auto_db->beginTransaction();
        try {
            foreach ($post['car_zone'] as $param) {
                $zoneId = $param['zone_id'];
                $carZoneModels = CarZone::find()->where('car_id = :car_id', [':car_id' => $car->id])->andWhere(['zone_id' => $zoneId])->one();
                $_zonePhoto = null;
                if (!empty($param['zone_photo'])) {
                    $carZonePhotoDir = Yii::getAlias("@backend/upload/car-zone-photo/{$car->id}");
                    $photoDate = date('YmdHis');
                    $fileName = "{$zoneId}_{$carZoneModels->zone_option_id}_{$photoDate}";
                    $_zonePhoto = ImageHelper::saveBase64ToImageFile($param['zone_photo'], $fileName, $carZonePhotoDir);
                }

                $carZoneModels->zone_id = $zoneId;
                if (!empty($param['zone_option_id'])) {
                    $carZoneModels->zone_option_id = $param['zone_option_id'];
                }
                $carZoneModels->observations = !empty($param['observations']) ? $param['observations'] : null;
                $carZoneModels->zone_photo = $_zonePhoto;
                $carZoneModels->updated = date('Y-m-d H:i:s');
                $carZoneModels->updated_by = $user->id;

                if (!$carZoneModels->save()) {
                    if ($carZoneModels->hasErrors()) {
                        foreach ($carZoneModels->errors as $error) {
                            throw new HttpException(409, $error[0]);
                        }
                    }
                    throw new HttpException(500, Yii::t('app', 'Failed to update car status'));
                }
            }

            //save car status to operation_car
            $carOperation = new CarOperation();
            $carOperation->user_id = $user->id;
            $carOperation->car_id = $car->id;
            $carOperation->operation_type_id = $handingCar === 'check_in' ? 1 : 2;
            $carOperation->added = date('Y-m-d H:i:s');
            $carOperation->added_by = $user->id;
            if (!$carOperation->save()) {
                if ($carOperation->hasErrors()) {
                    foreach ($carOperation->errors as $error) {
                        throw new HttpException(409, $error[0]);
                    }
                }
                throw new HttpException(500, Yii::t('app', 'Failed to save car operation. Please contact an administrator!'));
            }


            $msgForEmail = $this->generatePv($car->id, $signature, $handingCar);
            $carUser = $handingCar === 'check_in' ? $user->id : null;
            Car::setCarStatus(0, $car->id, $user->id, $carUser);

            $transaction->commit();
            $msg = $handingCar === 'check_in' ?
                Yii::t('app', 'Car successfully taken.') :
                Yii::t('app', 'Car successfully handed over.');
            $this->return['status'] = 200;
            $this->return['message'] = $msg . ' ' . $msgForEmail;
            return $this->return;
        } catch (HttpException $exc) {
            $transaction->rollBack();
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['status'] = $exc->statusCode;
            $this->return['message'] = Yii::t('app', $exc->getMessage());
            return $this->return;
        } catch (Exception $exc) {
            $transaction->rollBack();
            Yii::$app->response->statusCode = $exc->getCode();
            $this->return['status'] = $exc->getCode();
            $this->return['message'] = Yii::t('app', $exc->getMessage());
            return $this->return;
        }
    }

    /**
     * @param $id
     * @return array
     */
    public function actionUnlock($id)
    {
        $userId = Yii::$app->request->get('user_id');
        $userOperation = Yii::$app->request->get('operation');
        $carUserId = $userOperation === 'check_out' ? $userId : null;
        if (!empty($id))
            try {
                Car::setCarStatus(0, $id, $userId, $carUserId);
            } catch (HttpException $exc) {
                Yii::$app->response->statusCode = $exc->statusCode;
                $this->return['status'] = $exc->statusCode;
                $this->return['message'] = $exc->getMessage();
                return $this->return;
            }
        return $this->return;
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionUploadPhotoCarZone()
    {
        $params = Yii::$app->getRequest()->getBodyParams();

        if (empty($params['car_id'])) {
            Yii::$app->response->statusCode = 404;
            $this->return['status'] = 404;
            $this->return['message'] = Yii::t('app', 'No car available.');
            return $this->return;
        }

        if (empty($params['zone_photo'])) {
            Yii::$app->response->statusCode = 200;
            $this->return['status'] = 200;
            $this->return['message'] = Yii::t('app', 'No photo zone option for this car.');
            return $this->return;
        }

        $carZonePhotoDir = Yii::getAlias('@backend/upload/car-zone-photo');
        try {
            if (!is_dir($carZonePhotoDir)) {
                FileHelper::createDirectory($carZonePhotoDir);
            }
            foreach ($params['zone_photo'] as $key => $param) {
                $carZoneImage = $param['photo'];
                if (preg_match('/^data:image\/(\w+);base64,/', $carZoneImage, $type)) {
                    $image = substr($carZoneImage, strpos($carZoneImage, ',') + 1);
                    $type = strtolower($type[1]); // jpg, png, gif

                    if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                        throw new \Exception("invalid image type for car zone");
                    }
                    $image = str_replace(' ', '+', $image);
                    $image = base64_decode($image);

                    if ($image === false) {
                        throw new \Exception('base64_decode failed');
                    }
                } else {
                    throw new \Exception('did not match data URI with image data');
                }
                file_put_contents($carZonePhotoDir . "/41_" . uniqid() . ".{$type}", $image);

            }
        } catch (\Exception $exc) {
            $msg = "Error received while saving car zone photo: {$exc->getMessage()} \n";
            $msg .= "Please contact an administrator!";
            throw new \yii\db\Exception(Yii::t('app', $msg));
        }
        $this->return['message'] = Yii::t('app', 'Zone photo successfully added');
        return $this->return;
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionUploadPersonalDigitalSignature()
    {


        //save personal digital signature
//        $personalDocument = new PersonalDocument();
//        $personalDocument->user_id = 41;
//        $personalDocument->car_id = 1;
//        $personalDocument->name = 'Semnatura digitala';
//        $personalDocument->image_name = str_replace(" ", "_", 'Semnatura digitala') . "_" . 41 . "_" . uniqid();
//        $personalDocument->type = 2;
//        $personalDocument->added = date('Y-m-d H:i:s');
//
//        $personalDocument->added_by = 41;

        $params = Yii::$app->getRequest()->getBodyParams();


        if (empty($params['car_id'])) {
            Yii::$app->response->statusCode = 404;
            $this->return['status'] = 404;
            $this->return['message'] = Yii::t('app', 'No car available.');
            return $this->return;
        }

        $signatureImage = $params['signature'];

        $signatureDir = Yii::getAlias('@backend/upload/signatures');

        try {
            if (!is_dir($signatureDir)) {
                FileHelper::createDirectory($signatureDir);
            }
            if (preg_match('/^data:image\/(\w+);base64,/', $signatureImage, $type)) {
                $image = substr($signatureImage, strpos($signatureImage, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif

                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    throw new \Exception("invalid image type for signature");
                }
                $image = str_replace(' ', '+', $image);
                $image = base64_decode($image);

                if ($image === false) {
                    throw new \Exception('base64_decode failed');
                }
            } else {
                throw new \Exception('did not match data URI with image data');
            }
            file_put_contents($signatureDir . "/41_" . uniqid() . ".{$type}", $image);

            return $this->return;
        } catch (\Exception $exc) {
            $msg = "Error received while saving signature: {$exc->getMessage()} \n";
            $msg .= "Please contact an administrator!";
            throw new \yii\db\Exception(Yii::t('app', $msg));
        }
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionGeneratePdf()
    {


        //save personal digital signature
//        $personalDocument = new PersonalDocument();
//        $personalDocument->user_id = 41;
//        $personalDocument->car_id = 1;
//        $personalDocument->name = 'Semnatura digitala';
//        $personalDocument->image_name = str_replace(" ", "_", 'Semnatura digitala') . "_" . 41 . "_" . uniqid();
//        $personalDocument->type = 2;
//        $personalDocument->added = date('Y-m-d H:i:s');
//
//        $personalDocument->added_by = 41;

        $params = Yii::$app->getRequest()->getBodyParams();

        $html = $params['html'];
        $data = base64_decode(preg_replace('/\s\s+/', '', $html));

        $pdfDir = Yii::getAlias('@backend/upload/pdf');

        try {
            if (!is_dir($pdfDir)) {
                FileHelper::createDirectory($pdfDir);
            }
            $image = base64_decode($html);


//                $image = substr($html, strpos($html, ',') + 1);
            $type = strtolower('pdf'); // jpg, png, gif

//                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
//                    throw new \Exception("invalid image type for signature");
//                }
//                $image = str_replace(' ', '+', $image);
//                $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('base64_decode failed');
            }
            file_put_contents($pdfDir . "/41_" . uniqid() . ".{$type}", $image);

            return $this->return;
        } catch (\Exception $exc) {
            $msg = Yii::t('app', "Error received while saving signature:") . "{$exc->getMessage()} \n";
            $msg .= Yii:: t('app', "Please contact an administrator!");
            throw new \yii\db\Exception(Yii::t('app', $msg));
        }
    }

    /**
     * @param $carId
     * @param $sign
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function generatePv($carId, $sign, $handingCar)
    {
        User::setUsers();
        $model = Car::find()->where(['id' => $carId])->with('company', 'carDetail', 'carDocuments', 'brand', 'brandModel', 'carZone')->one();
        if (empty($model)) {
            throw new NotFoundHttpException(Yii::t('app', "The car for witch the protocol is generated could not be found. Please contact an administrator!"));
        }

        $zoneModels = Zone::find()->where("deleted = 0")->with([
            'carZone' => function (ActiveQuery $query) use ($carId) {
                $query->andWhere('car_id = :car_id', [':car_id' => $carId])->orderBy(['added' => SORT_DESC]);
            }, 'carZone.zoneOption'
        ])->asArray()->all();

        if (empty($zoneModels)) {
            throw new NotFoundHttpException(Yii::t('app', "The car for witch the protocol is generated could not be found. Please contact an administrator!"));
        }
        try {
            $pvUploadPath = Yii::getAlias('@backend/upload/pv-pdf/');
            if (!is_dir($pvUploadPath)) {
                FileHelper::createDirectory($pvUploadPath);
            }
            $pvFileName = date('YmdHis') . '.pdf';

            $companyCarId = $model['company_id'];
            $stylePdf = '';
            if (!empty($companyCarId)) {
                switch ($companyCarId) {
                    case 1:
                        $stylePdf = ['#5F7423']; //color
                        break;
                    case 2:
                        $stylePdf = ['#E093CA'];  //color
                        break;
                    default:
                        $stylePdf = ['black']; //color
                        break;
                }
            }
            // accessories for car
            $accessories = CarAccessory::find()->
            select("car_accessory.accessory_id as id, accessory.name as name, car_accessory.accessory_qty as count, car_accessory.observation as observations")->
            join('LEFT JOIN',
                'accessory',
                'accessory.id = car_accessory.accessory_id')->
            where("car_accessory.deleted = 0 AND car_id = {$model->id}")->asArray()->all();
            $backgroundImage = Yii::getAlias("@web/images/$companyCarId-pdf-img.png");
            $date = date("d-m-Y", strtotime(explode(' ', $model['updated'])[0]));
            $mpdf = new Mpdf(['tempDir' => Yii::getAlias('@backend/runtime')]);
            $mpdf->WriteHTML($this->renderPartial('pv-pdf', [
                'model' => $model,
                'zoneModel' => $zoneModels,
                'sign' => $sign,
                'handingCar' => $handingCar,
                'backgroundImage' => $backgroundImage,
                'date' => $date,
                'stylePdf' => $stylePdf,
                'accessories' => $accessories
            ]));
            $mpdf->Output($pvUploadPath . $pvFileName, Destination::FILE);
            $statusMessage = $handingCar === 'check_out' ? Yii::t('app', "handing over") : Yii::t('app', "picked up");
            $_statusMessage = $handingCar === 'check_out' ? Yii::t('app', "handing_over") : Yii::t('app', "picked_up");
            $userName = User::$users[$model['updated_by']]['first_name'] . ' ' . User::$users[$model['updated_by']]['last_name'];
            $sendEmail = new SendSharePointMailHelper();
            $sendEmail->subject = Yii::t('app', "Report of {statusMessage} the car ", ['statusMessage' => $_statusMessage]) . $model['plate_number'];
            $sendEmail->content = [
                "contentType" => "html",
                "content" => "<body>Bună $userName, <br>Ai $statusMessage  cu succes autoturismul {$model['brand']['name']} , {$model['brandModel']['name']}, {$model['plate_number']}.<br>Acest e-mail conține procesul verbal de predare - primire.<br>Mulțumim, o zi bună.</body>",
            ];

            $sendEmail->toRecipients = [
                [
                    "emailAddress" => [
                        "name" => $userName,
                        "address" => User::$users[$model['updated_by']]['email']
                    ]
                ]
            ];

            $sendEmail->ccRecipients = [
                [
                    "emailAddress" => [
                        "name" => "Daniel Lascar",
                        "address" => "daniel.lascar@leviatan.ro"
                    ]
                ],
                [
                    "emailAddress" => [
                        "name" => "Cornel Enache",
                        "address" => "cornel.enache@leviatan.ro"
                    ]
                ],
                [
                    "emailAddress" => [
                        "name" => "Marius Postolache",
                        "address" => "marius.postolache@leviatan.ro"
                    ]
                ]
            ];

            $sendEmail->attachments = [
                [
                    "@odata.type" => "#microsoft.graph.fileAttachment",
                    "name" => $pvFileName,
                    "contentType" => BaseFileHelper::getMimeType($pvUploadPath . $pvFileName),
                    "contentBytes" => chunk_split(base64_encode(file_get_contents($pvUploadPath . $pvFileName))),
                ],
            ];
            $msg = $sendEmail->sendEmail();

        } catch (MpdfException | Exception $exc) {
            throw new ServerErrorHttpException(Yii::t('app', "No valid response received from server. Please contact an administrator!") . ' ' . "{$exc->getMessage()}!");
        }
        return $msg;
    }

    /**
     * @return array
     * @throws ServerErrorHttpException
     */
    public function actionJourneys()
    {
        $get = Yii::$app->request->get();


        $interest = [1 => 'Activități administrative', 2 => 'Activități curente', 3 => 'Sibiu'];
        $status = [1 => 'valid', 2 => 'invalid'];

        $journeysScope = [
            [
                "id" => "1",
                "status" => $status[rand(1, 2)],
                "interest" => $interest[rand(1, 3)],
                "start" => [
                    "start_date" => "10-04-2022",
                    "start_hour" => "10:22",
                    "start_location" => "Calatorie 1"
                ],
                "end" => [
                    "end_date" => "10-04-2022",
                    "end_hour" => "10:33",
                    "end_location" => "Kaufland"
                ]
            ],
            [
                "id" => "2",
                "status" => $status[rand(1, 2)],
                "interest" => $interest[rand(1, 3)],
                "start" => [
                    "start_date" => "10-04-2022",
                    "start_hour" => "10:22",
                    "start_location" => "Calatorie 2"
                ],
                "end" => [
                    "end_date" => "10-04-2022",
                    "end_hour" => "10:33",
                    "end_location" => "Kaufland"
                ]
            ],
            [
                "id" => "3",
                "status" => $status[rand(1, 2)],
                "interest" => $interest[rand(1, 3)],
                "start" => [
                    "start_date" => "10-04-2022",
                    "start_hour" => "10:22",
                    "start_location" => "Lidl Obcini"
                ],
                "end" => [
                    "end_date" => "10-04-2022",
                    "end_hour" => "10:33",
                    "end_location" => "Kaufland"
                ]
            ],
            [
                "id" => "4",
                "status" => $status[rand(1, 2)],
                "interest" => $interest[rand(1, 3)],
                "start" => [
                    "start_date" => "10-04-2022",
                    "start_hour" => "10:22",
                    "start_location" => "Econfaire"
                ],
                "end" => [
                    "end_date" => "10-04-2022",
                    "end_hour" => "10:33",
                    "end_location" => "Lidl"
                ]
            ],
            [
                "id" => "5",
                "status" => $status[rand(1, 2)],
                "interest" => $interest[rand(1, 3)],
                "start" => [
                    "start_date" => "10-04-2022",
                    "start_hour" => "10:33",
                    "start_location" => "Ubitech"
                ],
                "end" => [
                    "end_date" => "10-04-2022",
                    "end_hour" => "10:44",
                    "end_location" => "Kaufland"
                ]
            ],
        ];


        try {
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
            $user = User::find()->where('id = :user_id', [':user_id' => $get['user_id']])->one();
            if (empty($user)) {
                Yii::$app->response->statusCode = 401;
                $this->return['status'] = 401;
                $this->return['message'] = Yii::t('app', 'Bad request');
                return $this->return;
            }
            if (empty($user['auth_key']) || $user['auth_key'] !== $get['token']) {
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

        } catch (Exception $exc) {
            throw new ServerErrorHttpException(Yii::t('app', 'Bad request') . ' ' . $exc->getMessage());
        }

        $this->return['status'] = 200;
        $this->return['message'] = $journeysScope;
        return $this->return;
    }

    public function actionJourneysNew($user_id)
    {
        $token = Yii::$app->request->get('token');
        if (empty($token)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing token');
            return $this->return;
        }
        $uuid = Yii::$app->request->get('uuid');
        if (empty($uuid)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing uuid');
            return $this->return;
        }
        $user = User::find()->where('auth_key = :auth_key', [':auth_key' => $token])->one();
        if (empty($user)) {
            Yii::$app->response->statusCode = 401;
            $this->return['status'] = 401;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }
        $device = Device::find()->where('uuid = :uuid', [':uuid' => $uuid])->one();
        if (empty($device)) {
            Yii::$app->response->statusCode = 401;
            $this->return['status'] = 401;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }

        $models = Journey::find()->where(['user_id' => $user_id])->all();

        if (empty($models)) {
            Yii::$app->response->statusCode = 200;
            $this->return['status'] = 200;
            $this->return['message'] = Yii::t('app', 'There are no journeys to validate');
            return $this->return;
        }

        $journey = [];
        foreach ($models as $model) {
            $journey[] = [
                'id' => $model->id,
                'status' => $model->status,
                'interest' => !empty($model->project->full_name) ? $model->project->full_name : null,
                'start' => [
                    'start_date' => explode(' ', $model->started)[0],
                    'start_hour' => explode(' ', $model->started)[1],
                    'start_location' => $model['startHotspot']['name'],
                ],
                'stop' => [
                    'stop_date' => explode(' ', $model->stopped)[0],
                    'stop_hour' => explode(' ', $model->stopped)[1],
                    'stop_location' => $model['stopHotspot']['name'],
                ]
            ];
        }
        return [
            "journeys" => $journey
        ];
    }

    /**
     * Function for api validate-journeys
     * @return array
     * @throws ServerErrorHttpException
     * @author Daniel L.
     * @since 02-05-2022
     */
    public function actionValidateJourneys()
    {
        $post = Yii::$app->request->post();

        try {
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
            if (empty($post['journey_list'])) {
                Yii::$app->response->statusCode = 400;
                $this->return['status'] = 400;
                $this->return['message'] = Yii::t('app', 'Incomplete received data, missing journey list');
                return $this->return;
            }
            if (empty($post['journey_scope'])) {
                Yii::$app->response->statusCode = 400;
                $this->return['status'] = 400;
                $this->return['message'] = Yii::t('app', 'Incomplete received data, missing journey scope');
                return $this->return;
            }
            $user = User::find()->where('id = :user_id', [':user_id' => $post['user_id']])->one();
            if (empty($user)) {
                Yii::$app->response->statusCode = 401;
                $this->return['status'] = 401;
                $this->return['message'] = Yii::t('app', 'Bad request');
                return $this->return;
            }
            if (empty($user['auth_key']) || $user['auth_key'] !== $post['token']) {
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
        } catch (Exception $exc) {
            throw new ServerErrorHttpException(Yii::t('app', 'Bad request') . ' ' . $exc->getMessage());
        }

        $this->return['status'] = 200;
        $this->return['message'] = "OK";
        $this->return['$post'] = $post;
        return $this->return;
    }

    /**
     * @throws ServerErrorHttpException
     */
    public function actionAccessories($car_id)
    {

        $get = Yii::$app->request->get();

        try {
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

            //Check if user has CarAdministrator role or CarsManager role
            $userRoles = Yii::$app->authManager->getRolesByUser($user['id']);
            if (in_array('CarsAdministrator', array_keys($userRoles)) || in_array('CarsManager', array_keys($userRoles))) {
                $this->return['carManager'] = true;
            } else {
                $this->return['carManager'] = false;
            }

            $accessoriesModels = CarAccessory::find()->where('car_id = :car_id', [':car_id' => $car_id])->all();

            if (empty($accessoriesModels)) {
                Yii::$app->response->statusCode = 404;
                $this->return['status'] = 404;
                $this->return['message'] = Yii::t('app', 'No accessory available.');
                return $this->return;
            }
        } catch (Exception $exc) {
            throw new ServerErrorHttpException(Yii::t('app', 'Bad request') . ' ' . $exc->getMessage());
        }

        $accessories = [];
        foreach ($accessoriesModels as $accessory) {
            $accessories[] = [
                'id' => $accessory['id'],
                'name' => $accessory['accessory']['name'],
                'quantity' => $accessory['accessory_qty'],
                'measure_unit' => $accessory['measureUnit']['code'],
                'observation' => $accessory['observation'],
            ];
        }
        $this->return['status'] = 200;
        $this->return['accessories'] = $accessories;
        $message = Yii::t('app', 'Successfully sent the accessories list');
        return $this->prepareResponse($message);
    }

    /**
     * Finds the Car model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Car the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Car::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}