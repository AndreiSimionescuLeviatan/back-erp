<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Device;
use api\modules\v1\models\Employee;
use backend\modules\hr\models\WorkLocation;
use backend\modules\pmp\models\ProductVersionEnvironment;
use Yii;
use yii\helpers\Url;

/**
 * V1 of Device controller
 */
class DeviceController extends RestV1Controller
{
    public $modelClass = 'api\modules\v1\models\Device';

    public function actions()
    {
        $actions = parent::actions();
        return $actions;
    }

    /**
     * The device registration action
     *
     * @return array
     */

    public function actionRegister()
    {
        $post = Yii::$app->request->post();

        try {
            $device = Device::register($post);
        } catch (\Exception $exc) {
            return $this->prepareResponse($exc->getMessage(), $exc->getCode());
        }

        if ($device === null) {
            $message = Yii::t('app', 'Could not register, your device not exist.');
            return $this->prepareResponse($message, 404);
        }

        $this->return['uuid'] = $device->uuid;
        $message = Yii::t('app', 'Successfully registered your device');
        return $this->prepareResponse($message);
    }

    /**
     * @throws \yii\db\StaleObjectException
     */
    public function actionAuth()
    {
        $post = Yii::$app->request->post();

        try {
            $device = Device::auth($post);
        } catch (\Exception $exc) {
            return $this->prepareResponse($exc->getMessage(), $exc->getCode());
        }

        $this->return['token'] = $device->token;
        $this->return['version'] = $device->product_version;
        $this->return['logo'] = Device::getLogoApk($device->uuid);
        $message = Yii::t('app', 'Successfully authenticated your device');
        return $this->prepareResponse($message);
    }

    /**
     * @return array|mixed
     */
    public function actionKeepAlive()
    {
        $post = Yii::$app->request->post();

        try {
            $device = Device::auth($post, 'token');
        } catch (\Exception $exc) {
            return $this->prepareResponse($exc->getMessage(), $exc->getCode());
        }

        $this->return['version'] = $device->product_version;

        $locationForHrApp = null;
        if(!empty($post['device_location'])) {
            if (
                !empty($post['device_location']['latitude'])
                && !empty($post['device_location']['longitude'])
                && !empty($post['user_id'])
            ) {
                try {
                    Device::createDeviceLocationTableIfNotExist();
                } catch (\Exception $exc) {
                    return $this->prepareResponse($exc->getMessage(), $exc->getCode());
                }
                $employeeId = Employee::getEmployeeId($post['user_id']);
                $locationDetails = WorkLocation::getWorkLocationByCoordinatesForEmployeeId([
                    'latitude' => $post['device_location']['latitude'],
                    'longitude' => $post['device_location']['longitude'],
                    'employee_id' => $employeeId
                ]);

                if (!empty($locationDetails)) {
                    Device::insertDataIntoDeviceLocationTable([
                        "device_id" => $device->id,
                        "location_id" => $locationDetails->id,
                        "latitude" => $post['device_location']['latitude'],
                        "longitude" => $post['device_location']['longitude'],
                        "location_name" => $locationDetails->name,
                        "location_address" => $locationDetails->address
                    ]);
                    $locationForHrApp = [
                        "name" => $locationDetails->name,
                        "address" => $locationDetails->address
                    ];
                }
            }
        }


        $this->return['location_details'] = $locationForHrApp;

        $message = Yii::t('app', 'Successfully updated your keep alive status');
        return $this->prepareResponse($message);
    }

    /**
     * @return array|mixed
     */
    public function actionDownload()
    {
        $post = Yii::$app->request->post();

        try {
            $device = Device::auth($post, 'token');
        } catch (\Exception $exc) {
            return $this->prepareResponse($exc->getMessage(), $exc->getCode());
        }

        if (empty($post['product_version'])) {
            return $this->prepareResponse(Yii::t('app', 'No product version received.'), 400);
        }

        if (empty($post['product_type'])) {
            return $this->prepareResponse(Yii::t('app', 'No product type received.'), 400);
        }

        try {
            $download = $device->getProductDownloadDetails($post['product_version'], $post['product_type']);
        } catch (\Exception $exc) {
            return $this->prepareResponse($exc->getMessage(), $exc->getCode());
        }

        $this->return['url'] = $download['url'];
        $this->return['hash'] = $download['hash'];

        $message = Yii::t('app', 'Successfully sent the download details');
        return $this->prepareResponse($message);
    }

    /**
     * @return array|mixed
     */
    public function actionLastVersion()
    {
        $post = Yii::$app->request->post();

        try {
            $device = Device::auth($post, 'token');
        } catch (\Exception $exc) {
            return $this->prepareResponse($exc->getMessage(), $exc->getCode());
        }

        if (empty($post['product_type'])) {
            $prodVersion = $device->product_version;
        } else {
            $prodEnvVersion = ProductVersionEnvironment::find()
                ->where("environment_id = {$device->environment_id} AND product_type_id = :product_type_id ", ["product_type_id" => $post['product_type']])
                ->orderBy(['id' => SORT_DESC])
                ->one();
            if ($prodEnvVersion === null || $prodEnvVersion->productHistory === null || empty($prodEnvVersion->productHistory->version)) {
                return $this->prepareResponse(Yii::t('app', 'Unable to retrieve product version. Please contact an administrator!'), 404);
            }
            $prodVersion = $prodEnvVersion->productHistory->version;
        }

        $this->return['version'] = $prodVersion;
        $message = Yii::t('app', 'Successfully sent the product version');

        return $this->prepareResponse($message);
    }

    public function actionTimestampDevice()
    {
        $post = Yii::$app->request->post();

        $deviceTimestamp = $post['device_timestamp'];
        $isCorrectTimestamp = 0;

        if(is_numeric($deviceTimestamp)) {
            if($deviceTimestamp > strtotime('now -5 minutes') && $deviceTimestamp < strtotime('now +5 minutes')) {
                $isCorrectTimestamp = 1;
            }
        }

        $this->return['is_correct_timestamp'] = $isCorrectTimestamp;
        $message = Yii::t('app', 'Successfully verified the device timestamp');

        return $this->prepareResponse($message);


    }
}