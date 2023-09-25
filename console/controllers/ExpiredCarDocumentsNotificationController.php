<?php

namespace console\controllers;

use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use backend\modules\auto\models\Car;
use backend\modules\auto\models\Event;
use common\components\SendSharePointMailHelper;
use DateTime;
use Exception;
use Mpdf\Tag\U;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\web\BadRequestHttpException;

class ExpiredCarDocumentsNotificationController extends Controller
{

    /**
     * Notification for document expiration
     * @return int
     * @throws Exception
     * @author Daniel L.
     * @since 05.05.2022
     */
    public function actionIndex()
    {
        $this->setViewPath('@app/mail');
        echo("\n" . Yii::t('cmd-auto', "Document expiration notification cron service is running..."));
        Yii::info("\n" . Yii::t('cmd-auto', "Document expiration notification cron service is running..."), 'expiredCarDocumentsNotification');
        $carParkAdminSetting = Settings::find()->where(['name' => 'CAR_PARK_ADMIN'])->asArray()->one();
        $autoAdminEmailsToNotify = !empty($carParkAdminSetting) && !empty($carParkAdminSetting['value']) ?
            explode(',', $carParkAdminSetting['value']) :
            explode(',', Yii::$app->params['carParkAdmin']);
        $documentsToNotify = [
            'RCA' => 'rca_valid_until',
            'Casco' => 'casco_valid_until',
            'ITP' => 'itp_valid_until',
            'Rovinieta' => 'vignette_valid_until'
        ];

        $adminNotification = [];
        $holderNotification = [];
        $userNotification = [];

        try {
            $cars = Car::find()->where('deleted = 0')->with('carDocuments', 'user', 'holder')->asArray()->all();
            foreach ($cars as $car) {
                $now = new DateTime(date('Y-m-d'));
                //check if we have documents for this car
                $carDocuments = $car['carDocuments'];
                $user = $car['user'];
                $holder = $car['holder'];

                //if we don't find any document in car_document table log to file
                if (empty($carDocuments)) {
                    $notificationMsg = Yii::t('cmd-auto', "For car <b>{plateNumber} no document was found</b>. Please verify!", ['plateNumber' => $car['plate_number']]);
                    echo("\n" . $notificationMsg);
                    Yii::warning("\n" . $notificationMsg, 'expiredCarDocumentsNotification');
                    continue;
                }

                //check to see if any document has expiration date before current date
                foreach ($documentsToNotify as $docKey => $dbColumnName) {
                    $documentsExpirationDate = new DateTime($carDocuments[$dbColumnName]);
                    if (!empty($carDocuments[$dbColumnName]) && $carDocuments[$dbColumnName] < date('Y-m-d')) {
                        $notificationMsg = Yii::t('cmd-auto', "For car <b>{plateNumber}</b> the <b>{docName}</b> is expired for <b>{daysCount} days</b>!", [
                            'plateNumber' => $car['plate_number'],
                            'docName' => $docKey,
                            'daysCount' => $now->diff($documentsExpirationDate)->days
                        ]);
                        echo("\n" . $notificationMsg);
                        Yii::info("\n" . $notificationMsg, 'expiredCarDocumentsNotification');
                        $adminNotification[] = $message = $notificationMsg;

                        Event::checkForEventAndCreateOrUpdateEvent(
                            [
                                'searchAttributes' => [
                                    'car_id' => $carDocuments['car_id'],
                                    'event_type' => Event::EXPIRING_CAR_DOCUMENT_DATE
                                ],
                                'createAttributes' => [
                                    'car_id' => $carDocuments['car_id'],
                                    'event_type' => Event::EXPIRING_CAR_DOCUMENT_DATE,
                                    'deleted' => 0
                                ],
                                'updateAttributes' => [
                                    'car_id' => $carDocuments['car_id'],
                                ]
                            ],
                            [
                                'searchAttributes' => [
                                    'car_id' => $carDocuments['car_id'],
                                    'document_type' => explode('_', $docKey)[0],
                                ],
                                'createAttributes' => [
                                    'car_id' => $carDocuments['car_id'],
                                    'document_type' => explode('_', $docKey)[0],
                                    'document_valability_date' => $carDocuments[$dbColumnName]
                                ],
                                'updateAttributes' => [
                                    'document_valability_date' => $carDocuments[$dbColumnName]
                                ]
                            ]
                        );

                        if (!empty($user) || !empty($holder)) {
                            //check if user_id = holder_id
                            if ($car['user_id'] === $car['holder_id']) {
                                $_logMsg = "The car holder and user is the same person, will send only one notification!";
                                echo("\n $_logMsg");
                                Yii::info("\n $_logMsg", 'expiredCarDocumentsNotification');

                                if (!empty($holder['email'])) {
                                    $holderNotification[$holder['email']][] = $message;
                                    echo("\n" . $message);
                                } else {
                                    $_logMsg = "The person who only uses the car {$car['plate_number']} is the same person but don't have an email set to send notification!";
                                    echo("\n $_logMsg");
                                    Yii::warning("\n $_logMsg", 'expiredCarDocumentsNotification');
                                }
                            } else {
                                if (!empty($holder['email']))
                                    $holderNotification[$holder['email']][] = $message;
                                echo("\n" . $message);
                                if (!empty($user['email']))
                                    $userNotification[$user['email']][] = $message;
                                echo("\n" . $message);
                            }
                        }
                    }
                }
            }

            if (empty($holderNotification) && empty($userNotification) && empty($adminNotification)) {
                echo("\n No notification to send\n");
                Yii::info("\n" . Yii::t('cmd-auto', "No notification to send"), 'expiredCarDocumentsNotification');
                return ExitCode::OK;
            }

            foreach ($holderNotification as $emailHolder => $contentHolder) {
                Yii::info("\n" . Yii::t('cmd-auto', "Will send notification to {emailHolder}", ['emailHolder' => $emailHolder]), 'expiredCarDocumentsNotification');
                $this->sendDocumentExpirationNotification($contentHolder, $emailHolder);
                Yii::info("\n" . Yii::t('cmd-auto', "EMAIL SENT"), 'expiredCarDocumentsNotification');
            }
            foreach ($userNotification as $userEmail => $contentUser) {
                Yii::info("\n" . Yii::t('cmd-auto', "Will send notification to {emailHolder}", ['emailHolder' => $userEmail]), 'expiredCarDocumentsNotification');
                $this->sendDocumentExpirationNotification($contentUser, $userEmail);
                Yii::info("\n" . Yii::t('cmd-auto', "EMAIL SENT"), 'expiredCarDocumentsNotification');
            }
            foreach ($autoAdminEmailsToNotify as $autoAdminEmail) {
                $autoAdminEmail = trim($autoAdminEmail);
                echo "\nWill send notification to '{$autoAdminEmail}'\n";
                Yii::info("\n" . Yii::t('cmd-auto', "Will send notification to {emailHolder}", ['emailHolder' => $autoAdminEmail]), 'expiredCarDocumentsNotification');
                $this->sendDocumentExpirationNotification($adminNotification, $autoAdminEmail);
                Yii::info("\n" . Yii::t('cmd-auto', "EMAIL SENT"), 'expiredCarDocumentsNotification');
                echo "\nEMAIL SENT\n";
            }
            echo("\nALL NOTIFICATION SENT\n");
            Yii::info("\n" . Yii::t('cmd-auto', "ALL NOTIFICATION SENT"), 'expiredCarDocumentsNotification');
            return ExitCode::OK;
        } catch (Exception $exc) {
            echo("\n {$exc->getMessage()} {$exc->getLine()}");
            Yii::error("\n {$exc->getMessage()} {$exc->getLine()}", 'expiredCarDocumentsNotification');
            return ExitCode::SOFTWARE;
        }
    }

    /**
     * Function to send the email notification
     * @param $notificationContent
     * @param $emailToRecipients
     * @return void
     * @throws BadRequestHttpException
     * @author Daniel L.
     * @since 10.05.2022
     */
    public function sendDocumentExpirationNotification($notificationContent, $emailToRecipients)
    {
        $user = User::find()->where('email = :email', [':email' => $emailToRecipients])->one();
        if (empty($user)) {
            Yii::error("\n" . Yii::t('cmd-auto', "User with email {emailToRecipients} not found", ['emailToRecipients' => $emailToRecipients]), 'expiredCarDocumentsNotification');
        } else {
            if (
                !empty(Yii::$app->params['erp_beneficiary_name'])
                && Yii::$app->params['erp_beneficiary_name'] === 'ghallard'
            ) {
                Yii::$app->mailer->compose('@app/mail/expired-documents-notification-html', [
                    'documentExpire' => $notificationContent,
                    'user' => $user,
                ])
                    ->setFrom('econfaire@ghallard.ro')
                    ->setTo($emailToRecipients)
                    ->setSubject(Yii::t('cmd-auto', "Car expired documents"))
                    ->send();
            } else {
                $mailBody = $this->renderPartial('expired-documents-notification-html', [
                    'documentExpire' => $notificationContent,
                    'user' => $user,
                ]);

                $sendEmail = new SendSharePointMailHelper();
                $sendEmail->subject = Yii::t('cmd-auto', "Car expired documents");
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

            Yii::info("\n" . Yii::t('cmd-auto', "Successfully sent the mail notification"), 'expiredCarDocumentsNotification');
        }
    }
}