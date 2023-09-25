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

class CarDocumentsMissingExpDateNotificationController extends Controller
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
        $_logMsg = Yii::t('cmd-auto', "Documents without expiration date notification cron service is running...\n");
        echo("\n" . $_logMsg);
        Yii::info("\n" . $_logMsg, 'carDocumentsWithoutExpDateNotification');
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
                $hasAnyDocument = false;
                //check if we have documents for this car
                $carDocuments = $car['carDocuments'];
                $user = $car['user'];
                $holder = $car['holder'];
                $carHasOwnerOrUser = !empty($user) || !empty($holder);
                $ownerEqualWithUser = $car['user_id'] === $car['holder_id'];


                //if we don't find any document in car_document table send notification
                if (empty($carDocuments)) {
                    $notificationMsg = Yii::t('cmd-auto', "For car <b>{plateNumber} no document was found</b>. Please verify!", ['plateNumber' => $car['plate_number']]);
                    $adminNotification[] = $notificationMsg;
                    if ($carHasOwnerOrUser) {
                        if ($ownerEqualWithUser) {
                            $holderNotification[$holder['email']][] = $notificationMsg;
                        } else {
                            if (!empty($holder['email']))
                                $holderNotification[$holder['email']][] = $notificationMsg;
                            if (!empty($user['email']))
                                $userNotification[$user['email']][] = $notificationMsg;
                        }
                    }
                    echo("\n" . $notificationMsg);
                    Yii::warning("\n" . $notificationMsg, 'carDocumentsWithoutExpDateNotification');
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

                //if no valid expiration date is set inform the user/admin
                if (!$hasAnyDocument) {
                    $notificationMsg = Yii::t('cmd-auto', "For car <b>{plateNumber} no valid expiration date was set for any document</b>. Please verify!", ['plateNumber' => $car['plate_number']]);
                    $adminNotification[] = $notificationMsg;
                    if ($carHasOwnerOrUser) {
                        if ($ownerEqualWithUser) {
                            $holderNotification[$holder['email']][] = $notificationMsg;
                        } else {
                            if (!empty($holder['email']))
                                $holderNotification[$holder['email']][] = $notificationMsg;
                            if (!empty($user['email']))
                                $userNotification[$user['email']][] = $notificationMsg;
                        }
                    }
                    echo("\n" . $notificationMsg);
                    Yii::info("\n" . $notificationMsg, 'autoDocumentExpirationNotification');
                    continue;
                }

                //check to see if any document has expiration date before current date
                foreach ($documentsToNotify as $docKey => $dbColumnName) {
                    if (empty($carDocuments[$dbColumnName])) {
                        $notificationMsg = Yii::t('cmd-auto', "For car <b>{plateNumber}</b> the document <b>{docName} don't has a valid expiration date!</b>", [
                            'plateNumber' => $car['plate_number'],
                            'docName' => $docKey,
                        ]);

                        Event::checkForEventAndCreateOrUpdateEvent(
                            [
                                'searchAttributes' => [
                                    'event_type' => Event::MISSING_VALIDITY_DATE,
                                    'car_id' => $carDocuments['car_id']
                                ],
                                'createAttributes' => [
                                    'event_type' => Event::MISSING_VALIDITY_DATE,
                                    'car_id' => $carDocuments['car_id'],
                                    'deleted' => 0
                                ],
                                'updateAttributes' => [
                                    'car_id' => $carDocuments['car_id'],
                                ]
                            ],
                            [
                                'searchAttributes' => [
                                    'car_id' => $carDocuments['car_id'],
                                    'document_type' => explode('_', $docKey)[0]
                                ],
                                'createAttributes' => [
                                    'car_id' => $carDocuments['car_id'],
                                    'document_type' => explode('_', $docKey)[0]
                                ],
                                'updateAttributes' => [
                                    'document_type' => explode('_', $docKey)[0]
                                ]
                            ]
                        );

                        echo("\n" . $notificationMsg);
                        Yii::info("\n" . $notificationMsg, 'carDocumentsWithoutExpDateNotification');
                        $adminNotification[] = $notificationMsg;

                        if ($carHasOwnerOrUser) {
                            if ($ownerEqualWithUser) {
                                $holderNotification[$holder['email']][] = $notificationMsg;
                            } else {
                                if (!empty($holder['email']))
                                    $holderNotification[$holder['email']][] = $notificationMsg;
                                if (!empty($user['email']))
                                    $userNotification[$user['email']][] = $notificationMsg;
                            }
                        }
                    }
                }
            }

            if (empty($holderNotification) && empty($userNotification) && empty($adminNotification)) {
                echo("\n No notification to send\n");
                Yii::info("\n" . Yii::t('cmd-auto', "NO NOTIFICATION TO SEND"), 'carDocumentsWithoutExpDateNotification');
                return ExitCode::OK;
            }

            foreach ($holderNotification as $emailHolder => $contentHolder) {
                Yii::info("\n" . Yii::t('cmd-auto', "Will send notification to {emailHolder}", ['emailHolder' => $emailHolder]), 'carDocumentsWithoutExpDateNotification');
                $this->sendDocumentExpirationNotification($contentHolder, $emailHolder);
                Yii::info("\n" . Yii::t('cmd-auto', "EMAIL SENT"), 'carDocumentsWithoutExpDateNotification');
            }
            foreach ($userNotification as $userEmail => $contentUser) {
                Yii::info("\n" . Yii::t('cmd-auto', "Will send notification to {emailHolder}", ['emailHolder' => $userEmail]), 'carDocumentsWithoutExpDateNotification');
                $this->sendDocumentExpirationNotification($contentUser, $userEmail);
                Yii::info("\n" . Yii::t('cmd-auto', "EMAIL SENT"), 'carDocumentsWithoutExpDateNotification');
            }
            foreach ($autoAdminEmailsToNotify as $autoAdminEmail) {
                $autoAdminEmail = trim($autoAdminEmail);
                echo "\nWill send notification to '{$autoAdminEmail}'\n";
                Yii::info("\n" . Yii::t('cmd-auto', "Will send notification to {emailHolder}", ['emailHolder' => $autoAdminEmail]), 'carDocumentsWithoutExpDateNotification');
                $this->sendDocumentExpirationNotification($adminNotification, $autoAdminEmail);
                Yii::info("\n" . Yii::t('cmd-auto', "EMAIL SENT"), 'carDocumentsWithoutExpDateNotification');
                echo "\nEMAIL SENT\n";
            }
            echo("\nALL NOTIFICATION SENT\n");
            Yii::info("\n" . Yii::t('cmd-auto', "ALL NOTIFICATION SENT"), 'carDocumentsWithoutExpDateNotification');
            return ExitCode::OK;
        } catch (Exception $exc) {
            echo("\n {$exc->getMessage()} {$exc->getLine()}");
            Yii::error("\n {$exc->getMessage()} {$exc->getLine()}", 'carDocumentsWithoutExpDateNotification');
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
            Yii::error("\n" . Yii::t('cmd-auto', "User with email {emailToRecipients} not found", ['emailToRecipients' => $emailToRecipients]), 'carDocumentsWithoutExpDateNotification');
        } else {
            $mailBody = $this->renderPartial('documents-without-exp-date-notification-html', [
                'documentExpire' => $notificationContent,
                'user' => $user,
            ]);

            $sendEmail = new SendSharePointMailHelper();
            $sendEmail->subject = Yii::t('cmd-auto', "Car documents without expiration date set");
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

            Yii::info("\n" . Yii::t('cmd-auto', "Successfully sent the mail notification"), 'carDocumentsWithoutExpDateNotification');
        }
    }
}