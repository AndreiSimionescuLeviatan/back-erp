<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\adm\models\Settings */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="settings-form">

    <?php $form = ActiveForm::begin([
        'fieldConfig' => [
            'errorOptions' => ['class' => 'error invalid-tooltip'],
            'labelOptions' => ['class' => 'control-label'],
        ],
        'successCssClass' => '',
    ]); ?>
    <div class="form-row">
        <?php echo $form->field($model, 'name',
            [
                'options' => [
                    'class' => 'form-group col-lg-6 col-sm-12'
                ]
            ])->textInput(['maxlength' => true])->label(Yii::t('adm', 'Name')); ?>
        <?php echo $form->field($model, 'value',
            [
                'options' => [
                    'class' => 'form-group col-lg-6 col-sm-12'
                ]
            ])->textarea(['maxlength' => true, 'rows' => 2])->label(Yii::t('adm', 'Value')); ?>
    </div>
    <div class="row">

        <?php
        echo $form->field($model, 'description', [
            'options' => [
                'class' => 'form-group col-12'
            ]
        ])->textarea(['rows' => 6]);
        ?>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="form-row">
                <div class="form-group col-auto">
                    <?php
                    $confirmReloadMsg = Yii::t('adm', "The data will be lost. Are you sure you want to reload?");
                    $confirmBackMsg = Yii::t('adm', "The data will be lost. Are you sure you want to leave?");
                    $url = Url::previous('settings');

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
                    <?php
                    if (Yii::$app->user->can('SuperAdmin')) {
                        echo Html::button(
                            Yii::t('adm', '{icon} Reset', ['icon' => '<i class="fas fa-redo"></i>']),
                            [
                                'id' => 'reload_button',
                                'class' => 'btn btn-primary'
                            ]
                        );

                        echo Html::submitButton(
                            Yii::t('adm', $model->isNewRecord ? '{icon} Save' : '{icon} Update', ['icon' => '<i class="far fa-save"></i>']),
                            [
                                'id' => 'button_save',
                                'class' => 'ml-1 btn btn-success ',
                            ]);
                    }
                    ?>
                </div>
            </div>
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
