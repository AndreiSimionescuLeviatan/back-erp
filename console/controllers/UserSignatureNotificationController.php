<?php

namespace console\controllers;

use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use backend\modules\adm\models\UserSignature;
use backend\modules\auto\models\Car;
use backend\modules\auto\models\CarConsumption;
use common\components\SendSharePointMailHelper;
use PHPUnit\Util\Exception;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class UserSignatureNotificationController extends Controller
{
    function actionIndex()
    {
        echo "Script run at " . date("Y-m-d-h:m:s") . "\n";
        $this->setViewPath('@app/mail');
        $autoAdminEmailsToNotify = Car::getReceiversEmailsCarParkAdmin();

        try {
            $cars = Car::findAllByAttributes(['deleted' => 0, 'deductibility' => 50]);
            foreach ($cars as $car) {
                $userSignature = UserSignature::find()
                    ->where(['user_id' => $car->holder_id])
                    ->andWhere(['deleted' => 0])
                    ->one();
                if (empty($userSignature)) {
                    $user = User::find()->where(['id' => $car->holder_id])->one();
                    UserSignature::sendEmailForUserSignatureNotification($user->email);
                    foreach ($autoAdminEmailsToNotify as $autoAdminEmail) {
                        $autoAdminEmail = trim($autoAdminEmail);
                        echo "\nWill send notification to '{$autoAdminEmail}'\n";
                        Yii::info("\n" . Yii::t('cmd-auto', "Will send notification to {emailHolder}", ['emailHolder' => $autoAdminEmail]));
                        UserSignature::sendEmailForUserSignatureNotification($autoAdminEmail);
                        Yii::info("\n" . Yii::t('cmd-auto', "EMAIL SENT"));
                        echo "\nEMAIL SENT\n";
                    }
                    echo("\nALL NOTIFICATION SENT\n");
                    Yii::info("\n" . Yii::t('cmd-auto', "ALL NOTIFICATION SENT"));
                    return ExitCode::OK;
                }
            }
        } catch (Exception $exc) {
            echo("\n {$exc->getMessage()} {$exc->getLine()}");
            Yii::error("\n {$exc->getMessage()} {$exc->getLine()}");
            return ExitCode::SOFTWARE;
        }
    }

}