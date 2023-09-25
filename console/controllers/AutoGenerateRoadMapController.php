<?php

namespace console\controllers;

use backend\modules\auto\models\Car;
use backend\modules\auto\models\Journey;
use common\components\SendSharePointMailHelper;
use Exception;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class AutoGenerateRoadMapController extends Controller
{
    /**
     * @return bool
     */
    public function actionIndex($year, $month)
    {
        $this->setViewPath('@app/mail');
        !isset($year) ? $year = date('Y', strtotime('last month')) : '';
        !isset($month) ? $month = date('m', strtotime('last month')) : '';

        Yii::info(Yii::t('cmd-auto', "\nGenerate Road Map list cron service is running..."), 'roadMapGenerate');

        $cars = Car::findByAttributes([
            'deleted' => 0
        ], true);

        if (empty($cars)) {
            Yii::warning("\n" . Yii::t('cmd-auto', 'No active cars found'));
            return ExitCode::DATAERR;
        }

        $timestamp = strtotime("{$year}-{$month}-01 00:00:00");
        if (!$timestamp) {
            Yii::error("\n" . Yii::t('cmd-auto', "It's not a good data type"));
            return ExitCode::DATAERR;
        }
        $monthFirstDate = date('Y-m-01', $timestamp);
        $monthLastDate = date('Y-m-t', $timestamp);
        foreach ($cars as $car) {
            Car::$carID = $car['id'];

            Journey::deleteJourneysSupplementsByCarID($year, $month);
            $car->setRoadmapStatus(0);

            $car->clearRoadmapsByMonthAndRoadmapJourneys($year, $month);

            try {
                $car->insertNewRoadmapAndNewRoadmapReceipt($year, $month);
                foreach (Car::$newRoadmap as $item) {
                    Car::increaseRandomKilometers($item, $year, $month);
                }
            } catch (Exception $exc) {
                Yii::error("\n" . $exc->getMessage());
                continue;
            }
            Car::setAllJourneys($year, $month);
            if (empty(Car::$roadmapJourneys)) {
                continue;
            }

            Car::setJourneysWithScope();
            if (empty(Car::$roadmapJourneys)) {
                continue;
            }
            Car::setJourneysWithStartAndStop();
            if (empty(Car::$roadmapJourneys)) {
                continue;
            }
            Car::setJourneysWithNoHome();
            if (empty(Car::$roadmapJourneys)) {
                continue;
            }
            Car::setJourneysInWorkInterval();
            if (empty(Car::$roadmapJourneys)) {
                continue;
            }
            Car::setJourneysWithWorkPoint();
            if (empty(Car::$roadmapJourneys)) {
                continue;
            }

            Car::getTotalKmJourneysFromRoadmap();

            Car::setJourneysInRoadmapJourneys();

        }

        $this->sendRoadMapImportNotification($monthFirstDate, $monthLastDate);
        echo "\nEMAIL SENT\n";
        return ExitCode::OK;
    }

    public function sendRoadMapImportNotification($monthFirstDate, $monthLastDate)
    {
        if (
            !empty(Yii::$app->params['erp_beneficiary_name'])
            && Yii::$app->params['erp_beneficiary_name'] === 'ghallard'
        ) {
            Yii::$app->mailer->compose('@app/mail/auto-generate-roadmap-html', [
                'monthFirstDate' => $monthFirstDate,
                'monthLastDate' => $monthLastDate,
            ])
                ->setFrom('econfaire@ghallard.ro')
                ->setTo('mihnea.gatej@leviatan.ro')
                ->setCc(["marius.postolache@leviatan.ro", "lidia.varasciuc@leviatan.ro"])
                ->setSubject(Yii::t('cmd-auto', 'RoadMap - Assigned journey to tax receipt'))
                ->send();
        } else {
            $sendEmail = new SendSharePointMailHelper();
            $sendEmail->subject = Yii::t('cmd-auto', 'RoadMap - Assigned journey to tax receipt');
            $sendEmail->content = [
                "contentType" => "html",
                "content" => $this->renderPartial('auto-generate-roadmap-html', [
                'monthFirstDate' => $monthFirstDate,
                'monthLastDate' => $monthLastDate,
            ])
            ];
            $sendEmail->toRecipients = [
                [
                    "emailAddress" => [
                        "name" => 'Alexandru Cervinschi',
                        "address" => 'alexandru.cervinschi@leviatan.ro',
                    ]
                ]
            ];
            $sendEmail->ccRecipients = [
                [
                    "emailAddress" => [
                        "name" => 'Marius Postolache',
                        "address" => "marius.postolache@leviatan.ro"
                    ]
                ],
                [
                    "emailAddress" => [
                        "name" => 'Lidia Varasciuc',
                        "address" => "lidia.varasciuc@leviatan.ro"
                    ]
                ]
            ];
            $sendEmail->sendEmail();
        }
    }
}