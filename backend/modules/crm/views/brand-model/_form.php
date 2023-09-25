<?php

use backend\modules\crm\models\Brand;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\crm\models\BrandModel */
/* @var $existingModel backend\modules\crm\models\BrandModel */
/* @var $form yii\widgets\ActiveForm */

?>
    <div class="brand-model-form">
        <?php $form = ActiveForm::begin([
            'fieldConfig' => [
                'errorOptions' => ['class' => 'error invalid-tooltip'],
                'labelOptions' => ['class' => 'control-label'],
            ],
            'successCssClass' => '',
        ]); ?>
        <div class="form-row">
            <div class="col-lg-6 col-sm-12">
                <div class="row">
                    <?php
                    echo $form
                        ->field($model, 'brand_id', ['options' => ['class' => 'form-group col-12',]])
                        ->dropDownList(Brand::$brand, ['prompt' => Yii::t('crm', 'Select brand...')])
                        ->label(Yii::t('crm', 'Brand'));
                    ?>
                    <?php
                    echo $form
                        ->field($model, 'name', ['options' => ['class' => 'form-group col-12']])
                        ->textInput(['maxlength' => true, 'pattern' => '[a-zA-Z0-9 ]+'])->label(Yii::t('crm', 'Name'));
                    ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-sm-12">
                <div class="form-row">
                    <div class="form-group col-auto">
                        <?php
                        $confirmReloadMsg = Yii::t('crm', "The data will be lost. Are you sure you want to reload?");
                        $confirmBackMsg = Yii::t('crm', "The data will be lost. Are you sure you want to leave?");
                        $url = Url::previous('brand_model');

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
                        <?php
                        echo Html::button(
                            Yii::t('crm', '{icon} Reset', ['icon' => '<i class="fas fa-redo"></i>']),
                            [
                                'id' => 'reload_button',
                                'class' => 'btn btn-primary'
                            ]
                        ); ?>
                        <?php
                        if (!empty($existingModel)) {
                            $classButtonSave = 'display-buttons-save-activate';
                            $classButtonActivate = '';
                            $urlActivate = Url::to(['activate', 'id' => $existingModel->id]);
                        } else {
                            $classButtonActivate = 'display-buttons-save-activate';
                            $urlActivate = null;
                        } ?>
                        <?php $confirmDelete = Yii::t('crm', 'Are you sure you want to activate this {item}?',
                            ['item' => Yii::t('crm', 'model')]);
                        echo Html::button(
                            Yii::t('crm', '{icon} Activate',
                                [
                                    'icon' => '<i class="fas fa-trash-restore-alt"></i>'
                                ]),
                            [
                                'id' => 'button_activate',
                                'class' => 'btn btn-warning ' . $classButtonActivate,
                                'onClick' => 'deleteActivateRecord("' . $confirmDelete . '", "' . $urlActivate . '");'
                            ]);
                        echo Html::submitButton(
                            Yii::t('crm', $model->isNewRecord ? '{icon} Save' : '{icon} Update', ['icon' => '<i class="far fa-save"></i>']),
                            [
                                'id' => 'button_save',
                                'class' => 'ml-1 btn btn-success ',
                            ]); ?>
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