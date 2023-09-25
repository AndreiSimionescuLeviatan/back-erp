<?php

use \yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;
use yii\captcha\Captcha;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap4\ActiveForm */

/* @var $model backend\modules\adm\models\forms\LoginForm */

$this->title = Yii::t('app', 'Login');
?>
<div class="card">
    <div class="card-body login-card-body">
        <div class="row">
            <div class="col-12">
                <?php foreach (Yii::$app->session->getAllFlashes() as $key => $message) {
                    echo '<div class="alert alert-' . $key . ' alert-dismissible text-sm fade show">';
                    echo $message;
                    echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                    echo '<span aria-hidden="true">&times;</span>';
                    echo '</button>';
                    echo '</div>';
                } ?>
            </div>
        </div>
        <p class="login-box-msg">
            <?php echo Yii::t('app', 'Sign in to start your session'); ?>
        </p>
        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'options' => ['autocomplete' => 'off']
        ]);

        //username form filed
        $userNameInputTemplate = '';
        $userNameInputTemplate .= '<div class="input-group mb-3">';
        $userNameInputTemplate .= '{input}';
        $userNameInputTemplate .= '<div class="input-group-append">';
        $userNameInputTemplate .= '<div class="input-group-text">';
        $userNameInputTemplate .= '<span class="fas fa-user"></span>';
        $userNameInputTemplate .= '</div></div></div>';
        echo $form->field($model, 'email', [
            'inputTemplate' => $userNameInputTemplate,
        ])->input('email', [
            'class' => 'form-control',
            'placeholder' => Yii::t('app', 'Email'),
            'autofocus' => true
        ])->label(false);
        //password form filed
        $pswInputTemplate = '';
        $pswInputTemplate .= '<div class="input-group mb-3">';
        $pswInputTemplate .= '{input}';
        $pswInputTemplate .= '<div class="input-group-append">';
        $pswInputTemplate .= '<div class="input-group-text">';
        $pswInputTemplate .= '<span class="fas fa-lock"></span>';
        $pswInputTemplate .= '</div></div></div>';
        echo $form->field($model, 'password', [
            'inputTemplate' => $pswInputTemplate,
        ])->passwordInput([
            'class' => 'form-control',
            'placeholder' => Yii::t('app', 'Password'),
        ])->label(false);

        $captchaInputTemplate = '';
        $captchaInputTemplate .= '<div class="input-group mb-3">';
        $captchaInputTemplate .= '{input}';
        $captchaInputTemplate .= '<div class="input-group-append">';
        $captchaInputTemplate .= '<div class="input-group-text p-0">';
        $captchaInputTemplate .= '{image}';
        $captchaInputTemplate .= '</div>';
        $captchaInputTemplate .= '</div>';
        $captchaInputTemplate .= '</div>';
        echo $form->field($model, 'verifyCode')->widget(Captcha::class, [
            'template' => $captchaInputTemplate,
            'options' => [
                'placeholder' => Yii::t('app', 'Enter the text from right'),
            ]
        ])->label(false);

        //remember me checkbox
        echo Html::beginTag('div', ['class' => 'row text-center']);
        //        echo Html::beginTag('div', ['class' => 'col-7']);
        //        echo $form->field($model, 'rememberMe', [
        //            'options' => ['class' => ['widget' => false]],
        //            'checkTemplate' => '<div class="icheck-primary">{input}{label}</div>',
        //            'checkOptions' => [
        //                'class' => false,
        //                'labelOptions' => [
        //                    'class' => false
        //                ]
        //            ]
        //        ])->checkbox();
        //        echo Html::endTag('div');

        //submit button
        echo Html::beginTag('div', ['class' => 'col-12']);
        echo Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-primary btn-block', 'name' => 'login-button']);

        echo Html::endTag('div');
        echo Html::endTag('div');
        ActiveForm::end();
        ?>
        <p class="mt-2 mb-0 text-center">
            <?php echo Html::a(Yii::t('app', 'Forgot the password? Click here to reset.'), ['site/request-password-reset']) ?>
        </p>
    </div>
    <!-- /.login-card-body -->
</div>