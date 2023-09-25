<?php

namespace console\controllers;

use backend\modules\auto\models\Journey;
use backend\modules\auto\models\Location;
use yii\base\Controller;
use yii\console\ExitCode;

class AutoSaveVisitsController extends Controller
{
    public function actionIndex()
    {
        Journey::setVisits();
        Journey::setVisits('stop');
        for ($i = 0; $i <= count(Journey::$visitsStart); $i++) {
            if (isset(Journey::$visitsStart[$i]) && isset(Journey::$visitsStop[$i])) {
                $keyStart = array_search(Journey::$visitsStart[$i], Journey::$visitsStart);
                $keyStop = array_search(Journey::$visitsStop[$i], Journey::$visitsStop);
                if ($keyStart == $keyStop) {
                    $location = Location::findOneByAttributes([
                        'id' => $keyStop
                    ]);
                    if ($location !== null) {
                        $location->visits = (int)Journey::$visitsStart[$i] + (int)Journey::$visitsStop[$i];
                        $location->save();
                    }
                }
            }
        }
        echo "\nVisits have been set\n";
        return ExitCode::OK;
    }
}