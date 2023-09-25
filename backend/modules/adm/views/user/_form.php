<?php

use backend\modules\adm\models\ErpCompany;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\adm\models\User */
/* @var $form yii\widgets\ActiveForm */
?>
<?php
$oldAttributes = [];
if (!empty($model->first_name)) {
    $oldAttributes[] = $model->first_name;
}
if (!empty($model->last_name)) {
    $oldAttributes[] = $model->last_name;
}
if (!empty($model->email)) {
    $oldAttributes[] = $model->email;
}
?>

    <div class="user-form">

        <?php $form = ActiveForm::begin([
            'fieldConfig' => [
                'errorOptions' => ['class' => 'error invalid-tooltip'],
                'labelOptions' => ['class' => 'control-label'],
            ],
            'successCssClass' => '',
        ]); ?>

        <div class="form-row">
            <?php echo $form->field($model, 'first_name',
                [
                    'options' => [
                        'class' => 'form-group col-6',
                    ]
                ])->label(Yii::t('adm', 'First name'));
            ?>
        </div>
        <div class="form-row">
            <?php echo $form->field($model, 'last_name',
                [
                    'options' => [
                        'class' => 'form-group col-6',
                    ]
                ])->label(Yii::t('adm', 'Last name'));
            ?>
        </div>
        <div class="form-row">
            <?php echo $form->field($model, 'email',
                [
                    'options' => [
                        'class' => 'form-group col-6',
                    ]
                ])->label(Yii::t('adm', 'E-mail'));
            ?>
        </div>
        <div class="form-row">
            <?php
            echo $form->field($model, 'companies',
                [
                    'options' => [
                        'class' => 'form-group col-6',
                    ]
                ])->widget(Select2::className(), [
                'data' => ErpCompany::$names,
                'options' => [
                    'prompt' => Yii::t('adm', 'Select company'),
                    'multiple' => true],
                'pluginOptions' => ['allowClear' => true]
            ])->label(Yii::t('adm', 'Company'));
            ?>
        </div>
        <div class="form-row">
            <?php
            if (!$model->isNewRecord)
                echo $form->field($model, 'status',
                    [
                        'options' => [
                            'class' => 'form-group col-6',
                        ]
                    ])->dropDownList(
                    ['10' => Yii::t('adm', 'Active'),
                        '0' => Yii::t('adm', 'Deleted'),
                        '9' => Yii::t('adm', 'Inactive')])
                    ->label(Yii::t('adm', 'Status'));
            ?>
        </div>

        <div class="row">
            <div class="col-lg-6 col-sm-12">
                <div class="form-row">
                    <div class="form-group col-auto">
                        <?php
                        $confirmReloadMsg = Yii::t('adm', "The data will be lost. Are you sure you want to reload?");
                        $confirmBackMsg = Yii::t('adm', "The data will be lost. Are you sure you want to leave?");
                        $url = Url::previous('user');

                        echo Html::button(Yii::t('adm', '{icon} Back', [
                            'icon' => '<i class="fas fa-chevron-circle-left"></i>'
                        ]),
                            [
                                'id' => 'back_button',
                                'class' => 'btn btn-info',
                            ]);
                        ?>
                    </div>
                    <div class="from-group col-auto ml-auto">
                        <?php echo Html::button(
                            Yii::t('adm', '{icon} Reset', ['icon' => '<i class="fas fa-redo"></i>']),
                            [
                                'id' => 'reload_button',
                                'class' => 'btn btn-primary'
                            ]
                        ); ?>
                        <?php echo Html::submitButton(
                            Yii::t('adm', $model->isNewRecord ? '{icon} Save' : '{icon} Update', ['icon' => '<i class="far fa-save"></i>']),
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