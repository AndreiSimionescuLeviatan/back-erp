<?php

use backend\modules\adm\models\Domain;
use backend\modules\adm\models\Entity;
use backend\modules\adm\models\Subdomain;
use kartik\depdrop\DepDrop;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\crm\models\Brand */
/* @var $entityDomainModel \backend\modules\crm\models\EntityDomain */
/* @var $existingBrand backend\modules\crm\models\Brand */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="brand-form">
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
                echo $form->field($model, 'name',
                    [
                        'options' => [
                            'class' => 'form-group col-12 '
                        ]
                    ])
                    ->textInput([
                        'maxlength' => true,
                        'class' => 'form-control replace-apostrophe',
                        'oninput' => "this.value = this.value.replace(//\d+|^\s+$[^a-zA-Z0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');",
                    ])
                    ->label(Yii::t('crm', 'Name'));
                ?>
            </div>
        </div>
    </div>
    <div class="form-row">
        <div class="col-lg-6 col-sm-12">
            <div class="row">
                <?php
                echo $form->field($entityDomainModel, 'domain_id',
                    [
                        'options' => [
                            'class' => 'form-group col-lg-4 col-sm-12'
                        ]
                    ])->widget(DepDrop::classname(), [
                    'data' => Domain::$names,
                    'options' => [
                        'prompt' => Yii::t('crm', 'Select {item} domain', ['item' => 'brand']),
                    ],
                    'pluginOptions' => [
                        'depends' => [
                            Html::getInputId($model, 'brand_id'),
                            Html::getInputId($model, 'name')
                        ],
                        'skipDep' => true,
                        'placeholder' => Yii::t('crm', 'Select {item} domain', ['item' => 'brand']),
                        'url' => Url::to(['/adm/domain/get-domains']),
                        'loadingText' => '',
                        'emptyMsg' => ''
                    ]
                ]);
                ?>
                <?php
                echo $form->field($entityDomainModel, 'entity_id',
                    [
                        'options' => [
                            'class' => 'form-group col-lg-4 col-sm-12'
                        ]
                    ])->widget(DepDrop::classname(), [
                    'data' => Entity::$names,
                    'options' => [
                        'prompt' => Yii::t('crm', 'Select {item} entity', ['item' => 'brand']),
                    ],
                    'type' => DepDrop::TYPE_SELECT2,
                    'pluginOptions' => [
                        'depends' => [
                            Html::getInputId($model, 'name'),
                            Html::getInputId($entityDomainModel, 'domain_id')
                        ],
                        'skipDep' => true,
                        'initialize' => true,
                        'initDepends' => [Html::getInputId($entityDomainModel, 'domain_id')],
                        'placeholder' => Yii::t('crm', 'Select {item} entity', ['item' => 'brand']),
                        'url' => Url::to(['/adm/entity/get-entities']),
                        'loadingText' => '',
                        'emptyMsg' => ''
                    ]
                ]);
                ?>
                <?php
                echo $form->field($entityDomainModel, 'subdomain_id',
                    [
                        'options' => [
                            'class' => 'form-group col-lg-4 col-sm-12'
                        ]
                    ])->widget(DepDrop::classname(), [
                    'data' => Subdomain::$names,
                    'options' => [
                        'prompt' => Yii::t('crm', 'Select {item} subdomain', ['item' => 'brand']),
                    ],
                    'type' => DepDrop::TYPE_SELECT2,
                    'pluginOptions' => [
                        'depends' => [
                            Html::getInputId($model, 'name'),
                            Html::getInputId($entityDomainModel, 'domain_id'),
                            Html::getInputId($entityDomainModel, 'entity_id')
                        ],
                        'initialize' => true,
                        'initDepends' => [Html::getInputId($entityDomainModel, 'entity_id')],
                        'skipDep' => true,
                        'placeholder' => Yii::t('crm', 'Select {item} subdomain', ['item' => 'brand']),
                        'url' => Url::to(['/adm/subdomain/get-subdomains']),
                        'loadingText' => '',
                        'emptyMsg' => ''
                    ]
                ]);
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
                    $url = Url::previous('brand');

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
                    if (!empty($existingBrand)) {
                        $classButtonSave = 'display-buttons-save-activate';
                        $classButtonActivate = '';
                        $urlActivate = Url::to(['activate', 'id' => $existingBrand->id]);
                    } else {
                        $classButtonActivate = 'display-buttons-save-activate';
                        $urlActivate = null;
                    } ?>
                    <?php $confirmDeleteMultiple = Yii::t('crm', 'Are you sure you want to activate this {item}?',
                        ['item' => Yii::t('crm', 'brand')]);
                    echo Html::button(
                        Yii::t('crm', '{icon} Activate',
                            [
                                'icon' => '<i class="fas fa-trash-restore-alt"></i>'
                            ]),
                        [
                            'id' => 'button_activate',
                            'class' => 'btn btn-warning ' . $classButtonActivate,
                            'onClick' => 'deleteActivateRecord("' . $confirmDeleteMultiple . '", "' . $urlActivate . '");'
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

$this->registerJs(
    "replaceApostrophe();",
    View::POS_READY,
    'replace-apostrophe-handler'
);
?>

