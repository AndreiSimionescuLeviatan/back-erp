<?php

namespace api\modules\v2\controllers;

use api\modules\v2\models\Speciality;

class SpecialityController extends RestV2Controller
{
    public $modelClass = 'api\modules\v2\models\Speciality';

    public function actionSpeciality()
    {
        $specialities = Speciality::find()->all();

        $data = [];
        foreach ($specialities as $speciality) {
            $data[$speciality['id']] = [
                'code' => $speciality['code'],
                'name' => $speciality['name']
            ];
        }

        return $data;
    }

}