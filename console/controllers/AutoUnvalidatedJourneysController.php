<?php

namespace console\controllers;

use api\models\User;
use backend\modules\auto\models\Car;
use backend\modules\auto\models\Journey;
use backend\modules\hr\models\Employee;
use Exception;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class AutoUnvalidatedJourneysController extends Controller
{
    public function actionIndex()
    {
        ini_set("memory_limit", "-1");
        set_time_limit(0);
        echo "Script run at " . date("Y-m-d-h:m:s") . "\n";
        $this->setViewPath('@app/mail');
        $autoJourneysToNotify = Car::getReceiversEmailsAutoJourneys();

        try {
            $unvalidatedJourneys = [];
            $lastSunday = date('Y-m-d H:i:s', strtotime('last Sunday'));
            $twoWeeksAgo = date('Y-m-d H:i:s', strtotime('-2 weeks', strtotime($lastSunday)));
            $journeys = Journey::find()
                ->where(['status' => 0, 'deleted' => 0, 'supplementary' => 0])
                ->andWhere([
                    'IN', 'user_id',
                    Employee::find()->select('user_id')
                        ->where(['IN', 'company_id', Yii::$app->params['companyIds']])
                        ->andWhere(['<>', 'user_id', Yii::$app->params['superAdmin']])
                ])->andWhere(['BETWEEN', 'started', $twoWeeksAgo, $lastSunday])
                ->all();
            foreach ($journeys as $journey) {
                $plateNumber = '';
                $car = Car::findOne($journey->car_id);
                if ($car) {
                    $plateNumber = $car->plate_number;
                }
                $unvalidatedJourneys[$journey->user_id]['count'] = isset($unvalidatedJourneys[$journey->user_id]['count']) ? $unvalidatedJourneys[$journey->user_id]['count'] + 1 : 1;
                $unvalidatedJourneys[$journey->user_id]['plate_number'] = $plateNumber;
                $unvalidatedJourneys[$journey->user_id]['user'] = $journey->user;
            }
            if (!empty($unvalidatedJourneys)) {
                foreach ($autoJourneysToNotify as $autoJourneysToNotifyEmail) {
                    $autoJourneysToNotifyEmail = trim($autoJourneysToNotifyEmail);
                    echo "\nWill send notification to '{$autoJourneysToNotifyEmail}'\n";
                    Yii::info("\n" . Yii::t('cmd-auto', "Will send notification to {email}", ['email' => $autoJourneysToNotifyEmail]));
                    Journey::sendEmailForUnvalidatedJourneys($autoJourneysToNotifyEmail, $unvalidatedJourneys);
                    Yii::info("\n" . Yii::t('cmd-auto', "EMAIL SENT"));
                    echo "\nEMAIL SENT\n";
                }
            }
        } catch (Exception $exc) {
            echo("\n {$exc->getMessage()} {$exc->getLine()}");
            Yii::error("\n {$exc->getMessage()} {$exc->getLine()}");
            return ExitCode::SOFTWARE;
        }
    }
}
