<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap4\ActiveForm */

/* @var $model backend\modules\adm\models\forms\PasswordResetRequestForm */

$this->title = Yii::t('app', 'Request password reset');
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
            <?php echo Yii::t('app', 'Please fill out your email. A link to reset password will be sent there.'); ?>
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

        //submit button
        echo Html::beginTag('div', ['class' => 'row text-center']);
        echo Html::beginTag('div', ['class' => 'col-12']);
        echo Html::submitButton(Yii::t('app', 'Request password reset'), ['class' => 'btn btn-primary btn-block', 'name' => 'login-button']);
        echo Html::endTag('div');
        echo Html::endTag('div');
        ActiveForm::end();
        ?>
        <p class="mt-2 mb-0 text-center">
            <?php echo Html::a(Yii::t('app', 'Go to the login page.'), ['site/login']) ?>
        </p>
    </div>
    <!-- /.login-card-body -->
</div>
