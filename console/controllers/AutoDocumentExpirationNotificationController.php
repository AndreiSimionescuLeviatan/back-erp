<?php

namespace console\controllers;

use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use backend\modules\auto\models\Car;
use backend\modules\auto\models\Event;
use common\components\SendSharePointMailHelper;
use DateTime;
use Exception;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\web\BadRequestHttpException;

class AutoDocumentExpirationNotificationController extends Controller
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
        echo("\n" . Yii::t('cmd-auto', "Document expiration notification cron service is running..." . "\n"));
        Yii::info("\n" . Yii::t('cmd-auto', "Document expiration notification cron service is running..."), 'autoDocumentExpirationNotification');
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
        $intervals = [1, 2, 3, 4, 5, 6, 7, 14, 28];

        try {
            $cars = Car::find()->where('deleted = 0')->with('carDocuments', 'user', 'holder')->asArray()->all();
            foreach ($cars as $car) {
                $hasAnyDocument = false;
                $now = new DateTime(date('Y-m-d'));
                //check if we have documents for this car
                $carDocuments = $car['carDocuments'];
                $user = $car['user'];
                $holder = $car['holder'];

                foreach ($documentsToNotify as $documentName => $dbColumnName) {
                    $documentsExpirationDate = new DateTime($carDocuments[$dbColumnName]);
                    $document = explode('_', $documentName)[0];
                    foreach ($intervals as $interval) {
                        if ($now <= $documentsExpirationDate && $now->diff($documentsExpirationDate)->days == $interval) {
                            Event::checkForEventAndCreateOrUpdateEvent(
                                [
                                    'searchAttributes' => [
                                        'car_id' => $car['id'],
                                        'event_type' => Event::EXPIRED_CAR_DOCUMENT_DATE,
                                        'deleted' => 0
                                    ],
                                    'createAttributes' => [
                                        'car_id' => $car['id'],
                                        'event_type' => Event::EXPIRED_CAR_DOCUMENT_DATE,
                                        'deleted' => 0
                                    ],
                                    'updateAttributes' => [
                                        'car_id' => $car['id'],
                                    ]
                                ],
                                [
                                    'searchAttributes' => [
                                        'car_id' => $car['id'],
                                        'plate_number' => $car['plate_number'],
                                        'document_type' => $document,
                                    ],
                                    'createAttributes' => [
                                        'car_id' => $car['id'],
                                        'document_type' => $document,
                                        'plate_number' => $car['plate_number'],
                                        'document_valability_date' => $carDocuments[$dbColumnName]
                                    ],
                                    'updateAttributes' => [
                                        'document_valability_date' => $carDocuments[$dbColumnName]
                                    ]
                                ]
                            );
                        }
                    }
                }

                //if we don't find any line in car_document table inform the car park admin
                if (empty($carDocuments)) {
                    $notificationMsg = Yii::t('cmd-auto', "For car <b>{plateNumber} no document was found</b>. Please verify!", ['plateNumber' => $car['plate_number']]);
                    echo("\n" . $notificationMsg);
                    Yii::info("\n" . $notificationMsg, 'autoDocumentExpirationNotification');
                    continue;
                }

                //check to see is any date is set for the car
                foreach ($documentsToNotify as $dbColumnName) {
                    if ($hasAnyDocument)
                        continue;
                    if (!empty($carDocuments[$dbColumnName])) {
                        $hasAnyDocument = true;
                    }
                }

                //if no valid expiration date is set inform the admin
                if (!$hasAnyDocument) {
                    $notificationMsg = Yii::t('cmd-auto', "For car {plateNumber} no valid expiration date was set for any document. Please verify!", ['plateNumber' => $car['plate_number']]);
                    echo("\n" . $notificationMsg);
                    Yii::info("\n" . $notificationMsg, 'autoDocumentExpirationNotification');
                    continue;
                }

                foreach ($documentsToNotify as $documentName => $dbColumnName) {
                    $documentsExpirationDate = new DateTime($carDocuments[$dbColumnName]);

                    if ((int)$carDocuments['casco_needed'] === 0 && $documentName === 'Casco') {
                        continue;
                    }

                    if (empty($carDocuments[$dbColumnName])) {
                        $notificationMsg = Yii::t('cmd-auto', "For car {plateNumber} no valid expiration was set for {documentName}. Please verify!", [
                            'plateNumber' => $car['plate_number'],
                            'documentName' => $documentName,
                        ]);
                        echo("\n" . $notificationMsg);
                        Yii::info("\n" . $notificationMsg, 'autoDocumentExpirationNotification');
                        continue;
                    }

                    $message = Yii::t('cmd-auto', "For car {plateNumber} {documentName} will expire",
                        [
                            'plateNumber' => $car['plate_number'],
                            'documentName' => $documentName
                        ]);

                    foreach ($intervals as $interval) {
                        if ($now <= $documentsExpirationDate && $now->diff($documentsExpirationDate)->days == $interval) {
                            $adminNotification[] = $message .= $now->diff($documentsExpirationDate)->days == 0 ?
                                Yii::t('cmd-auto', " today") :
                                Yii::t('cmd-auto', " in {daysCount} days", ['daysCount' => $now->diff($documentsExpirationDate)->days]);

                            //check if the car has holder or user set
                            if (!empty($user) || !empty($holder)) {
                                //check if user_id = holder_id
                                if ($car['user_id'] === $car['holder_id']) {
                                    if (!empty($holder['email']) && !empty($user['email'])) {
                                        $userNotification[$user['email']][] = $message;
                                        echo("\n" . $message);
                                    } elseif (!empty($holder['email']) && empty($user['email'])) {
                                        $holderNotification[$holder['email']][] = $message;
                                        echo("\n" . $message);
                                    } elseif (!empty($user['email'])) {
                                        $userNotification[$user['email']] = $message;
                                        echo("\n" . $message);
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
            }

            if (empty($holderNotification) && empty($userNotification) && empty($adminNotification)) {
                echo("\n No notification to send\n");
                Yii::info("\n" . Yii::t('cmd-auto', "No notification to send"), 'autoDocumentExpirationNotification');
                return ExitCode::OK;
            }

            foreach ($holderNotification as $emailHolder => $contentHolder) {
                Yii::info("\n" . Yii::t('cmd-auto', "Will send notification to {emailHolder}", ['emailHolder' => $emailHolder]), 'autoDocumentExpirationNotification');
                $this->sendDocumentExpirationNotification($contentHolder, $emailHolder);
                Yii::info("\n" . Yii::t('cmd-auto', "EMAIL SENT"), 'autoDocumentExpirationNotification');
            }
            foreach ($userNotification as $userEmail => $contentUser) {
                Yii::info("\n" . Yii::t('cmd-auto', "Will send notification to {emailHolder}", ['emailHolder' => $userEmail]), 'autoDocumentExpirationNotification');
                $this->sendDocumentExpirationNotification($contentUser, $userEmail);
                Yii::info("\n" . Yii::t('cmd-auto', "EMAIL SENT"), 'autoDocumentExpirationNotification');
            }
            foreach ($autoAdminEmailsToNotify as $autoAdminEmail) {
                $autoAdminEmail = trim($autoAdminEmail);
                echo "\nWill send notification to '{$autoAdminEmail}'\n";
                Yii::info("\n" . Yii::t('cmd-auto', "Will send notification to {emailHolder}", ['emailHolder' => $autoAdminEmail]), 'autoDocumentExpirationNotification');
                $this->sendDocumentExpirationNotification($adminNotification, $autoAdminEmail);
                Yii::info("\n" . Yii::t('cmd-auto', "EMAIL SENT"), 'autoDocumentExpirationNotification');
                echo "\nEMAIL SENT\n";
            }
            echo("\nALL NOTIFICATION SENT\n");
            Yii::info("\n" . Yii::t('cmd-auto', "ALL NOTIFICATION SENT"), 'autoDocumentExpirationNotification');
            return ExitCode::OK;
        } catch (Exception $exc) {
            echo("\n {$exc->getMessage()} {$exc->getLine()}");
            Yii::error("\n {$exc->getMessage()} {$exc->getLine()}", 'autoDocumentExpirationNotification');
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
            Yii::error("\n" . Yii::t('cmd-auto', "User with email {emailToRecipients} not found", ['emailToRecipients' => $emailToRecipients]), 'autoDocumentExpirationNotification');
        } else {
            if (
                !empty(Yii::$app->params['erp_beneficiary_name'])
                && Yii::$app->params['erp_beneficiary_name'] === 'ghallard'
            ) {
                Yii::$app->mailer->compose('@app/mail/auto-documents-expiration-notification-html', [
                    'documentExpire' => $notificationContent,
                    'user' => $user,
                ])
                    ->setFrom('econfaire@ghallard.ro')
                    ->setTo($emailToRecipients)
                    ->setSubject(Yii::t('cmd-auto', "Documents expiration notification"))
                    ->send();
            } else {
                $mailBody = $this->renderPartial('auto-documents-expiration-notification-html', [
                    'documentExpire' => $notificationContent,
                    'user' => $user,
                ]);

                $sendEmail = new SendSharePointMailHelper();
                $sendEmail->subject = Yii::t('cmd-auto', "Documents expiration notification");
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

            Yii::info("\n" . Yii::t('cmd-auto', "Successfully sent the mail notification"), 'autoDocumentExpirationNotification');
        }
    }
}