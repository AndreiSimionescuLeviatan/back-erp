<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $model backend\modules\adm\models\forms\ResetPasswordForm */

$this->title = Yii::t('app', 'Reset password');
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
            <?php echo Yii::t('app', 'Please choose your new password'); ?>
        </p>
        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'options' => ['autocomplete' => 'off']
        ]);

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
            'placeholder' => Yii::t('app', 'Enter your new password'),
            'autofocus' => true
        ])->label(false);

        //submit button
        echo Html::beginTag('div', ['class' => 'row text-center']);
        echo Html::beginTag('div', ['class' => 'col-12']);
        echo Html::submitButton(Yii::t('app', 'Set new password'), ['class' => 'btn btn-primary btn-block', 'name' => 'login-button']);

        echo Html::endTag('div');
        echo Html::endTag('div');
        ActiveForm::end();
        ?>
    </div>
    <!-- /.login-card-body -->
</div>
