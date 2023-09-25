<?php

namespace console\controllers;

use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use backend\modules\auto\models\Car;
use backend\modules\auto\models\CarConsumption;
use backend\modules\auto\models\CarKm;
use backend\modules\auto\models\Event;
use common\components\SendSharePointMailHelper;
use PHPUnit\Util\Exception;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class AutoModifyKilometersNotificationController extends Controller
{
    function actionIndex()
    {
        echo "Script run at " . date("Y-m-d-h:m:s") . "\n";
        $this->setViewPath('@app/mail');
        $carParkAdminSetting = Settings::find()->where(['name' => 'CAR_PARK_ADMIN'])->asArray()->one();
        $autoAdminEmailsToNotify = !empty($carParkAdminSetting) && !empty($carParkAdminSetting['value']) ?
            explode(',', $carParkAdminSetting['value']) :
            explode(',', Yii::$app->params['carParkAdmin']);

        try {
            $cars = Car::find()->where('deleted = 0')->asArray()->all();
            foreach ($cars as $car) {
                $year = date('Y');
                $month = date('m');

                $monthFirstDate = date('Y-m-01', strtotime("{$year}-{$month}-01 00:00:00"));
                $monthLastDate = date('Y-m-t', strtotime($year . '-' . $month . '-01'));

                $carsKm = CarKm::find()
                    ->where(['car_id' => $car['id']])
                    ->andWhere(['source' => 1])
                    ->andWhere(['BETWEEN', 'added', $monthFirstDate, $monthLastDate])->one();
                $carsConsumption = CarConsumption::find()
                    ->where(['car_id' => $car['id']])
                    ->andWhere(['source' => 1])
                    ->andWhere(['BETWEEN', 'added', $monthFirstDate, $monthLastDate])->one();

                if (!empty($carsKm) && !empty($carsConsumption)) {
                    continue;
                }

                $lastCarConsumption = CarConsumption::find()
                    ->where(['car_id' => $car['id']])
                    ->andWhere(['source' => 1])
                    ->orderBy(['added' => SORT_DESC])
                    ->one();
                Event::checkForEventAndCreateOrUpdateEvent(
                    [
                        'searchAttributes' => [
                            'car_id' => $car['id'],
                            'event_type' => Event::CAR_KM_FUEL_CONSUMPTION_MISSING
                        ],
                        'createAttributes' => [
                            'car_id' => $car['id'],
                            'event_type' => Event::CAR_KM_FUEL_CONSUMPTION_MISSING,
                            'deleted' => 0
                        ],
                        'updateAttributes' => [
                            'car_id' => $car['id'],
                        ]
                    ],
                    [
                        'searchAttributes' => [
                            'car_id' => $car['id'],
                        ],
                        'createAttributes' => [
                            'car_id' => $car['id'],
                            'last_km_fuel_consumption_updated_date' => $lastCarConsumption !== null ? $lastCarConsumption->added : null
                        ],
                        'updateAttributes' => [
                            'last_km_fuel_consumption_updated_date' => $lastCarConsumption !== null ? $lastCarConsumption->added : null
                        ]
                    ]
                );
                $isCarKm = empty($carsKm) ? true : false;
                $isCarConsumption = empty($carsConsumption) ? true : false;
                $userEmail = User::find()->where(['id' => $car['holder_id']])->one();
                $this->sendEmailForSetKm($userEmail['email'], $car['plate_number'], $isCarKm, $isCarConsumption);


            }
            foreach ($autoAdminEmailsToNotify as $autoAdminEmail) {
                $autoAdminEmail = trim($autoAdminEmail);
                echo "\nWill send notification to '{$autoAdminEmail}'\n";
                Yii::info("\n" . Yii::t('app', "Will send notification to {$autoAdminEmail}"));
                $this->sendEmailForSetKm($autoAdminEmail, $car['plate_number']);
                Yii::info("\n" . Yii::t('cmd-auto', "EMAIL SENT"));
                echo "\nEMAIL SENT\n";
            }
            echo("\nALL NOTIFICATION SENT\n");
            Yii::info("\n" . Yii::t('cmd-auto', "ALL NOTIFICATION SENT"));
            return ExitCode::OK;
        } catch (Exception $exc) {
            echo("\n {$exc->getMessage()} {$exc->getLine()}");
            Yii::error("\n {$exc->getMessage()} {$exc->getLine()}");
            return ExitCode::SOFTWARE;
        }
    }

    /**
     * @throws \yii\web\BadRequestHttpException
     */
    public function sendEmailForSetKm($emailToRecipients, $plateNumber, $isCarKm, $isCarConsumption)
    {
        $user = User::find()->where('email = :email', [':email' => $emailToRecipients])->one();
        if (empty($user)) {
            Yii::error("\n" . Yii::t('cmd-auto', "User with email {emailToRecipients} not found", ['emailToRecipients' => $emailToRecipients]));
        } else {
            if ($isCarKm && $isCarConsumption){
                $subject = Yii::t('cmd-auto', "Set kilometers and consumption");
            } else if ($isCarConsumption){
                $subject = Yii::t('cmd-auto', "Set consumption");
            } else {
                $subject = Yii::t('cmd-auto', "Set kilometers");
            }

            if (
                !empty(Yii::$app->params['erp_beneficiary_name'])
                && Yii::$app->params['erp_beneficiary_name'] === 'ghallard'
            ) {
                Yii::$app->mailer->compose('@app/mail/auto-set-car-km-expiration-notification.php', [
                    'user' => $user,
                    'plateNumber' => $plateNumber,
                    'isCarKm' => $isCarKm,
                    'isCarConsumption' => $isCarConsumption,
                ])
                    ->setFrom('econfaire@ghallard.ro')
                    ->setTo($emailToRecipients)
                    ->setSubject($subject)
                    ->send();
            } else {
                $mailBody = $this->renderPartial('auto-set-car-km-expiration-notification.php', [
                    'user' => $user,
                    'plateNumber' => $plateNumber,
                    'isCarKm' => $isCarKm,
                    'isCarConsumption' => $isCarConsumption,
                ]);

                $sendEmail = new SendSharePointMailHelper();
                $sendEmail->subject = $subject;
                $sendEmail->content = [
                    "contentType" => "html",
                    "content" => $mailBody,
                ];
                $sendEmail->toRecipients = [
                    [
                        "emailAddress" => [
                            "name" => $user->fullName(),
                            "address" => $emailToRecipients,
                        ]
                    ]
                ];
                $sendEmail->sendEmail();
            }

            Yii::info("\n" . Yii::t('cmd-auto', "Successfully sent the mail notification"));
        }
    }
}