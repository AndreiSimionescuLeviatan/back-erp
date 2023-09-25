<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Car;
use backend\modules\auto\models\Journey;
use Yii;

/**
 * V1 of Accessory controller
 */
class AccessoryController extends RestV1Controller
{
    public $modelClass = 'api\modules\v1\models\Accessory';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    /**
     * @param $car_id
     * @return array
     */
    public function actionIndex($car_id)
    {
        $carModel = Car::find()
            ->where(['id' => $car_id])
            ->with('carAccessories', 'carAccessories.accessory')
            ->asArray()
            ->one();
        if (empty($carModel['carAccessories'])) {
            Yii::$app->response->statusCode = 404;
            $this->return['status'] = 404;
            $this->return['message'] = Yii::t('app', 'No accessory available.');
            return $this->return;
        }

        $accessories = [];
        foreach ($carModel['carAccessories'] as $accessory) {
            $accessories[] = [
                'id' => $accessory['id'],
                'name' => !empty($accessory['accessory']) ? $accessory['accessory']['name'] : '-',
                'quantity' => $accessory['accessory_qty'],
                'measure_unit' => $accessory['accessory']['measure_unit_name'],
                'observation' => $accessory['observation'],
                'expiration_date' => $accessory['expiration_date'] !== null ? Journey::formatDate($accessory['expiration_date']) : '',
                'color_badge' => $accessory['expiration_date'] !== null ? Journey::checkBadgeColor($accessory['expiration_date']) : '',
            ];
        }
        $this->return['status'] = 200;
        $this->return['carManager'] = Yii::$app->user->can('/auto/accessory/update');
        $this->return['accessories'] = $accessories;
        $message = Yii::t('app', 'Successfully sent the accessories list');
        return $this->prepareResponse($message);
    }
}