<?php

namespace console\controllers;

use api\models\Project;
use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use backend\modules\adm\models\UserSignature;
use backend\modules\auto\models\Car;
use backend\modules\auto\models\Roadmap;
use common\components\SendSharePointMailHelper;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Exception;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\BaseFileHelper;
use yii\helpers\FileHelper;

class AutoSendRoadmapController extends Controller
{
    function actionIndex()
    {
        echo "Script run at " . date("Y-m-d-h:m:s") . "\n";
        $this->setViewPath('@app/mail');

        try {
            $cars = Car::find()->where('deleted = 0')->asArray()->all();
            foreach ($cars as $car) {
                $year = date('Y');

                $lastMonth = date('m', strtotime('-1 month'));
                $lastMonth = $lastMonth < 10 ? '0' . $lastMonth : $lastMonth;
                if ($lastMonth == 1) {
                    $year = $year - 1;
                    $lastMonth = 12;
                }
                $model = Roadmap::find()
                    ->where('car_id = :car_id', [':car_id' => $car['id']])
                    ->andWhere('deleted = 0')
                    ->andWhere('year = :year', [':year' => $year])
                    ->andWhere('month = :month', [':month' => $lastMonth])
                    ->one();
                if ($model === null) {
                    continue;
                }
                $carCompany = Car::find()->where(['id' => $model->car_id])->with('carCompany')->one();
                $unit = '-';
                if (!empty($carCompany) && !empty($carCompany['carCompany'])) {
                    $unit = $carCompany['carCompany']['name'];
                }
                $receipts = Roadmap::getReceipts($model->id);
                $journeys = Roadmap::getRoadMapJourney($model->id);
                $userSignature = null;
                if ($model->deductibility != 100) {
                    $userSignature = UserSignature::find()
                        ->where('user_id = :user_id AND deleted = 0', [':user_id' => $model->holder_id])
                        ->orderBy('id DESC')
                        ->one();
                }

                $mpdf = new Mpdf(['tempDir' => Yii::getAlias('@backend/runtime')]);
                $mpdf->WriteHTML(Yii::$app->controller->renderPartial('@backend/modules/auto/views/roadmap/pdf', [
                    'id' => $model->id,
                    'car' => $car,
                    'unit' => $unit,
                    'receipts' => $receipts,
                    'roadMapJourneys' => $journeys,
                    'model' => $model,
                    'signature' => $userSignature == null ? null : $userSignature->signature,
                ]));
                $path = Yii::getAlias("@backend/upload/roadmap-pdf/");
                if (!is_dir($path)) {
                    FileHelper::createDirectory($path);
                }
                $name = date('YmdHis') . "-roadmap_{$model->id}.pdf";
                $mpdf->Output($path . $name, Destination::FILE);
                if ($model->deductibility == 100) {
                    $emailToRecipients = User::find()->where('id = :id', [':id' => $model->holder_id])->one()['email'];
                    $this->sendRoadmapEmail($emailToRecipients, $path, $name, $car['plate_number']);
                }
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
    public function sendRoadmapEmail($emailToRecipients, $uploadPath, $fileName = 'roadmap.pdf', $plateNumber)
    {
        $lastMonthName = date('F', strtotime('-1 month'));
        $autoAdminEmailsToNotify = Car::getReceiversEmailsCarParkAdmin();
        $user = User::find()->where('email = :email', [':email' => $emailToRecipients])->one();
        if (empty($user)) {
            Yii::error("\n" . Yii::t('cmd-auto', "User with email {emailToRecipients} not found", ['emailToRecipients' => $emailToRecipients]));
        } else {
            if (
                !empty(Yii::$app->params['erp_beneficiary_name'])
                && Yii::$app->params['erp_beneficiary_name'] === 'ghallard'
            ) {
                $ccRecipients = [];
                $email = Yii::$app->mailer->compose('@app/mail/auto-send-roadmap-html', [
                    'user' => $user,
                    'lastMonthName' => $lastMonthName,
                    'plateNumber' => $plateNumber
                ])
                    ->setFrom('econfaire@ghallard.ro')
                    ->setTo($emailToRecipients)
                    ->setSubject(Yii::t('cmd-auto', "NEXUS - Locations notification"));
                foreach ($autoAdminEmailsToNotify as $autoAdminEmail) {
                    $ccRecipients[] = $autoAdminEmail;
                }
                    $email->setCc($ccRecipients)
                        ->attach($uploadPath . $fileName)
                        ->send();
            } else {
                $sendEmail = new SendSharePointMailHelper();
                $sendEmail->subject = Yii::t('cmd-auto', "Roadmap") . " " . Yii::t('cmd-auto', "for") . " " . Yii::t('cmd-auto', "the month") . " " . Yii::t('cmd-auto', "{$lastMonthName}") . " - " . $plateNumber;
                $sendEmail->content = [
                    "contentType" => "html",
                    "content" => $this->renderPartial('auto-send-roadmap-html', [
                        'user' => $user,
                        'lastMonthName' => $lastMonthName,
                        'plateNumber' => $plateNumber
                    ])
                ];
                $sendEmail->toRecipients = [
                    [
                        "emailAddress" => [
                            "name" => $user->fullName(),
                            "address" => $emailToRecipients,
                        ]
                    ]
                ];

                $sendEmail->attachments = [
                    [
                        "@odata.type" => "#microsoft.graph.fileAttachment",
                        "name" => $fileName,
                        "contentType" => BaseFileHelper::getMimeType($uploadPath . $fileName),
                        "contentBytes" => chunk_split(base64_encode(file_get_contents($uploadPath . $fileName))),
                    ],
                ];
                foreach ($autoAdminEmailsToNotify as $autoAdminEmail) {
                    $sendEmail->ccRecipients[] = [
                        "emailAddress" => [
                            "name" => (User::find()->where('email = :email', [':email' => $autoAdminEmail])->one())->fullName(),
                            "address" => $autoAdminEmail
                        ]
                    ];
                }

                $sendEmail->sendEmail();
            }
            Yii::info("\n" . Yii::t('cmd-auto', "Successfully sent the mail notification"));
        }
    }
}