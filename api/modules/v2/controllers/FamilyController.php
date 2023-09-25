<?php

namespace api\modules\v2\controllers;

use api\modules\v2\models\FamilyPlacement;

class FamilyController extends RestV2Controller
{
    public $modelClass = 'api\modules\v2\models\FamilyPlacement';

    public function actionPlacements()
    {
        $placements = FamilyPlacement::find()->all();

        $data = [];
        foreach ($placements as $placement)
        {
            $data[$placement['id']] = $placement['name'];
        }

        return $data;
    }

}