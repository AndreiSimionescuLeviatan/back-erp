<?php

use backend\modules\crm\models\Company;
use backend\modules\hr\models\Employee;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\adm\models\ErpCompany */
/* @var $form yii\widgets\ActiveForm */
?>

    <div class="erp-company-form">
        <?php $form = ActiveForm::begin([
            'fieldConfig' => [
                'errorOptions' => ['class' => 'error invalid-tooltip'],
                'labelOptions' => ['class' => 'control-label'],
            ],
            'successCssClass' => '',
        ]); ?>

        <div class="form-row">
            <?php echo $form->field($model, 'company_id',
                [
                    'options' => [
                        'class' => 'form-group col-6',
                    ]
                ])->widget(Select2::className(), [
                'data' => Company::$names,
                'options' => [
                    'prompt' => Yii::t('adm', 'Select company')
                ],
                'pluginOptions' => ['allowClear' => true]
            ])->label(Yii::t('adm', 'Company'));
            ?>
        </div>

        <div class="row">
            <div class="col-lg-6 col-sm-12">
                <div class="form-row">
                    <?php echo $form->field($model, 'general_manager_id',
                        [
                            'options' => [
                                'class' => 'form-group col',
                            ]
                        ])->widget(Select2::className(), [
                        'data' => Employee::$employees,
                        'options' => [
                            'prompt' => Yii::t('adm', 'Select company general manager')
                        ],
                        'pluginOptions' => ['allowClear' => true]
                    ])->label(Yii::t('adm', 'General manager'));
                    ?>
                    <?php echo $form->field($model, 'deputy_general_manager_id',
                        [
                            'options' => [
                                'class' => 'form-group col',
                            ]
                        ])->widget(Select2::className(), [
                        'data' => Employee::$employees,
                        'options' => [
                            'prompt' => Yii::t('adm', 'Select company deputy general manager')
                        ],
                        'pluginOptions' => ['allowClear' => true]
                    ])->label(Yii::t('adm', 'Deputy general manager'));
                    ?>
                </div>
                <div class="form-row">
                    <?php echo $form->field($model, 'technical_manager_id',
                        [
                            'options' => [
                                'class' => 'form-group col',
                            ]
                        ])->widget(Select2::className(), [
                        'data' => Employee::$employees,
                        'options' => [
                            'prompt' => Yii::t('adm', 'Select company technical manager')
                        ],
                        'pluginOptions' => ['allowClear' => true]
                    ])->label(Yii::t('adm', 'Technical manager'));
                    ?>
                    <?php echo $form->field($model, 'executive_manager_id',
                        [
                            'options' => [
                                'class' => 'form-group col',
                            ]
                        ])->widget(Select2::className(), [
                        'data' => Employee::$employees,
                        'options' => [
                            'prompt' => Yii::t('adm', 'Select company executive manager')
                        ],
                        'pluginOptions' => ['allowClear' => true]
                    ])->label(Yii::t('adm', 'Executive manager'));
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-sm-12">
                <div class="form-row">
                    <div class="form-group col-auto">
                        <?php
                        $confirmBackMsg = Yii::t('adm', 'Are you sure you want to leave?');
                        $url = Url::previous('erp-company');
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
                    <div class="form-group col-auto ml-auto">
                        <?php
                        echo Html::submitButton(Yii::t('adm', 'Save'),
                            [
                                'class' => 'btn btn-success'
                            ])
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
?>