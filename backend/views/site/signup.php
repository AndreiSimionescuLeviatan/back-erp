<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap4\ActiveForm */

/* @var $model SignupForm */

use backend\modules\adm\models\forms\SignupForm;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

$this->title = 'Signup';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    select.form-control.is-valid, .was-validated select.form-control:valid,
    select.form-control.is-invalid, .was-validated select.form-control:invalid {
        background-position: right calc(.375em + 2.5rem) center;
    }
</style>
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
            <?php echo Yii::t('app', 'Register a new membership'); ?>
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
        echo $form->field($model, 'username', [
            'inputTemplate' => $userNameInputTemplate,
        ])->textInput([
            'class' => 'form-control',
            'placeholder' => 'Username',
        ])->label(false);

        //username form filed
        $userNameInputTemplate = '';
        $userNameInputTemplate .= '<div class="input-group mb-3">';
        $userNameInputTemplate .= '{input}';
        $userNameInputTemplate .= '<div class="input-group-append">';
        $userNameInputTemplate .= '<div class="input-group-text">';
        $userNameInputTemplate .= '<span class="fas fa-envelope"></span>';
        $userNameInputTemplate .= '</div></div></div>';
        echo $form->field($model, 'email', [
            'inputTemplate' => $userNameInputTemplate,
        ])->textInput([
            'class' => 'form-control',
            'placeholder' => 'Email',
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
            'placeholder' => 'Password',
        ])->label(false);

        //password form filed
        echo $form->field($model, 'passwordConfirm', [
            'inputTemplate' => $pswInputTemplate,
        ])->passwordInput([
            'class' => 'form-control',
            'placeholder' => 'Confirm password',
        ])->label(false);

        ?>
        <div class="form-group">
            <?php echo Html::submitButton(Yii::t('app', 'Register'), ['class' => 'btn btn-primary btn-block', 'name' => 'signup-button']) ?>
        </div>
        <?php
        ActiveForm::end();
        ?>
        <p class="mb-0">
            <?php echo Html::a('I already have a membership', ['site/login']); ?>
        </p>
    </div>
    <!-- /.login-card-body -->
</div>

