<?php

namespace console\controllers;

use backend\modules\adm\models\User;
use backend\modules\auto\models\Location;
use backend\modules\auto\models\LocationCircle;
use Yii;
use yii\base\Controller;
use yii\console\ExitCode;
use yii\db\Exception;

class AutoSetLocationCircleController extends Controller
{
    /**
     * @throws Exception
     */
    public function actionIndex()
    {
        $superAdmin = User::getSuperAdmin();

        $locations = Location::findAllByAttributes([
            'deleted' => 0
        ]);
        foreach ($locations as $location) {
            $attributes = [
                'location_id' => $location->id,
                'deleted' => 0
            ];
            try {
                $locationCircle = LocationCircle::getByAttributes($attributes,
                    $attributes + [
                        'radius' => LocationCircle::DEFAULT_RADIUS,
                        'added' => date('Y-m-d H:i:s'),
                        'added_by' => $superAdmin]);
            } catch (\Exception $exc) {
                echo "\nLocation circle could not been set\n";
            }
        }
        echo "\nLocation circles have been set\n";
        return ExitCode::OK;
    }
}