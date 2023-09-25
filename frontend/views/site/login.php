<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap4\ActiveForm */

/* @var $model \common\models\LoginForm */

use \yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;

$this->title = 'Login';
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
        <?php $form = ActiveForm::begin(['id' => 'login-form']);

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
        ])->textInput([
            'class' => 'form-control',
            'placeholder' => Yii::t('app', 'Email'),
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

        //remember me checkbox
        echo Html::beginTag('div', ['class' => 'row']);
        echo Html::beginTag('div', ['class' => 'col-8']);
        echo $form->field($model, 'rememberMe', [
            'options' => ['class' => ['widget' => false]],
            'checkTemplate' => '<div class="icheck-primary">{input}{label}</div>',
            'checkOptions' => [
                'class' => false,
                'labelOptions' => [
                    'class' => false
                ]
            ]
        ])->checkbox();
        echo Html::endTag('div');

        //submit button
        echo Html::beginTag('div', ['class' => 'col-4']);
        echo Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-primary btn-block', 'name' => 'login-button']);

        echo Html::endTag('div');
        echo Html::endTag('div');
        ActiveForm::end();
        ?>
        <p class="mb-0">
            <?php echo Html::a(Yii::t('app', 'No account yet, click here to register'), ['site/signup']) ?>
        </p>
    </div>
    <!-- /.login-card-body -->
</div>