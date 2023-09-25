<?php

namespace console\controllers;

use backend\modules\auto\models\Car;
use yii\console\Controller;

class AutoUnlockingCarsController extends Controller
{
    public function actionIndex()
    {
        $cars = Car::findAllByAttributes([
            'status' => Car::STATUS_BEING_PROCESSED_CAR,
            'deleted' => 0,
        ]);
        foreach ($cars as $car) {
            if (strtotime($car->updated) < strtotime('-30 minutes')) {
                $car->status = Car::STATUS_AVAILABLE_CAR;
                $car->user_id = NULL;
                $car->save();
            }
        }
    }
}