<?php

namespace backend\modules\adm\models\forms;

use backend\components\MailSender;
use Yii;
use yii\base\Model;
use backend\modules\adm\models\User;
use yii\db\Exception;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $email;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => '\backend\modules\adm\models\User',
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => Yii::t('adm', 'There is no user with this email address')
            ],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was sent
     * @throws Exception
     */
    public function sendEmail()
    {
        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email' => $this->email,
        ]);

        if (!$user) {
            throw new Exception(Yii::t('adm', 'No valid user found! Please contact an administrator!'));
        }
        $user->setScenario('skipCompanyValidation');

        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
            if (!$user->save()) {
                throw new Exception(Yii::t('adm', 'Internal error! Please contact an administrator!'));
            }
        }
        try {
            $subject = Yii::t('adm', 'Password reset for ') . Yii::$app->name;
            $mailBody = Yii::$app->controller->renderPartial('passwordResetToken-html', [
                'user' => $user
            ]);
            return MailSender::sendMail($subject, $mailBody, $user);
        } catch (\Exception $exc) {
            throw new Exception(Yii::t('adm', $exc->getMessage()));
        }
    }
}
