<?php

use backend\modules\adm\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\adm\models\User */
/* @var $updatePswModel \backend\modules\adm\models\forms\UpdatePswForm */
/* @var $form yii\widgets\ActiveForm */
?>

    <div class="update-user-psw-form">
        <?php $form = ActiveForm::begin([
            'fieldConfig' => [
                'errorOptions' => ['class' => 'error invalid-tooltip'],
                'labelOptions' => ['class' => 'control-label'],
            ],
            'successCssClass' => '',
        ]); ?>
        <div class="form-row">
            <?php
            $readOnly = $model->psw_changed == User::PSW_CHANGED_NO && $model->first_time_login == User::FIRST_TIME_LOGIN_YES;
            echo $form->field($updatePswModel, 'oldPassword', [
                'options' => [
                    'class' => 'form-group col-lg-4 col-sm-12',
                    'readonly' => true
                ]
            ])->passwordInput(['maxlength' => true, 'readonly' => $readOnly])->label(Yii::t('adm', 'Old password'));
            ?>
            <?php
            echo $form->field($updatePswModel, 'newPassword', [
                'options' => [
                    'class' => 'form-group col-lg-4 col-sm-12',
                ]
            ])->passwordInput(['maxlength' => true])->label(Yii::t('adm', 'New Password'));
            ?>
            <?php
            echo $form->field($updatePswModel, 'newPasswordConfirm', [
                'options' => [
                    'class' => 'form-group col-lg-4 col-sm-12',
                ]
            ])->passwordInput(['maxlength' => true])->label(Yii::t('adm', 'Password confirm'));
            ?>
        </div>
        <?php
        $confirmReloadMsg = Yii::t('adm', "The data will be lost. Are you sure you want to reload?");
        $confirmBackMsg = Yii::t('adm', "The data will be lost. Are you sure you want to leave?");
        $url = Url::previous('user');
        ?>
        <div class="form-row">
            <div class="form-group col-auto">
                <?php
                echo Html::button(Yii::t('adm', '{icon} Back',
                    [
                        'icon' => '<i class="fas fa-chevron-circle-left"></i>'
                    ]),
                    [
                        'id' => 'back_button',
                        'class' => 'btn btn-info'
                    ]);
                ?>
            </div>
            <div class="from-group col-auto ml-auto">
                <?php echo Html::button(
                    Yii::t('adm', '{icon} Reset',
                        [
                            'icon' => '<i class="fas fa-redo"></i>'
                        ]),
                    [
                        'id' => 'reload_button',
                        'class' => 'btn btn-primary'
                    ]
                ); ?>
                <?php echo Html::submitButton(
                    Yii::t('adm', $model->isNewRecord ? '{icon} Create' : '{icon} Update', ['icon' => '<i class="far fa-save"></i>']),
                    ['class' => 'btn btn-success']
                ); ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

<?php
$this->registerJs(
    "backFunction('{$confirmBackMsg}', '{$form->getId()}', '{$url}');",
    View::POS_READY,
    'back-function-handler'
);

$this->registerJs(
    "reloadFunction('{$confirmReloadMsg}', '{$form->getId()}');",
    View::POS_READY,
    'reload-function-handler'
);
?>