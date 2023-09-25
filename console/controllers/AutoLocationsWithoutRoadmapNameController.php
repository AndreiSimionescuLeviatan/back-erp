<?php

namespace console\controllers;

use api\modules\v1\models\Journey;
use backend\modules\adm\models\User;
use backend\modules\auto\models\Car;
use backend\modules\auto\models\Location;
use Yii;
use yii\console\Controller;

class AutoLocationsWithoutRoadmapNameController extends Controller
{
    public function actionIndex()
    {

        $parkAdmins = Car::getReceiversEmailsCarParkAdmin();
        $locations = Location::find()
            ->distinct()
            ->select('location.name, user.first_name, user.last_name, car.plate_number')
            ->where(['location.deleted' => 0])
            ->andWhere(['or', ['name' => null], ['like', 'name', 'Hotspot']])
            ->leftJoin(Journey::tableName(), 'location.id = journey.start_hotspot_id OR location.id = journey.stop_hotspot_id')
            ->andWhere(['journey.deleted' => 0])
            ->andWhere(['journey.status' => Journey::STATUS_FOR_VALID])
            ->andWhere(['BETWEEN', 'started', date('Y-m-d H:i:s', strtotime('-1 weeks')), date('Y-m-d H:i:s', strtotime('-1 day'))])
            ->leftJoin('car', 'journey.car_id = car.id')
            ->leftJoin(User::tableName(), 'journey.user_id = user.id')
            ->asArray()
            ->all();

        if (!empty($locations)) {
            foreach ($parkAdmins as $admin) {
                $autoJourneysToNotifyEmail = trim($admin);
                echo "\nWill send notification to '{$autoJourneysToNotifyEmail}'\n";
                Yii::info("\n" . Yii::t('cmd-auto', "Will send notification to {email}", ['email' => $autoJourneysToNotifyEmail]));
                Location::sendEmailForLocationWithoutRoadmapName($autoJourneysToNotifyEmail, $locations);
                Yii::info("\n" . Yii::t('cmd-auto', "EMAIL SENT"));
                echo "\nEMAIL SENT\n";
            }
        }
    }

}