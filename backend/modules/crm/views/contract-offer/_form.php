<?php

use backend\modules\crm\models\Company;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\crm\models\ContractOffer */
/* @var $form yii\widgets\ActiveForm */
?>

    <div class="contract-offer-form">
        <?php $form = ActiveForm::begin([
            'fieldConfig' => [
                'errorOptions' => ['class' => 'error invalid-tooltip'],
                'labelOptions' => ['class' => 'control-label'],
            ],
            'successCssClass' => '',
            'validateOnChange' => false
        ]); ?>
        <div class="form-row">
            <?php echo $form->field($model, 'company_id', [
                'options' => [
                    'class' => 'form-group col-6',
                ]
            ])->widget(Select2::className(), [
                'name' => 'project-page',
                'data' => Company::$names,
                'options' => [
                    'prompt' => Yii::t('crm', 'Select company...'),
                    'id' => 'company-id',
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ]
            ]) ?>
        </div>
        <div class="form-row">
            <?php echo $form->field($model, 'name',
                [
                    'options' => [
                        'class' => 'form-group col-6',
                    ]
                ])->label(Yii::t('crm', 'Name'));
            ?>
        </div>

        <div class="row">
            <div class="col-lg-6 col-sm-12">
                <div class="form-row">
                    <div class="form-group col-auto">
                        <?php
                        $confirmReloadMsg = Yii::t('crm', "The data will be lost. Are you sure you want to reload?");
                        $confirmBackMsg = Yii::t('crm', "The data will be lost. Are you sure you want to leave?");
                        $url = Url::previous('page');

                        echo Html::button(Yii::t('crm', '{icon} Back',
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
                            Yii::t('crm', '{icon} Reset', ['icon' => '<i class="fas fa-redo"></i>']),
                            [
                                'id' => 'reload_button',
                                'class' => 'btn btn-primary',
                            ]
                        ); ?>
                        <?php echo Html::submitButton(
                            Yii::t('crm', $model->isNewRecord ? '{icon} Save' : '{icon} Update', ['icon' => '<i class="far fa-save"></i>']),
                            ['class' => 'btn btn-success']
                        ); ?>
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