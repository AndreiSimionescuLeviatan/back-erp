<?php

namespace console\controllers;

use api\modules\v1\models\MailHelper;
use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\helpers\Url;

class AdmChangeSuperAdminPswController extends Controller
{
    /**
     * Resets psw for Super Admin
     * After update an email with new psw will be sent to the chosen user
     * The function will be appeal every one week
     */
    public function actionIndex()
    {
        $conn = User::getDb();
        $toEmail = Settings::findOne(['name' => 'SUPER_ADMIN_PSW_RECEIVER']);
        if (empty($toEmail) || empty($toEmail->value)) {
            echo "No setting found for receiver email \n";
            return ExitCode::NOUSER;
        }
        $superAdminEmail = Settings::findOne(['name' => 'SUPER_ADMIN_EMAIL_ADDRESS']);
        if (empty($superAdminEmail) || empty($superAdminEmail->value)) {
            echo "No Super Admin user found \n";
            return ExitCode::NOUSER;
        }
        $user = User::findByEmail($superAdminEmail->value);
        if (empty($user)) {
            echo "The user with email address could not be found \n";
            return ExitCode::NOUSER;
        }
        $user->setScenario('skipCompanyValidation');

        $transaction = $conn->beginTransaction();
        try {
            $newPsw = Yii::$app->security->generateRandomString(16);
            echo $newPsw . "\n";

            $user->auth_key = Yii::$app->security->generateRandomString();
            $user->password_hash = Yii::$app->security->generatePasswordHash($newPsw);
            $user->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
            $user->updated_at = Yii::$app->formatter->asTimestamp(date('Y-d-m h:i:s'));

            if (!$user->save()) {
                if ($user->hasErrors()) {
                    foreach ($user->errors as $error) {
                        throw new Exception($error[0]);
                    }
                }
                throw new Exception('Failed to update Super Admin password');
            }
            $domain = Url::base(true);
            $mailNotification = new MailHelper();
            $subject = 'Noua parola pentru Super Admin';
            $post = [
                'to' => $toEmail->value,
                'subject' => $subject,
                'content' => "Noua parola pentru domeniul '{$domain}' pentru utilizatorul Super Admin este: {$newPsw}",
            ];
            if (!$mailNotification->sendEmailNotification($post)) {
                throw new Exception("The user with email address could not be found \n");
            }

            $transaction->commit();
            echo "Super Admin password successfully changed \n";
            echo "The new password for Super Admin '{$superAdminEmail->value}' successfully sent to '{$toEmail->value}' \n";
            return ExitCode::OK;
        } catch (\Exception $exc) {
            $transaction->rollBack();
            echo "{$exc->getMessage()}\n";
            return ExitCode::SOFTWARE;
        }
    }
}