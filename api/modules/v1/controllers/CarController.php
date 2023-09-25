<?php

namespace api\modules\v1\controllers;


use api\models\CarZoneHistory;
use api\models\Company;
use api\modules\v1\models\Car;
use api\modules\v1\models\CarAccessory;
use api\modules\v1\models\CarDocument;
use api\modules\v1\models\CarOperation;
use api\modules\v1\models\CarZone;
use api\modules\v1\models\Employee;
use api\modules\v1\models\EmployeeAutoFleet;
use api\modules\v1\models\Zone;
use api\modules\v1\models\ZoneOption;
use backend\components\ImageHelper;
use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use backend\modules\adm\models\UserSignature;
use backend\modules\auto\models\CarConsumption;
use backend\modules\auto\models\CarDocumentHistory;
use backend\modules\auto\models\CarKm;
use backend\modules\auto\models\CarZonePhoto;
use backend\modules\auto\models\PvRegister;
use backend\modules\crm\models\Brand;
use backend\modules\crm\models\BrandModel;
use common\components\HttpStatus;
use common\components\SendSharePointMailHelper;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Mpdf\Output\Destination;
use Yii;
use yii\base\Exception;
use yii\data\ActiveDataFilter;
use yii\db\ActiveQuery;
use yii\helpers\BaseFileHelper;
use yii\helpers\FileHelper;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

/**
 * V1 of Car controller
 */
class CarController extends RestV1Controller
{
    public $modelClass = 'api\modules\v1\models\Car';
    public $pvFileName = '';

    public function actions()
    {
        $actions = parent::actions();
        return $actions;
    }

    /**
     * @return array|ActiveDataFilter
     * @throws HttpException
     * @throws ServerErrorHttpException
     */
    public function actionAvailableCars()
    {
        $_brandsIds = [];
        $this->return['brands'] = [];
        $this->return['models'] = [];
        $getPreviousCar = Yii::$app->request->get('previous_car');
        if (!empty($getPreviousCar)) {
            Car::setCarStatus(0, $getPreviousCar, Yii::$app->user->id, null);
        }
        Company::setNamesAuto();

        $employee = Employee::find()->where(['user_id' => Yii::$app->user->id, 'status' => 1])->one();
        if (empty($employee)) {
            $this->return['status'] = HttpStatus::UNAUTHORIZED;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', 'You are not authorized to access this page!');
            return $this->return;
        }

        $getFilterParams = Yii::$app->request->get();
        $filter = new ActiveDataFilter([
            'searchModel' => 'api\models\search\CarSearch',
            'attributeMap' => [
                'brand_id' => 'car.brand_id',
                'model_id' => 'car.model_id',
                'deleted' => 'car.deleted',
                'user_id' => 'car.user_id',
            ],
        ]);

        /**
         * check if we have a company set in request and
         * if not get the current user company
         */
        if (empty(Yii::$app->request->get('filter')['company_id'])) {
            $getFilterParams['filter']['company_id'] = $employee->employeeMainCompany->company_id;
        }

        $filterCondition = null;
        if ($filter->load($getFilterParams)) {
            $filterCondition = $filter->build();
            if ($filterCondition === false) {
                // Serializer would get errors out of it
                return $filter;
            }
        }

        $carsQuery = Car::find();
        $carsQuery->select([
            'car.id', 'plate_number', 'vin', 'fabrication_year', 'car.fuel_id', 'color', 'car.company_id', 'car.brand_id', 'car.model_id', 'car.user_id',
            'company.name AS company',
            'fuel.name AS fuel',
            'brand.name AS brand',
            'brand_model.name AS model'
        ]);
        $carsQuery->joinWith(['company', 'fuel', 'brand', 'brandModel'], false);
        $carsQuery->where("car.deleted = 0 AND car.status = 0 AND (car.user_id IS NULL OR car.user_id = '')");
        if ($filterCondition !== null) {
            $carsQuery->andWhere($filterCondition);
        }
        $carList = $carsQuery->asArray()->all();

        /**
         * get available car models based on applied filters
         */
        foreach ($carList as $car) {
            /**
             * the brand list is always available and is based on company filters
             */
            if (!in_array($car['brand_id'], $_brandsIds)) {
                $_brandsIds[] = $car['brand_id'];
            }
        }

        /**
         * add data to brand filters
         */
        if (!empty($_brandsIds)) {
            $carBrandSubQuery = Car::find()
                ->select('brand_id')
                ->distinct()
                ->where("car.deleted = 0 AND car.status = 0 AND (car.user_id IS NULL OR car.user_id = '')");

            if (!empty($filter->filter['company_id'])) {
                $carBrandSubQuery->andWhere("car.company_id = {$filter->filter['company_id']}");
            }

            $brandQuery = Brand::find();
            $brandQuery->select(['id', 'name', "false AS `selected`"]);
            $brandQuery->where(['deleted' => 0, 'id' => $carBrandSubQuery]);
            $brandQuery->orderBy("name ASC");
            $this->return['brands'] = $brandQuery->asArray()->all();
            if (!empty($filter->filter['brand_id'])) {
                foreach ($this->return['brands'] as $key => $brand) {
                    $this->return['brands'][$key]['selected'] = (int)$filter->filter['brand_id'] === (int)$brand['id'];
                }
            }

            /**
             * add data to model filters only if we have a brand set
             */
            if (!empty($filter->filter['brand_id'])) {
                $carModelSubQuery = Car::find()
                    ->select('model_id')
                    ->distinct()
                    ->where("car.deleted = 0 AND car.status = 0 AND (car.user_id IS NULL OR car.user_id = '')")
                    ->andWhere(['brand_id' => $filter->filter['brand_id'], 'company_id' => "{$filter->filter['company_id']}"]);

                $modelQuery = BrandModel::find();
                $modelQuery->select(['id', 'name', "false AS `selected`"]);
                $modelQuery->where(['deleted' => 0]);
                $modelQuery->andWhere(['id' => $carModelSubQuery]);
                $modelQuery->orderBy("name ASC");
                $this->return['models'] = $modelQuery->asArray()->all();
                if (!empty($filter->filter['model_id'])) {
                    foreach ($this->return['models'] as $key => $model) {
                        $this->return['models'][$key]['selected'] = $filter->filter['model_id'] === $model['id'];
                    }
                }
            }
        }
        $companies = EmployeeAutoFleet::find()->where(['deleted' => 0, 'employee_id' => $employee->id])->all();
        if (empty($companies)) {
            $this->return['status'] = HttpStatus::UNAUTHORIZED;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', 'You are not authorized to access this page!');
            return $this->return;
        }

        $company = [];
        foreach ($companies as $cmp) {
            $company[] = [
                'id' => $cmp->company_id,
                'name' => !empty(Company::$auto[$cmp->company_id]) ? Company::$auto[$cmp->company_id] : '-'
            ];
        }
        $this->return['companies'] = $company;
        $this->return['cars'] = $carList;
        return $this->return;
    }

    /**
     * @param $id
     * @return array
     * @throws HttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDetails($id)
    {

        $model = Car::find()
            ->where("id = :id", [':id' => $id])
            ->with('company', 'carDetail', 'brand', 'brandModel', 'fuel', 'carZones', 'carZones.zone', 'carZones.zoneOption')
            ->asArray()
            ->one();

        if (empty($model)) {
            throw new HttpException(HttpStatus::NOT_FOUND, Yii::t('api-auto', 'No car available.'));
        }

        $lastKmUpdateMessage = CarKm::getLastUpdatedKm($id);
        $lastConsumptionUpdateMessage = CarConsumption::getLastUpdatedConsumption($id);
        $existCarId = CarZone::find()->where('car_id = :car_id', ['car_id' => $model['id']])->one();
        if (empty($existCarId)) {
            foreach (Zone::getZonesIds() as $zonesId) {
                $modelCarZoneNewCar = new CarZone();
                $modelCarZoneNewCar->car_id = $id;
                $modelCarZoneNewCar->zone_id = $zonesId;
                $modelCarZoneNewCar->zone_option_id = ZoneOption::find()->where(['zone_id' => $zonesId, 'value' => 1])->one()->id;
                $modelCarZoneNewCar->added = date('Y-m-d H:i:s');
                $modelCarZoneNewCar->added_by = Yii::$app->user->id;
                $modelCarZoneNewCar->load(Yii::$app->getRequest()->getBodyParams(), '');
                if (!$modelCarZoneNewCar->save()) {
                    if (!$modelCarZoneNewCar->hasErrors()) {
                        throw new ServerErrorHttpException(Yii::t('api-auto', 'Failed to create the car zone for this car.'));
                    } else {
                        throw new ServerErrorHttpException($modelCarZoneNewCar->errors[0][0]);
                    }
                }
            }
        }
        $model['status'] == 0 ? Car::setCarStatus(1, $id, Yii::$app->user->id, Yii::$app->user->id) : '';
        $zoneModel = Zone::find()->where("deleted = 0")->with([
            'carZone' => function (ActiveQuery $query) use ($id) {
                $query->andWhere('car_id = :car_id', [':car_id' => $id])->orderBy(['added' => SORT_DESC]);
            }, 'carZone.zoneOption'
        ])->asArray()->all();
        if (!empty($zoneModel)) {
            foreach ($zoneModel as $key => $carZoneOptionApi) {
                $carZonePhotos = CarZonePhoto::findAllByAttributes(['deleted' => 0, 'car_zone_id' => $carZoneOptionApi['carZone']['id']]);
                if (!empty($carZonePhotos)) {
                    $prevZoneImage = [];
                    foreach ($carZonePhotos as $carZonePhotoApi) {
                        $prevZoneImage[$carZonePhotoApi['zone_id']][$carZonePhotoApi['nr_photo']] = !empty($carZonePhotoApi['photo']) ? ImageHelper::convertImageFileToBase64("{$id}/" . $carZonePhotoApi['photo']) : null;
                    }
                }
                $carZones = $carZoneOptionApi['carZone'];
                $this->return['zonesDetails'][] = [
                    'id' => $carZoneOptionApi['id'],
                    'field' => $carZoneOptionApi['field'],
                    'label' => $carZoneOptionApi['label'],
                    'observation' => !empty($carZones['observations']) ? $carZones['observations'] : '',
                    'prev_zone_image' => isset($prevZoneImage) && count($prevZoneImage) > 0 && isset($prevZoneImage[$carZoneOptionApi['id']]) && $prevZoneImage[$carZoneOptionApi['id']] !== null ? $prevZoneImage[$carZoneOptionApi['id']] : [],
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

        $lastConsumption = CarConsumption::find()->where('car_id = :car_id', ['car_id' => $model['id']])->orderBy(['added' => SORT_DESC])->one();

        $km = CarKm::find()->where('car_id = :car_id AND source = 1', ['car_id' => $model['id']])->max('km');
        $source = 1;
        if (empty($km)) {
            $km = CarKm::find()->where('car_id = :car_id', ['car_id' => $model['id']])->max('km');
            $source = 2;
        }
        $this->return['car'] = [
            'kilometers' => $km == null ? 0 : $km,
            'source_km' => $source,
            'consumption' => !empty($lastConsumption) ? $lastConsumption['consumption'] : 0,
            'id' => $model['id'],
            'plate_number' => $model['plate_number'],
            'car_company_id' => $model['company']['id'],
            'company' => $model['company']['name'],
            'brand' => $model['brand']['name'],
            'model' => $model['brandModel']['name'],
            'vin' => $model['vin'],
            'fabrication_year' => $model['fabrication_year'],
            'fuel' => $model['fuel']['name'],
            'color' => $model['color'],
            'last_update_km' => $lastKmUpdateMessage,
            'last_update_consumption' => $lastConsumptionUpdateMessage,
        ];
        $this->return['message'] = Yii::t('api-auto', 'Successfully taken car data');
        return $this->return;
    }

    /**
     * @param $id
     * @return array|array[]
     * @throws HttpException
     * @throws \Exception
     */
    public function actionDocuments($id)
    {
        $car = Car::find()
            ->where("id = :id", [':id' => $id])
            ->with('carDetails', 'carDocuments', 'brand', 'brandModel', 'fuel')
            ->one();
        if ($car === null) {
            throw new HttpException(HttpStatus::NOT_FOUND, Yii::t('api-auto', 'No car available.'));
        }

        $isEmpowering = false;
        $sql = "SELECT empowering_name FROM `ecf_auto`.`car_operation` WHERE car_id = {$id} AND empowering_name IS NOT NULL ORDER BY id DESC LIMIT 1";
        $carOperation = Yii::$app->db->createCommand($sql)->queryOne();
        if ($carOperation) {
            $carOperation = $carOperation['empowering_name'];
            $srvPath = Yii::getAlias("@backend/upload/empowering-pdf/{$car->id}/") . $carOperation;
            if (file_exists($srvPath)) {
                $isEmpowering = true;
            }
        }
        return [
            'car' => [
                'id' => $car->id,
                'plate_number' => $car->plate_number,
                'company' => $car->company->name,
                'brand' => $car->brand->name,
                'model' => $car->brandModel->name,
                'vin' => $car->vin,
                'fabrication_year' => $car->fabrication_year,
                'fuel' => $car->fuel->name,
                'color' => $car->color,
                'documents' => $car->getDocumentsDetails($car),
                'empowering' => $isEmpowering,
            ]
        ];
    }

    public function actionGetDocument()
    {
        $get = Yii::$app->request->get();
        if (empty($get['id'])) {
            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', "Some error occurred while downloading the document, car id is missing, please try again.");
            return $this->return;
        }
        if (empty($get['document'])) {
            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', "Some error occurred while downloading the document, document name is missing, please try again.");
            return $this->return;
        }
        $document = [];
        if ($get['document'] == 'empowering') {
            $sql = "SELECT empowering_name FROM `ecf_auto`.`car_operation` WHERE car_id = {$get['id']} AND empowering_name IS NOT NULL ORDER BY id DESC LIMIT 1";
            $carOperation = Yii::$app->db->createCommand($sql)->queryOne()['empowering_name'];
            if ($carOperation) {
                $srvPath = Yii::getAlias("@backend/upload/empowering-pdf/{$get['id']}/") . $carOperation;
                if (file_exists($srvPath)) {
                    $document['file'] = 'data:application/pdf;base64,' . base64_encode(file_get_contents($srvPath));
                }
            }
            return $document;
        } else if ($get['document'] == 'documents') {
            $car = Car::find()
                ->where("id = :id", [':id' => $get['id']])
                ->with('carDetails', 'carDocuments', 'brand', 'brandModel', 'fuel')
                ->one();
            if ($car === null) {
                $this->return['status'] = 400;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-auto', "Some error occurred while downloading the document, car id is missing, please try again.");
                return $this->return;
            }
            $types = [
                1 => '.pdf',
                2 => '.jpg',
                3 => '.jpeg'
            ];
            $documentType = $get['document_field'];
            $sql = "SELECT * FROM `ecf_auto`.`car_document` WHERE car_id = {$get['id']} AND {$get['document_field']}_document_file IS NOT NULL ORDER BY id DESC LIMIT 1";
            $carDocStatus = Yii::$app->db->createCommand($sql)->queryOne()["{$get['document_field']}_document_file"];
            $field = strtoupper($documentType);
            $document = [];
            foreach ($types as $type) {
                $srvPath = Yii::getAlias("@backend/upload/auto/erp/car_{$car->id}/documents/{$field}/") . $carDocStatus . $type;
                if (file_exists($srvPath)) {
                    if (in_array($type, ['.jpg', '.jpeg'])) {
                        $docType = 'image/jpeg';
                    } else {
                        $docType = 'application/pdf';
                    }
                    $document['file'] = 'data:' . $docType . ';base64,' . base64_encode(file_get_contents($srvPath));
                    return $document;
                }
            }
            return $document;
        }
    }

    /**
     * @return array
     * @throws ServerErrorHttpException
     */
    public function actionPreviewPv()
    {
        $post = Yii::$app->request->post();

        try {

            $car = Car::find()
                ->with('brand', 'brandModel', 'company', 'carDocuments')
                ->where('id = :carId', [':carId' => $post['car_id']])
                ->asArray()
                ->one();
            if (empty($car)) {
                $this->return['status'] = HttpStatus::BAD_REQUEST;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-auto', 'No car id received');
            }
            $carId = $car['id'];

            if (empty($post['user_operation'])) {
                $post['user_operation'] = Car::getUserOperation();
                if (empty($post['user_operation'])) {
                    $this->return['status'] = HttpStatus::BAD_REQUEST;
                    Yii::$app->response->statusCode = $this->return['status'];
                    $this->return['message'] = Yii::t('api-auto', 'No operation type received. Please contact an administrator!');
                    return $this->return;
                }
            }
            $userOperation = $post['user_operation'];
            if (!in_array($userOperation, ['check_in', 'check_out'])) {
                $this->return['status'] = HttpStatus::BAD_REQUEST;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-auto', 'Received operation type is not correct. Please contact an administrator!');
                return $this->return;
            }

            $zoneModels = Zone::find()->where("deleted = 0")->with([
                'carZone' => function (ActiveQuery $query) use ($carId) {
                    $query->andWhere('car_id = :car_id', [':car_id' => $carId])->orderBy(['added' => SORT_DESC]);
                }, 'carZone.zoneOption'
            ])->asArray()->all();
            $adjustedZoneModels = $post['car_zone'];
            if (empty($zoneModels)) {
                throw new NotFoundHttpException(Yii::t('api-auto', "The car for witch the protocol is generated could not be found. Please contact an administrator!"));
            }

            // accessories for car
            if (!empty($car)) {
                $accessories = CarAccessory::find()
                    ->select("car_accessory.id as id, accessory.name as name, car_accessory.accessory_qty as count, car_accessory.observation as observations")
                    ->join('LEFT JOIN', 'accessory', 'accessory.id = car_accessory.accessory_id')
                    ->where("car_accessory.deleted = 0 AND car_id = {$car['id']}")
                    ->asArray()
                    ->all();
            }
            $date = date('d-m-Y');
            $carKm = CarKm::find()
                ->where(['car_id' => $carId])
                ->orderBy(['id' => SORT_DESC])
                ->one();

            $lastRegistrationNumber = PvRegister::find()
                ->where(['company_id' => $car['company_id']])
                ->orderBy(['added' => SORT_DESC])
                ->one();

            $pvNrReg = 1;
            if (!empty($lastRegistrationNumber)) {
                if (!empty($lastRegistrationNumber->empowering_nr_register)) {
                    $pvNrReg = $lastRegistrationNumber->empowering_nr_register + 1;
                } else {
                    $pvNrReg = $lastRegistrationNumber->pv_nr_register + 1;
                }
            }

            $html = $this->renderPartial('preview-fr7-pv-pdf', [
                'model' => $car,
                'user' => Yii::$app->user->identity,
                'zoneModel' => $zoneModels,
                'adjustedZoneModels' => $adjustedZoneModels,
                'handingCar' => $userOperation,
                'date' => $date,
                'postZoneOption' => !empty($post['car_zone']) ? $post['car_zone'] : [],
                'accessories' => $accessories,
                'post' => $post,
                'carKm' => empty($carKm) ? '-' : $carKm->km,
                'regNumber' => $pvNrReg
            ]);
        } catch (Exception $exc) {
            throw new ServerErrorHttpException(Yii::t('api-auto', 'Bad request') . ' ' . $exc->getMessage());
        }
        return mb_convert_encoding($html, 'UTF-8', 'UTF-8');
    }


    /**
     * @param $carId
     * @param $sign
     * @param $userOperation
     * @param $operationId
     * @return string
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function generatePv($carId, $sign, $userOperation, $operationId, $zonePhotos = null)
    {
        $model = Car::find()
            ->where(['id' => $carId])
            ->with('company', 'carDetail', 'carDocuments', 'brand', 'brandModel', 'carZones')
            ->asArray()
            ->one();
        if (empty($model)) {
            throw new NotFoundHttpException(Yii::t('api-auto', "The car for witch the protocol is generated could not be found. Please contact an administrator!"));
        }

        $employee = Employee::find()->where(['user_id' => Yii::$app->user->id])->with('user')->one();
        if (empty($employee)) {
            throw new NotFoundHttpException(Yii::t('api-auto', "The employee for witch the protocol is generated could not be found. Please contact an administrator!"));
        }

        $zoneModels = Zone::find()
            ->where("deleted = 0")
            ->with([
                'carZone' => function (ActiveQuery $query) use ($carId) {
                    $query->andWhere('car_id = :car_id', [':car_id' => $carId])->orderBy(['added' => SORT_DESC]);
                }, 'carZone.zoneOption'
            ])
            ->asArray()
            ->all();

        if (empty($zoneModels)) {
            throw new NotFoundHttpException(Yii::t('api-auto', "The car for witch the protocol is generated could not be found. Please contact an administrator!"));
        }

        $carParkAdminSetting = Settings::find()->where(['name' => 'CAR_PARK_ADMIN'])->asArray()->one();
        if (!empty($carParkAdminSetting) && !empty($carParkAdminSetting['value'])) {
            $carParkAdmins = trim($carParkAdminSetting['value']);
            $autoAdminEmailsToNotify = explode(',', $carParkAdmins);
        }

        try {
            $pvUploadPath = Yii::getAlias("@backend/web/car-zone-photo/{$carId}/");
            if (!is_dir($pvUploadPath)) {
                FileHelper::createDirectory($pvUploadPath);
            }
            $this->pvFileName = date('YmdHis') . '_car_operation_' . $operationId . '.pdf';

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
            $accessories = CarAccessory::find()
                ->select("car_accessory.accessory_id as id, accessory.name as name, car_accessory.accessory_qty as count, car_accessory.observation as observations")
                ->join('LEFT JOIN',
                    'accessory',
                    'accessory.id = car_accessory.accessory_id')
                ->where("car_accessory.deleted = 0 AND car_id = {$model['id']}")
                ->asArray()
                ->all();
            $backgroundImage = Yii::getAlias("@api/web/images/{$companyCarId}-pdf-img.png");
            $date = date('d-m-Y');
            $mpdf = new Mpdf(['tempDir' => Yii::getAlias('@backend/runtime')]);
            $carZonePhoto = null;
            if ($zonePhotos !== null && $zonePhotos !== 'NULL') {
                $carZonePhoto = CarZonePhoto::find()
                    ->andWhere(['in', 'car_zone_id', array_values($zonePhotos)])
                    ->andWhere(['deleted' => 0])
                    ->asArray()
                    ->all();
            }
            $carKm = CarKm::find()
                ->where(['car_id' => $carId])
                ->orderBy(['id' => SORT_DESC])
                ->one();

            $carModel = Car::findOne($carId);
            if (empty($carModel)) {
                return false;
            }

            $lastRegistrationNumber = PvRegister::find()
                ->where(['company_id' => $carModel->company_id])
                ->orderBy(['added' => SORT_DESC])
                ->one();
            $mpdf->writeHTML($this->renderPartial('pv-pdf', [
                'employee' => $employee,
                'model' => $model,
                'zoneModel' => $zoneModels,
                'sign' => $sign,
                'carZonePhoto' => $carZonePhoto,
                'handingCar' => $userOperation,
                'backgroundImage' => $backgroundImage,
                'date' => $date,
                'stylePdf' => $stylePdf,
                'accessories' => $accessories,
                'carKm' => empty($carKm) ? '-' : $carKm->km,
                'regNumber' => !empty($lastRegistrationNumber) ? $lastRegistrationNumber->pv_nr_register : 1
            ]));
            $user = User::find()->where(['id' => Yii::$app->user->id])->one();
            $mpdf->Output($pvUploadPath . $this->pvFileName, Destination::FILE);
            $statusMessage = $userOperation === 'check_out' ? Yii::t('api-auto', 'handing over') : Yii::t('api-auto', 'picked up');
            $_statusMessage = $userOperation === 'check_out' ? Yii::t('api-auto', 'handing_over') : Yii::t('api-auto', 'picked_up');
            $userName = $employee->fullName();

            if (
                !empty(Yii::$app->params['erp_beneficiary_name'])
                && Yii::$app->params['erp_beneficiary_name'] === 'ghallard'
            ) {
                $ccRecipients = [];
                $messages = Yii::$app->mailer->compose('@api/views/mail/update-car-status-html', [
                    'statusMessage' => $statusMessage,
                    'userName' => $userName,
                    'brand' => $model['brand']['name'],
                    'brandModel' => $model['brandModel']['name'],
                    'plateNumber' => $model['plate_number']
                ])
                    ->setFrom('econfaire@ghallard.ro')
                    ->setTo($employee->email)
                    ->attach($pvUploadPath . $this->pvFileName)
                    ->setSubject(Yii::t('api-auto', "Report of {statusMessage} the car ", ['statusMessage' => $_statusMessage]) . $model['plate_number']);
                foreach ($autoAdminEmailsToNotify as $autoAdminEmail) {
                    $ccRecipients[] = $autoAdminEmail;
                }
                $messages->setCc($ccRecipients)->send();

                $msg = null;
            } else {
                $sendEmail = new SendSharePointMailHelper();
                $sendEmail->subject = Yii::t('api-auto', "Report of {statusMessage} the car ", ['statusMessage' => $_statusMessage]) . $model['plate_number'];
                $sendEmail->content = [
                    "contentType" => "html",
                    "content" => $this->renderPartial('@api/views/mail/update-car-status-html', [
                        'statusMessage' => $statusMessage,
                        'userName' => $userName,
                        'brand' => $model['brand']['name'],
                        'brandModel' => $model['brandModel']['name'],
                        'plateNumber' => $model['plate_number']
                    ]),
                ];
                $sendEmail->toRecipients = [
                    [
                        "emailAddress" => [
                            "name" => $userName,
                            "address" => $employee->email
                        ]
                    ]
                ];

                $sendEmail->attachments = [
                    [
                        "@odata.type" => "#microsoft.graph.fileAttachment",
                        "name" => $this->pvFileName,
                        "contentType" => BaseFileHelper::getMimeType($pvUploadPath . $this->pvFileName),
                        "contentBytes" => chunk_split(base64_encode(file_get_contents($pvUploadPath . $this->pvFileName))),
                    ],
                ];

                foreach ($autoAdminEmailsToNotify as $autoAdminEmail) {
                    $sendEmail->ccRecipients[] = [
                        "emailAddress" => [
                            "name" => (User::find()->where('email = :email', [':email' => $autoAdminEmail])->one())->fullName(),
                            "address" => $autoAdminEmail
                        ]
                    ];
                }
                $msg = $sendEmail->sendEmail();
            }

        } catch (MpdfException|Exception $exc) {
            throw new ServerErrorHttpException(Yii::t('api-auto', 'No valid response received from server. Please contact an administrator!') . ' ' . "{$exc->getMessage()}!");
        }

        return $msg;
    }

    public function actionDownloadCarPv($file)
    {
        $root = Yii::getAlias($file);
        if (file_exists($root)) {
            return Yii::$app->response->sendFile($root);
        } else {
            throw new \yii\web\NotFoundHttpException("{$file} is not found!");
        }
    }

    /**
     * @return array|void
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdateCarStatus()
    {
        $mutex = Yii::$app->mutex;
        $lock = $mutex->acquire('car-status-update');

        if ($lock) {
            sleep(5);
        }

        try {
            $post = Yii::$app->request->post();
            if (empty($post['user_operation'])) {
                $post['user_operation'] = Car::getUserOperation();
                if (empty($post['user_operation'])) {
                    $this->return['status'] = HttpStatus::BAD_REQUEST;
                    Yii::$app->response->statusCode = $this->return['status'];
                    $this->return['message'] = Yii::t('api-auto', 'No operation type received. Please contact an administrator!');
                    return $this->return;
                }
            }
            $userOperation = $post['user_operation'];
            if (!in_array($userOperation, ['check_in', 'check_out'])) {
                $this->return['status'] = HttpStatus::BAD_REQUEST;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-auto', 'Received operation type is not correct. Please contact an administrator!');
                return $this->return;
            }

            if (empty($post['car_id'])) {
                $this->return['status'] = HttpStatus::BAD_REQUEST;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-auto', 'Incomplete received data, missing car details');
                return $this->return;
            }
            $carId = $post['car_id'];

            if (empty($post['signature'])) {
                $this->return['status'] = HttpStatus::BAD_REQUEST;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-auto', 'No signature received. If you are sure that you signed, please contact an administrator!');
                return $this->return;
            }
            $signature = $post['signature'];

            $car = Car::find()->where('id = :car_id AND deleted = 0', [':car_id' => $carId])->with('company', 'brand', 'brandModel')->one();
            if (empty($car)) {
                $this->return['status'] = HttpStatus::BAD_REQUEST;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-auto', 'No data found for requested car. Please contact an administrator!');
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
                        $this->return['status'] = HttpStatus::BAD_REQUEST;
                        Yii::$app->response->statusCode = $this->return['status'];
                        $this->return['message'] = Yii::t('api-auto', 'Invalid signature format!');
                        return $this->return;
                    }
                    $signatureName = Yii::$app->user->id . '_' . uniqid() . ".{$type}";
                    $signaturePath = $signatureDir . '/' . $signatureName;
                    $image = str_replace(' ', '+', $image);
                    $image = base64_decode($image);
                    $existingUserSignature = UserSignature::find()
                        ->where(['user_id' => Yii::$app->user->id, 'deleted' => 0])
                        ->one();
                    if (empty($existingUserSignature)) {
                        $userSignature = new UserSignature();
                        $userSignature->signature = $signatureName;
                        $userSignature->user_id = Yii::$app->user->id;
                        $userSignature->added = date('Y-m-d H:i:s');
                        $userSignature->added_by = Yii::$app->user->id;
                        if (!$userSignature->save()) {
                            if ($userSignature->hasErrors()) {
                                foreach ($userSignature->errors as $error) {
                                    throw new HttpException(409, $error[0]);
                                }
                            }
                            throw new HttpException(409, Yii::t('api-auto', 'Could not save signature. Please contact an administrator!'));
                        }
                    } else {
                        $existingUserSignature->signature = $signatureName;
                        $existingUserSignature->updated = date('Y-m-d H:i:s');
                        $existingUserSignature->updated_by = Yii::$app->user->id;
                        if (!$existingUserSignature->save()) {
                            if ($existingUserSignature->hasErrors()) {
                                foreach ($existingUserSignature->errors as $error) {
                                    throw new HttpException(409, $error[0]);
                                }
                            }
                            throw new HttpException(409, Yii::t('api-auto', 'Could not save signature. Please contact an administrator!'));
                        }
                    }
                    if ($image === false) {
                        $this->return['status'] = HttpStatus::BAD_REQUEST;
                        Yii::$app->response->statusCode = $this->return['status'];
                        $this->return['message'] = Yii::t('api-auto', 'Could not decode the signature. Please contact an administrator!');
                        return $this->return;
                    }
                } else {
                    $this->return['status'] = HttpStatus::BAD_REQUEST;
                    Yii::$app->response->statusCode = $this->return['status'];
                    $this->return['message'] = Yii::t('api-auto', 'Did not match data URI with image data. Please contact an administrator!');
                    return $this->return;
                }
                if (!file_put_contents($signaturePath, $image)) {
                    $this->return['status'] = HttpStatus::INTERNAL_SERVER_ERROR;
                    Yii::$app->response->statusCode = $this->return['status'];
                    $this->return['message'] = Yii::t('api-auto', 'The signature could not be saved. Please contact an administrator!');
                    return $this->return;
                }
            } catch (\Exception $exc) {
                $msg = "Error received while saving signature: {$exc->getMessage()} \n";
                $msg .= "Please contact an administrator!";
                $this->return['status'] = HttpStatus::BAD_REQUEST;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = $msg;
                return $this->return;
            }
            if (empty($post['car_zone'])) {
                $transaction = Yii::$app->ecf_auto_db->beginTransaction();
                try {
                    $empoweringName = date('YmdHis') . "-car_{$car->id}.pdf";
                    $carOperation = new CarOperation();
                    $carOperation->user_id = Yii::$app->user->id;
                    $carOperation->car_id = $car->id;
                    $carOperation->operation_type_id = $userOperation === 'check_in' ? 1 : 2;
                    $carOperation->added = date('Y-m-d H:i:s');
                    $carOperation->added_by = Yii::$app->user->id;
                    if (!$carOperation->save()) {
                        if ($carOperation->hasErrors()) {
                            foreach ($carOperation->errors as $error) {
                                throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                            }
                        }
                        throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-auto', 'Failed to save car operation. Please contact an administrator!'));
                    }
                    $carOperation->pdf_name = date('YmdHis') . '_car_operation_' . $carOperation->id . '.pdf';
                    $carOperation->empowering_name = $userOperation === 'check_in' ? $empoweringName : null;
                    $carOperation->save();

                    PvRegister::setRegistrationNumber($carOperation);

                    $carUserId = $userOperation === 'check_in' ? Yii::$app->user->id : null;
                    $msgForEmail = $this->generatePv($car->id, $signature, $userOperation, $carOperation->id);
                    if ($userOperation === 'check_in') {
                        $this->generateEmpowering($car, $empoweringName);
                    }

                    if (!empty($post['accessories'])) {
                        foreach ($post['accessories'] as $param) {
                            $id = $param['id'];
                            $accessory = CarAccessory::find()->where('car_id = :car_id', [':car_id' => $car->id])->andWhere(['id' => $id])->one();
                            $accessory->accessory_qty = $param['quantity'];
                            $accessory->observation = $param['observation'];
                            $accessory->updated = date('Y-m-d H:i:s');
                            $accessory->updated_by = Yii::$app->user->id;

                            if (!$accessory->save()) {
                                if ($accessory->hasErrors()) {
                                    foreach ($accessory->errors as $error) {
                                        throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                                    }
                                }
                                throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-auto', 'Failed to update car accessories'));
                            }
                        }
                    }
                    $userOperation == 'check_in' ? Car::setCarStatus(2, $car->id, Yii::$app->user->id, $carUserId) : Car::setCarStatus(0, $car->id, Yii::$app->user->id, null);
                    $transaction->commit();
                } catch (HttpException $exc) {
                    $transaction->rollBack();
                    Yii::$app->response->statusCode = $exc->statusCode;
                    $this->return['status'] = $exc->statusCode;
                    $this->return['message'] = Yii::t('api-auto', $exc->getMessage());
                    return $this->return;
                }
                $msg = $userOperation === 'check_in' ?
                    Yii::t('api-auto', 'Car successfully taken.') . ' ' .
                    Yii::t('api-auto', 'No changes zone option for this car.') :
                    Yii::t('api-auto', 'Car successfully handed over.') . ' ' .
                    Yii::t('api-auto', 'No changes zone option for this car.');
                $this->return['status'] = HttpStatus::OK;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = $msg . ' ' . $msgForEmail;
                return $this->return;
            }
            $transaction = Yii::$app->ecf_auto_db->beginTransaction();
            $zonePhotos = [];
            try {
                foreach ($post['car_zone'] as $key => $param) {
                    $zoneId = $param['zone_id'];
                    $photoIds[$zoneId] = [];
                    $carZoneModels = CarZone::find()->where('car_id = :car_id', [':car_id' => $car->id])->andWhere(['zone_id' => $zoneId])->one();
                    if (isset($param['zone_photo']) && !empty($param['zone_photo'])) {
                        foreach ($param['zone_photo'] as $key => $photo) {
                            $carZonePhotoDir = Yii::getAlias("@backend/web/car-zone-photo/{$car->id}");
                            $photoDate = date('YmdHis');
                            $fileName = "{$zoneId}_{$carZoneModels->zone_option_id}_{$key}_{$photoDate}";
                            $modelCarZonePhotoExist = CarZonePhoto::findOneByAttributes([
                                'car_zone_id' => $carZoneModels->id,
                                'zone_id' => $zoneId,
                                'nr_photo' => $key,
                                'deleted' => 0
                            ]);
                            if ($photo != '' && empty($modelCarZonePhotoExist)) {
                                ImageHelper::saveBase64ToImageFile($photo, $fileName, $carZonePhotoDir);
                                $modelCarZonePhoto = new CarZonePhoto();
                                $modelCarZonePhoto->car_zone_id = $carZoneModels->id;
                                $modelCarZonePhoto->zone_id = $zoneId;
                                $modelCarZonePhoto->nr_photo = $key;
                                $modelCarZonePhoto->photo = empty($fileName) ? null : "{$fileName}.jpeg";
                                $modelCarZonePhoto->added = date('Y-m-d H:i:s');
                                $modelCarZonePhoto->added_by = Yii::$app->user->id;

                                if (!$modelCarZonePhoto->save()) {
                                    if ($modelCarZonePhoto->hasErrors()) {
                                        foreach ($modelCarZonePhoto->errors as $error) {
                                            throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                                        }
                                    }
                                    throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-auto', 'Failed to save car zone photo'));
                                }
                                $photoIds[$zoneId] = empty($fileName) ? null : "{$fileName}.jpeg";
                                $modelCarZonePhoto->save();
                            } else {
                                if (!empty($modelCarZonePhotoExist) && $photo == '') {
                                    $sql = "UPDATE car_zone_photo SET deleted = 1 WHERE id = {$modelCarZonePhotoExist->id} AND car_zone_id = {$carZoneModels->id} AND zone_id = {$zoneId} AND nr_photo = {$key}";
                                    Yii::$app->ecf_auto_db->createCommand($sql)->execute();
                                }
                            }
                            $zonePhotos[] = $carZoneModels->id;
                        }
                    }
                    $carZoneModels->zone_id = $zoneId;
                    if (!empty($param['zone_option_id'])) {
                        $carZoneModels->zone_option_id = $param['zone_option_id'];
                    }
                    $carZoneModels->observations = !empty($param['observations']) ? $param['observations'] : null;
                    $carZoneModels->zone_photo = !empty($photoIds[$zoneId]) ? $photoIds[$zoneId] : null;
                    $carZoneModels->updated = date('Y-m-d H:i:s');
                    $carZoneModels->updated_by = Yii::$app->user->id;

                    if (!$carZoneModels->save()) {
                        if ($carZoneModels->hasErrors()) {
                            foreach ($carZoneModels->errors as $error) {
                                throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                            }
                        }
                        throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-auto', 'Failed to update car status'));
                    }

                    $carZoneHistory = new CarZoneHistory();
                    $carZoneHistory->car_id = $car->id;
                    $carZoneHistory->zone_id = $zoneId;
                    $carZoneHistory->zone_option_id = $param['zone_option_id'];
                    $carZoneHistory->observations = !empty($param['observations']) ? $param['observations'] : null;
                    $carZoneHistory->zone_photo = !empty($photoIds[$zoneId]) ? $photoIds[$zoneId] : null;
                    $carZoneHistory->added = date('Y-m-d H:i:s');
                    $carZoneHistory->added_by = Yii::$app->user->id;
                    if (!$carZoneHistory->save()) {
                        if ($carZoneHistory->hasErrors()) {
                            foreach ($carZoneHistory->errors as $error) {
                                throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                            }
                        }
                        throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-auto', 'Failed to update car status'));
                    }
                }

                if (!empty($post['accessories'])) {
                    try {
                        foreach ($post['accessories'] as $param) {
                            $id = $param['id'];
                            $accessory = CarAccessory::find()->where('car_id = :car_id', [':car_id' => $car->id])->andWhere(['id' => $id])->one();
                            $accessory->accessory_qty = $param['quantity'];
                            $accessory->observation = $param['observation'];
                            $accessory->updated = date('Y-m-d H:i:s');
                            $accessory->updated_by = Yii::$app->user->id;

                            if (!$accessory->save()) {
                                if ($accessory->hasErrors()) {
                                    foreach ($accessory->errors as $error) {
                                        throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                                    }
                                }
                                throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-auto', 'Failed to update car accessories'));
                            }
                        }
                    } catch (HttpException $exc) {
                        Yii::$app->response->statusCode = $exc->statusCode;
                        $this->return['status'] = $exc->statusCode;
                        $this->return['message'] = Yii::t('api-auto', $exc->getMessage());
                        return $this->return;
                    }
                }
                $empoweringName = date('YmdHis') . "-car_{$car->id}.pdf";

                //save car status to operation_car
                $carOperation = new CarOperation();
                $carOperation->user_id = Yii::$app->user->id;
                $carOperation->car_id = $car->id;
                $carOperation->operation_type_id = $userOperation === 'check_in' ? 1 : 2;
                $carOperation->added = date('Y-m-d H:i:s');
                $carOperation->added_by = Yii::$app->user->id;
                if (!$carOperation->save()) {
                    if ($carOperation->hasErrors()) {
                        foreach ($carOperation->errors as $error) {
                            throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                        }
                    }
                    throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-auto', 'Failed to save car operation. Please contact an administrator!'));
                }
                $carOperation->pdf_name = date('YmdHis') . '_car_operation_' . $carOperation->id . '.pdf';
                $carOperation->empowering_name = $userOperation === 'check_in' ? $empoweringName : null;
                $carOperation->save();

                PvRegister::setRegistrationNumber($carOperation, 2);

                try {
                    $msgForEmail = $this->generatePv($car->id, $signature, $userOperation, $carOperation->id, $zonePhotos);
                    if ($userOperation === 'check_in') {
                        $this->generateEmpowering($car, $empoweringName);
                    }
                    $carUserId = $userOperation === 'check_in' ? Yii::$app->user->id : null;
                    $userOperation == 'check_in' ? Car::setCarStatus(2, $car->id, Yii::$app->user->id, $carUserId) : Car::setCarStatus(0, $car->id, Yii::$app->user->id, null);
                } catch (ServerErrorHttpException $exc) {
                    $msgForEmail = "Exception status: {$exc->statusCode}, message: {$exc->getMessage()}";
                }

                $transaction->commit();
                $msg = $userOperation === 'check_in' ?
                    Yii::t('api-auto', 'Car successfully taken.') :
                    Yii::t('api-auto', 'Car successfully handed over.');
                $this->return['status'] = HttpStatus::OK;
                $this->return['message'] = $msg . ' ' . $msgForEmail;
                return $this->return;
            } catch (HttpException $exc) {
                $transaction->rollBack();
                Yii::$app->response->statusCode = $exc->statusCode;
                $this->return['status'] = $exc->statusCode;
                $this->return['message'] = Yii::t('api-auto', $exc->getMessage());
                return $this->return;
            } catch (Exception $exc) {
                $transaction->rollBack();
                Yii::$app->response->statusCode = $exc->getCode();
                $this->return['status'] = $exc->getCode();
                $this->return['message'] = Yii::t('api-auto', $exc->getMessage());
                return $this->return;
            }
            $msg = $userOperation === 'check_in' ?
                Yii::t('api-auto', 'Car successfully taken.') . ' ' .
                Yii::t('api-auto', 'No changes zone option for this car.') :
                Yii::t('api-auto', 'Car successfully handed over.') . ' ' .
                Yii::t('api-auto', 'No changes zone option for this car.');
            $this->return['status'] = HttpStatus::OK;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = $msg . ' ' . $msgForEmail;
            return $this->return;

            $transaction = Yii::$app->ecf_auto_db->beginTransaction();
            $zonePhotos = [];
            try {
                foreach ($post['car_zone'] as $key => $param) {
                    $zoneId = $param['zone_id'];
                    $carZoneModels = CarZone::find()->where('car_id = :car_id', [':car_id' => $car->id])->andWhere(['zone_id' => $zoneId])->one();
                    if (isset($param['zone_photo']) && !empty($param['zone_photo'])) {
                        foreach ($param['zone_photo'] as $key => $photo) {
                            $carZonePhotoDir = Yii::getAlias("@backend/web/car-zone-photo/{$car->id}");
                            $photoDate = date('YmdHis');
                            $fileName = "{$zoneId}_{$carZoneModels->zone_option_id}_{$key}_{$photoDate}";
                            $modelCarZonePhotoExist = CarZonePhoto::findOneByAttributes([
                                'car_zone_id' => $carZoneModels->id,
                                'zone_id' => $zoneId,
                                'nr_photo' => $key,
                                'deleted' => 0
                            ]);
                            if ($photo != '' && empty($modelCarZonePhotoExist)) {
                                ImageHelper::saveBase64ToImageFile($photo, $fileName, $carZonePhotoDir);
                                $modelCarZonePhoto = new CarZonePhoto();
                                $modelCarZonePhoto->car_zone_id = $carZoneModels->id;
                                $modelCarZonePhoto->zone_id = $zoneId;
                                $modelCarZonePhoto->nr_photo = $key;
                                $modelCarZonePhoto->photo = empty($fileName) ? null : "{$fileName}.jpeg";
                                $modelCarZonePhoto->added = date('Y-m-d H:i:s');
                                $modelCarZonePhoto->added_by = Yii::$app->user->id;

                                if (!$modelCarZonePhoto->save()) {
                                    if ($modelCarZonePhoto->hasErrors()) {
                                        foreach ($modelCarZonePhoto->errors as $error) {
                                            throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                                        }
                                    }
                                    throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-auto', 'Failed to save car zone photo'));
                                }
                                $modelCarZonePhoto->save();
                            } else {
                                if (!empty($modelCarZonePhotoExist) && $photo == '') {
                                    $sql = "UPDATE car_zone_photo SET deleted = 1 WHERE id = {$modelCarZonePhotoExist->id} AND car_zone_id = {$carZoneModels->id} AND zone_id = {$zoneId} AND nr_photo = {$key}";
                                    Yii::$app->ecf_auto_db->createCommand($sql)->execute();
                                }
                            }
                            $zonePhotos[] = $carZoneModels->id;
                        }
                    }
                    $carZoneModels->zone_id = $zoneId;
                    if (!empty($param['zone_option_id'])) {
                        $carZoneModels->zone_option_id = $param['zone_option_id'];
                    }
                    $carZoneModels->observations = !empty($param['observations']) ? $param['observations'] : null;
                    $carZoneModels->zone_photo = null;
                    $carZoneModels->updated = date('Y-m-d H:i:s');
                    $carZoneModels->updated_by = Yii::$app->user->id;

                    if (!$carZoneModels->save()) {
                        if ($carZoneModels->hasErrors()) {
                            foreach ($carZoneModels->errors as $error) {
                                throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                            }
                        }
                        throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-auto', 'Failed to update car status'));
                    }
                }

                if (!empty($post['accessories'])) {
                    try {
                        foreach ($post['accessories'] as $param) {
                            $id = $param['id'];
                            $accessory = CarAccessory::find()->where('car_id = :car_id', [':car_id' => $car->id])->andWhere(['id' => $id])->one();
                            $accessory->accessory_qty = $param['quantity'];
                            $accessory->observation = $param['observation'];
                            $accessory->updated = date('Y-m-d H:i:s');
                            $accessory->updated_by = Yii::$app->user->id;

                            if (!$accessory->save()) {
                                if ($accessory->hasErrors()) {
                                    foreach ($accessory->errors as $error) {
                                        throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                                    }
                                }
                                throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-auto', 'Failed to update car accessories'));
                            }
                        }
                    } catch (HttpException $exc) {
                        Yii::$app->response->statusCode = $exc->statusCode;
                        $this->return['status'] = $exc->statusCode;
                        $this->return['message'] = Yii::t('api-auto', $exc->getMessage());
                        return $this->return;
                    }
                }
                $empoweringName = date('YmdHis') . "-car_{$car->id}.pdf";

                //save car status to operation_car
                $carOperation = new CarOperation();
                $carOperation->user_id = Yii::$app->user->id;
                $carOperation->car_id = $car->id;
                $carOperation->operation_type_id = $userOperation === 'check_in' ? 1 : 2;
                $carOperation->added = date('Y-m-d H:i:s');
                $carOperation->added_by = Yii::$app->user->id;
                if (!$carOperation->save()) {
                    if ($carOperation->hasErrors()) {
                        foreach ($carOperation->errors as $error) {
                            throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                        }
                    }
                    throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-auto', 'Failed to save car operation. Please contact an administrator!'));
                }
                $carOperation->pdf_name = date('YmdHis') . '_car_operation_' . $carOperation->id . '.pdf';
                $carOperation->empowering_name = $userOperation === 'check_in' ? $empoweringName : null;
                $carOperation->save();

                try {
                    $msgForEmail = $this->generatePv($car->id, $signature, $userOperation, $carOperation->id, $zonePhotos);
                    if ($userOperation === 'check_in') {
                        $this->generateEmpowering($car, $empoweringName);
                    }
                    $carUserId = $userOperation === 'check_in' ? Yii::$app->user->id : null;
                    $userOperation == 'check_in' ? Car::setCarStatus(2, $car->id, Yii::$app->user->id, $carUserId) : Car::setCarStatus(0, $car->id, Yii::$app->user->id, null);
                } catch (ServerErrorHttpException $exc) {
                    $msgForEmail = "Exception status: {$exc->statusCode}, message: {$exc->getMessage()}";
                }

                    $transaction->commit();
                    $msg = $userOperation === 'check_in' ?
                        Yii::t('api-auto', 'Car successfully taken.') :
                        Yii::t('api-auto', 'Car successfully handed over.');
                    $this->return['status'] = HttpStatus::OK;
                    $this->return['message'] = $msg . ' ' . $msgForEmail;
                    return $this->return;
            } catch (HttpException $exc) {
                $transaction->rollBack();
                Yii::$app->response->statusCode = $exc->statusCode;
                $this->return['status'] = $exc->statusCode;
                $this->return['message'] = Yii::t('api-auto', $exc->getMessage());
                return $this->return;
            } catch (Exception $exc) {
                $transaction->rollBack();
                Yii::$app->response->statusCode = $exc->getCode();
                $this->return['status'] = $exc->getCode();
                $this->return['message'] = Yii::t('api-auto', $exc->getMessage());
                return $this->return;
            }
        } finally {
            $mutex->release('car-status-update');
        }
    }

    public function actionUnlock()
    {
        $post = Yii::$app->request->post();
        if (empty($post)) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', 'No data received. Please contact an administrator!');
            return $this->return;
        }
        if (empty($id)) {
            $this->return['message'] = Yii::t('api-auto', 'No car previously reserved');
            return $this->return;
        }

        $userOperation = $post['user_operation'];
        $carUserId = $userOperation === 'check_out' ? Yii::$app->user->id : null;
        if (!empty($id))
            try {
                Car::setCarStatus(0, $id, Yii::$app->user->id, $carUserId);
            } catch (HttpException $exc) {
                Yii::$app->response->statusCode = $exc->statusCode;
                $this->return['status'] = $exc->statusCode;
                $this->return['message'] = $exc->getMessage();
                return $this->return;
            }
        return $this->return;
    }

    /**
     * @throws Exception
     * @todo: de luat dintr-o tabela car_document_type, mai precis setNames()
     */
    public function actionUploadDocument()
    {
        $post = Yii::$app->request->post();
        $documentsFilesTypes = [
            'RCA' => 'rca_document_file',
            'CASCO' => 'casco_document_file',
            'ITP' => 'itp_document_file',
            'VIGNETTE' => 'vignette_document_file',
        ];
        $fileTypeAccepted = ['pdf', 'jpg', 'jpeg', 'png'];

        if (empty($post['document_type'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', "Some error occurred while uploading the document, document type is missing, please try again.");
            return $this->return;
        }
        $documentType = strtoupper($post['document_type']);
        if (!array_key_exists($documentType, $documentsFilesTypes)) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', "Some error occurred while uploading the document, document type is incorrect, please try again.");
            return $this->return;
        }
        if (empty($post['car_id'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', "Some error occurred while uploading the document, car id is missing, please try again");
            return $this->return;
        }
        $car = Car::find()->where('id = :id', [':id' => $post['car_id']])->one();
        if ($car === null) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', 'The car does not exist');
            return $this->return;
        }
        $upload = UploadedFile::getInstancesByName('document');
        $fileExtension = $upload[0]->extension ? $upload[0]->extension : $upload[0]->name;
        if (!in_array($fileExtension, $fileTypeAccepted)) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', 'The file format is not supported');
            return $this->return;
        }
        $documentPath = Yii::getAlias("@backend/upload/auto/erp/car_{$post['car_id']}/documents/{$documentType}/");
        if (!is_dir($documentPath)) {
            FileHelper::createDirectory($documentPath);
        }
        $fileName = date('YmdHis') . "-{$post['car_id']}-{$documentType}";
        if (empty($upload)) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-auto', 'Some error occurred while uploading the document, document is missing, please try again');
            return $this->return;
        }
        foreach ($upload as $file) {
            if ($file->size > '10485760') {
                $this->return['status'] = HttpStatus::BAD_REQUEST;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-auto', "The document is too large, please try again with a smaller document");
                return $this->return;
            } else {
                $file->saveAs($documentPath . $fileName . '.' . $fileExtension);
            }
        }
        $documentFileType = $documentsFilesTypes[$documentType];
        $changeValue = [];
        $changeValue[$documentFileType] = $fileName;
        $model = CarDocument::findOneByAttributes(['car_id' => $post['car_id']]);
        $oldValue[$documentFileType] = $model->$documentFileType;
        if (!empty($model)) {
            $model->$documentFileType = $fileName;
            $model['updated'] = date('Y-m-d H:i:s');
            $model['updated_by'] = Yii::$app->user->id;
            if (!$model->save()) {
                if ($model->hasErrors()) {
                    foreach ($model->errors as $error) {
                        Yii::$app->response->statusCode = 409;
                        $this->return['status'] = 409;
                        $this->return['message'] = $error[0];
                        return $this->return;
                    }
                }
                $this->return['status'] = HttpStatus::INTERNAL_SERVER_ERROR;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-auto', 'Failed to save car document. Please contact an administrator!');
                return $this->return;
            }

            try {
                CarDocumentHistory::insertHistory($model, $oldValue);
            } catch (HttpException $exc) {
                $this->return['status'] = $exc->statusCode;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = $exc->getMessage();
                return $this->return;
            }
        }

        $carParkAdminSetting = Settings::find()->where(['name' => 'CAR_PARK_ADMIN'])->asArray()->one();
        if (!empty($carParkAdminSetting) && !empty($carParkAdminSetting['value'])) {
            $carParkAdmins = trim($carParkAdminSetting['value']);
            $adminsToNotify = explode(',', $carParkAdmins);
        }
        $employee = User::findOneByAttributes(['id' => Yii::$app->user->id]);
        foreach ($adminsToNotify as $adminToNotify) {
            $admin = User::findOneByAttributes(['email' => $adminToNotify]);
            if (empty($admin)) {
                $this->return['status'] = HttpStatus::BAD_REQUEST;
                $this->return['message'] = Yii::t('api-auto', 'No admin found') . ': ' . $adminToNotify;
                return $this->return;
            }
            if (empty($employee)) {
                $this->return['status'] = HttpStatus::BAD_REQUEST;
                $this->return['message'] = Yii::t('api-auto', 'No employee found') . ': ' . Yii::$app->user->id;
                return $this->return;
            }

            $mailBody = $this->renderPartial('auto-car-accessory-expiration-notifications-html', [
                'adminName' => $admin->fullName(),
                'employeeName' => $employee->fullName(),
                'documentType' => $documentType,
                'company' => $car['company']['name'],
                'plateNumber' => $car['plate_number']
            ]);
            if (
                !empty(Yii::$app->params['erp_beneficiary_name'])
                && Yii::$app->params['erp_beneficiary_name'] === 'ghallard'
            ) {
                Yii::$app->mailer->compose($mailBody)
                    ->setFrom('econfaire@ghallard.ro')
                    ->setTo($adminToNotify)
                    ->setSubject(Yii::t('api-auto', 'Upload car documents'))
                    ->send();
            } else {
                $sendEmail = new SendSharePointMailHelper();
                $sendEmail->content = [
                    "contentType" => "html",
                    "content" => $mailBody,
                ];

                $sendEmail->subject = Yii::t('api-auto', 'Upload car documents');

                $sendEmail->toRecipients = [
                    [
                        "emailAddress" => [
                            "name" => $admin->fullName(),
                            "address" => $adminToNotify
                        ]
                    ]
                ];

                $sendEmail->ccRecipients[] = [
                    "emailAddress" => [
                        "name" => $employee->fullName(),
                        "address" => $adminToNotify
                    ]
                ];
                $sendEmail->sendEmail();
            }
        }

        $this->return['status'] = HttpStatus::OK;
        $this->return['message'] = Yii::t('api-auto', 'Document successfully uploaded');
        return $this->return;
    }

    /**
     * @return array
     * @throws HttpException
     *
     */
    public function actionSetKm()
    {
        $post = Yii::$app->request->post();
        $companyId = $post['company_id'];
        if (empty($companyId)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('api-auto', 'No company id received');
            return $this->return;
        }
        $carId = $post['car_id'];
        if (empty($carId)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('api-auto', 'No car id received');
            return $this->return;
        }
        $source = $post['source'];
        if (empty($source)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('api-auto', 'No source id received');
            return $this->return;
        }
        $km = $post['kilometers'];
        if ($km != 0) {
            $modelCarKm = new CarKm;
            $modelCarKm->company_id = $companyId;
            $modelCarKm->car_id = $carId;
            $modelCarKm->km = $km;
            $modelCarKm->source = $source;
            $modelCarKm->added = date('Y-m-d H:i:s');
            $modelCarKm->added_by = Yii::$app->user->id;
            if (!$modelCarKm->save()) {
                if ($modelCarKm->hasErrors()) {
                    foreach ($modelCarKm->errors as $error) {
                        throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                    }
                }
                throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-auto', 'Failed to save car operation. Please contact an administrator!'));
            }
            $modelCarKm->save();
        }
        $consumption = $post['consumption'];
        $carConsumption = CarConsumption::find()->where('car_id = :car_id', [':car_id' => $carId])->orderBy('id DESC')->one();
        $lastConsumption = isset($carConsumption) ? $carConsumption->consumption : 0;
        if (
            $consumption != 0
            && $consumption != $lastConsumption
        ) {
            $modelCarConsumption = new CarConsumption;
            $modelCarConsumption->car_id = $carId;
            $modelCarConsumption->consumption = $consumption;
            $modelCarConsumption->source = $source;
            $modelCarConsumption->added = date('Y-m-d H:i:s');
            $modelCarConsumption->added_by = Yii::$app->user->id;

            if (!$modelCarConsumption->save()) {
                if ($modelCarConsumption->hasErrors()) {
                    foreach ($modelCarConsumption->errors as $error) {
                        throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                    }
                }
                throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-auto', 'Failed to save car operation. Please contact an administrator!'));
            }
            $modelCarConsumption->save();
        }

        $lastKmUpdateMessage = CarKm::getLastUpdatedKm($carId);
        $lastConsumptionUpdateMessage = CarConsumption::getLastUpdatedConsumption($carId);

        $this->return['new_kilometers'] = $km;
        $this->return['last_update_km'] = $lastKmUpdateMessage;
        $this->return['last_update_consumption'] = $lastConsumptionUpdateMessage;
        $this->return['new_consume'] = $consumption;
        $this->return['status'] = 200;
        $this->return['message'] = Yii::t('api-auto', 'Kilometers successfully set');
        return $this->return;
    }

    /**
     * @throws MpdfException
     * @throws Exception
     */
    public function generateEmpowering($car, $empoweringName = null)
    {
        $employee = Employee::find()->where(['user_id' => Yii::$app->user->id])->with('user')->one();
        if (empty($employee)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('api-auto', 'The employee for witch the empowering is generated could not be found. Please contact an administrator!');
            return $this->return;
        }
        $carParkAdminSetting = Settings::find()->where(['name' => 'CAR_PARK_ADMIN'])->asArray()->one();
        if (!empty($carParkAdminSetting) && !empty($carParkAdminSetting['value'])) {
            $carParkAdmins = trim($carParkAdminSetting['value']);
            $adminsToNotify = explode(',', $carParkAdmins);
        }
        $holder = User::find()->where('id = :id', [':id' => $car['holder_id']])->one();
        $uploadPath = Yii::getAlias("@backend/upload/empowering-pdf/{$car['id']}/");
        if (!is_dir($uploadPath)) {
            FileHelper::createDirectory($uploadPath);
        }
        $fileName = $empoweringName !== null ? $empoweringName : date('YmdHis') . "-car_{$car['id']}.pdf";
        $backgroundImage = Yii::getAlias("@api/web/images/{$car['company_id']}-pdf-img.png");
        if (!empty($car['company_id'])) {
            switch ($car['company_id']) {
                case 1:
                    $styleEmpowering = ['#5F7423']; //color
                    break;
                case 2:
                    $styleEmpowering = ['#E093CA'];  //color
                    break;
                default:
                    $styleEmpowering = ['black']; //color
                    break;
            }
        }
        User::setUsers(true);
        $mpdf = new Mpdf(['tempDir' => Yii::getAlias('@backend/runtime')]);
        $lastRegistrationNumber = PvRegister::find()
            ->where(['company_id' => $car->company_id])
            ->orderBy(['added' => SORT_DESC])
            ->one();
        $mpdf->WriteHTML($this->renderPartial('empowering-pdf', [
            'car' => $car,
            'employee' => $employee,
            'backgroundImage' => $backgroundImage,
            'styleEmpowering' => $styleEmpowering,
            'regNumber' => !empty($lastRegistrationNumber) ? $lastRegistrationNumber->empowering_nr_register : 2
        ]));
        $mpdf->Output($uploadPath . $fileName, Destination::FILE);

        $subject = "{$car['company']['name']} - {$car['plate_number']} - " . Yii::t('api-auto', "Empowering");

        if (
            !empty(Yii::$app->params['erp_beneficiary_name'])
            && Yii::$app->params['erp_beneficiary_name'] === 'ghallard'
        ) {
            $ccRecipients = [];
            $messages = Yii::$app->mailer->compose('@api/views/mail/generate-empowering-html', [
                'employeeName' => $employee->fullName(),
            ])
                ->setFrom('econfaire@ghallard.ro')
                ->setTo([$employee->email,  $holder['email']])
                ->attach($uploadPath . $fileName)
                ->setSubject($subject);
            foreach ($adminsToNotify as $adminToNotify) {
                $ccRecipients[] = $adminToNotify;
            }
            $messages->setCc($ccRecipients)->send();
        } else {
            $sendEmail = new SendSharePointMailHelper();
            $sendEmail->subject = $subject;
            $sendEmail->content = [
                "contentType" => "html",
                "content" => $this->renderPartial('@api/views/mail/generate-empowering-html', [
                    'employeeName' => $employee->fullName(),
                ]),
            ];

            $sendEmail->toRecipients = [
                [
                    "emailAddress" => [
                        "name" => $employee->fullName(),
                        "address" => $employee->email
                    ]
                ]
            ];

            $sendEmail->ccRecipients[] = [
                "emailAddress" => [
                    "name" => $holder->fullName(),
                    "address" => $holder['email']
                ]
            ];

            foreach ($adminsToNotify as $adminToNotify) {
                $sendEmail->ccRecipients[] = [
                    "emailAddress" => [
                        "name" => (User::find()->where('email = :email', [':email' => $adminToNotify])->one())->fullName(),
                        "address" => $adminToNotify
                    ]
                ];
            }

            $sendEmail->attachments = [
                [
                    "@odata.type" => "#microsoft.graph.fileAttachment",
                    "name" => $this->pvFileName,
                    "contentType" => BaseFileHelper::getMimeType($uploadPath . $fileName),
                    "contentBytes" => chunk_split(base64_encode(file_get_contents($uploadPath . $fileName))),
                ],
            ];
            $sendEmail->sendEmail();
        }
        return true;
    }
}