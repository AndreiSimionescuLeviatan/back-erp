<?php

namespace api\controllers;

use api\models\Device;
use backend\modules\pmp\models\ProductVersionEnvironment;
use Yii;

/**
 * User controller
 * @deprecated on Revit Andrei renounced to this action since 13/10/2022
 */
class DeviceController extends RestController
{
    public $modelClass = 'api\models\Device';

    /**
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();

        // disable the "delete" and "create" actions
        unset($actions['delete']);

        return $actions;
    }

    /**
     * The device registration action
     *
     * @return array
     * @deprecated on Revit Andrei renounced to this action since 13/10/2022
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
     * @deprecated on Revit Andrei renounced to this action since 13/10/2022
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
        $this->return['version'] = $device->current_version;
        $message = Yii::t('app', 'Successfully authenticated your device');
        return $this->prepareResponse($message);
    }

    /**
     * @return array|mixed
     * @deprecated on Revit Andrei renounced to this action since 13/10/2022
     */
    public function actionKeepAlive()
    {
        $post = Yii::$app->request->post();

        try {
            $device = Device::auth($post, 'token');
        } catch (\Exception $exc) {
            return $this->prepareResponse($exc->getMessage(), $exc->getCode());
        }

        $this->return['version'] = $device->current_version;
        $message = Yii::t('app', 'Successfully updated your keep alive status');
        return $this->prepareResponse($message);
    }

    public function actionDownload()
    {
        $post = Yii::$app->request->post();

        try {
            $device = Device::auth($post, 'token', false);
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
     * @deprecated on Revit Andrei renounced to this action since 13/10/2022
     */
    public function actionLastVersion()
    {
        $post = Yii::$app->request->post();

        try {
            $device = Device::auth($post, 'token', false);
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
}
