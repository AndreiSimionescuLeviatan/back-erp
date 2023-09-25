<?php

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model \backend\modules\crm\models\Company */

$this->title = Yii::t('crm', 'Import companies');
$this->params['breadcrumbs'][] = ['label' => Yii::t('crm', 'Companies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-import">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <div class="company-form">
        <?php
        $form = ActiveForm::begin([
            'fieldConfig' => [
                'errorOptions' => ['class' => 'error invalid-tooltip'],
                'labelOptions' => ['class' => 'control-label'],
            ],
            'successCssClass' => ''
        ]); ?>
        <div class="form-row">
            <div class="col-md-6">
                <div class="row">
                    <?php
                    echo $form->field($model, 'excelFile', [
                        'options' => [
                            'class' => 'form-group col-12'
                        ]
                    ])->fileInput(['multiple' => false,
                        'accept' => '.xlsx, .xls'
                    ])->label(Yii::t('crm', 'Excel File '));
                    ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-sm-12 ">
                <div class="row">
                    <div class="form-group col-auto ">
                        <?php
                        $confirmReloadMsg = Yii::t('crm', "The data will be lost. Are you sure you want to reload?");
                        $confirmBackMsg = Yii::t('crm', "The data will be lost. Are you sure you want to leave?");
                        $url = Url::previous('company');
                        ?>

                        <?php
                        echo Html::button(Yii::t('crm', '{icon} Back',
                            [
                                'icon' => '<i class="fas fa-chevron-circle-left"></i>'
                            ]),
                            [
                                'class' => 'btn btn-info',
                                'onclick' => 'backAction()'
                            ]);
                        ?>
                    </div>
                    <div class="from-group col-auto ml-auto text-right">
                        <?php
                        echo Html::button(
                            Yii::t('crm', '{icon} Reset', ['icon' => '<i class="fas fa-redo"></i>']),
                            [
                                'id' => 'reload_button',
                                'class' => 'btn btn-primary',
                                'onclick' => 'reloadAction()'
                            ]
                        ); ?>
                        <?php echo Html::submitButton(
                            Yii::t('crm', '{icon} Upload', ['icon' => '<i class="fas fa-upload"></i>']),
                            [
                                'class' => 'btn btn-success',
                                'id' => 'submit-button',
                                'onClick' => 'disableButtonsUntilRefresh()'
                            ]
                        ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$this->registerJs(
    'bindBtnsActions()',
    View::POS_END,
    'bid_action_btns'
);
?>

<script>
    function disableButtonsUntilRefresh() {
        $('#submit-button').html("please wait...");

        setTimeout(function () {
            $(".btn").prop("disabled", true);
        }, 100);
    }

    function updateButton() {
        if (verifySelects()) {
            $('#submit-button').attr('disabled', false);
            $('#reload_button').attr('disabled', false);
        } else {
            $('#submit-button').attr('disabled', true);
            $('#reload_button').attr('disabled', true);
        }
    }

    function verifySelects() {
        if ($('#company-excelfile').val() != '') {
            return true;
        } else {
            return false
        }
    }

    /**
     * Enable or disable reload and submit buttons when a file was uploaded
     * @author Anca P.
     * @since 25.07.2022
     */
    function bindBtnsActions() {
        $('#submit-button').attr('disabled', true);
        $('#reload_button').attr('disabled', true);

        $('#company-excelfile').on('change', function (e) {
            updateButton();
        });
    }

    /**
     * Show confirm message when back button is pressed
     * @author Anca P.
     * @since 25.07.2022
     */
    function backAction() {
        if (document.getElementById('company-excelfile').files.length === 0) {
            window.history.back();
        } else {
            bootbox.confirm({
                message: '<?php echo $confirmBackMsg; ?>',
                buttons: {
                    confirm: {
                        label: '<?php echo Yii::t('crm', 'Yes'); ?>',
                        className: 'btn-primary'
                    },
                    cancel: {
                        label: '<?php echo Yii::t('crm', 'No'); ?>',
                    }
                },
                callback: function (result) {
                    if (result) {
                        window.history.back();
                    }
                }
            });
        }
    }

    /**
     * Show confirm message when reset button is pressed
     * @author Anca P.
     * @since 25.07.2022
     */
    function reloadAction() {
        if (document.getElementById('company-excelfile').files.length === 0) {
            window.location.reload();
        } else {
            bootbox.confirm({
                message: '<?php echo $confirmReloadMsg; ?>',
                buttons: {
                    confirm: {
                        label: '<?php echo Yii::t('crm', 'Yes'); ?>',
                        className: 'btn-primary'
                    },
                    cancel: {
                        label: '<?php echo Yii::t('crm', 'No'); ?>',
                    }
                },
                callback: function (result) {
                    if (result) {
                        window.location.reload();
                    }
                }
            });
        }
    }
</script>
