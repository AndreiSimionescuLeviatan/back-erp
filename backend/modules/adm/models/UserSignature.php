<?php

namespace backend\modules\adm\models;

use common\components\SendSharePointMailHelper;
use Yii;

class UserSignature extends UserSignatureParent
{

    public static function getSignature($userID)
    {
        $signature = UserSignature::find()
            ->where([
                'user_id' => $userID,
                'deleted' => 0
                ])
            ->one();
        if (!empty($signature)) {
            $signatureDir = Yii::getAlias('@backend/web/images/signatures');
            $signatureFile = $signatureDir . '/' . $signature->signature;
            if (file_exists($signatureFile)) {
                $signature->signature = file_get_contents($signatureFile);
                $signature->signature = base64_encode($signature->signature);
                $signature = 'data:image/png;base64,' . $signature->signature;
                return $signature;
            }
        }
        return null;
    }


    public static function sendEmailForUserSignatureNotification($emailToRecipients)
    {
        $user = User::find()->where('email = :email', [':email' => $emailToRecipients])->one();
        if (empty($user)) {
            Yii::error("\n" . Yii::t('app', "User with email {$emailToRecipients} not found"));
        } else {
            $mailBody = Yii::$app->controller->renderPartial('user-signature-notification.php', [
                'user' => $user,
            ]);

            $sendEmail = new SendSharePointMailHelper();
            $sendEmail->subject = Yii::t('adm', 'Signature for roadmap.');

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

            Yii::info("\n" . Yii::t('adm', "Successfully sent the mail notification"));
        }
    }

}
