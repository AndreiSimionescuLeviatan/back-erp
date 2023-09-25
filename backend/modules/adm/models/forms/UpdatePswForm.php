<?php

namespace backend\modules\adm\models\forms;

use Yii;
use yii\base\Model;
use backend\modules\adm\models\User;

/**
 * Update password form
 */
class UpdatePswForm extends Model
{
    public $oldPassword;
    public $newPassword;
    public $newPasswordConfirm;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['oldPassword', 'required', 'except' => ['changeUserPsw']],

            ['newPassword', 'required'],
            ['newPassword', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],

            ['newPasswordConfirm', 'required'],
            ['newPasswordConfirm', 'compare', 'compareAttribute' => 'newPassword', 'message' => Yii::t('adm', 'Passwords don\'t match.')],
        ];
    }

    /**
     * Allow user to change his password.
     * Also, the SuperAdmin role can change users psw
     * @return bool whether the updating password was successful
     */
    public function changePassword()
    {
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'id' => Yii::$app->request->get('id'),
        ]);

        if (!$user) {
            Yii::$app->session->setFlash('danger', Yii::t('adm', 'Sorry, no user to match your criteria found.'));
            return false;
        }

        if ($user->id !== Yii::$app->user->id && !Yii::$app->user->can('SuperAdmin')) {
            Yii::$app->session->setFlash('danger', Yii::t('adm', 'Sorry, you can not change other user password.'));
            return false;
        }
        if (!Yii::$app->user->can('SuperAdmin') && !Yii::$app->getSecurity()->validatePassword($this->oldPassword, $user->password_hash)) {
            Yii::$app->session->setFlash('danger', Yii::t('adm', 'Sorry, we are unable to reset your password. Please verify your new password and if the error is not fixed contact an administrator!'));
            return false;
        }

        $user->setScenario('skipCompanyValidation');
        $user->setPassword($this->newPassword);
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();
        $user->psw_changed = User::PSW_CHANGED_YES;
        $user->first_time_login = User::FIRST_TIME_LOGIN_NO;
        return $user->save();
    }
}
