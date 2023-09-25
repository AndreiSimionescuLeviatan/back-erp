<?php

use backend\modules\adm\models\Domain;
use backend\modules\adm\models\Entity;
use backend\modules\adm\models\Subdomain;
use backend\modules\crm\models\Company;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;
use common\components\AppHelper;

/* @var $this yii\web\View */
/* @var $companyModel backend\modules\crm\models\Company */
/* @var $entityDomainModel \backend\modules\crm\models\EntityDomain */
/* @var $ibanCompanyModel \backend\modules\crm\models\IbanCompany */
/* @var $form yii\widgets\ActiveForm */
/* @var $cityList \backend\modules\location\models\City */
/* @var $ibanCompanies yii\widgets\ActiveForm */
/* @var $tva */
/* @var $isNewRecord */

$setTvaFunction = empty($_GET['id']) ? 'addRo(this.id)' : 'addRoCui(this.id)';
if (!empty($_GET['id'])) {
    $company_id = $_GET['id'];
} else {
    $company_id = 0;
}

$idNumber = 1;
?>

<div class="company-form">
    <?php $form = ActiveForm::begin([
        'fieldConfig' => [
            'errorOptions' => ['class' => 'error invalid-tooltip'],
            'labelOptions' => ['class' => 'control-label'],
        ],
        'successCssClass' => '',
    ]); ?>

    <div class="form-row">
        <?php echo $form->field($companyModel, 'name',
            [
                'options' => [
                    'class' => 'form-group col-6',
                    'onKeyUp' => "generateCode(this.name)"
                ]
            ])->label(Yii::t('crm', 'Name'));
        ?>
    </div>
    <div class="form-row">
        <?php echo $form->field($companyModel, 'short_name',
            [
                'options' => [
                    'class' => 'form-group col-6',
                ]
            ])->
        textInput([
            'oninput' => "this.value = this.value.replace(/[^-a-zA-Z0-9\s,ĂăÂâÎîȘșȚț\.]+/g, '').replace(/(\..*?)\..*/g, '$1');"
        ])->label(Yii::t('crm', 'Short Name'));
        ?>
    </div>
    <div class="form-row">
        <?php echo $form->field($companyModel, 'code',
            [
                'options' => [
                    'class' => 'form-group col-6',
                ],
            ])->textInput(['readonly' => true])->label(Yii::t('crm', 'Code'));
        ?>
    </div>
    <div class="form-row">
        <?php
        echo $form->field($companyModel, 'tva', [
            'options' => [
                'class' => 'form-group col-6',
                'onChange' => $setTvaFunction
            ],
        ])->widget(Select2::className(), [
            'data' => Company::$statusTVA,
            'options' => [
                'prompt' => Yii::t('crm', 'This company has TVA?'),
            ],
        ])->label(Yii::t('crm', 'TVA'));
        ?>
    </div>
    <div class="form-row">
        <?php echo $form->field($companyModel, 'cui',
            [
                'options' => [
                    'class' => 'form-group col-6',
                    'style' => 'padding-right: 0px',
                    'onKeyUp' => 'checkCUI()',
                ]
            ])->label(Yii::t('crm', 'CUI'));
        ?>
    </div>
    <div class="form-row">
        <?php echo $form->field($companyModel, 'reg_number',
            [
                'options' => [
                    'class' => 'form-group col-6',
                ]
            ])->label(Yii::t('crm', 'Trade register'));
        ?>
    </div>
    <div class="card col-6" style="background-color: #f8f8ff;" id="ibans-container">
        <?php echo Html::label(Yii::t('crm', 'create_iban'), null, [
            'class' => 'control-label'
        ]);
        foreach ($ibanCompanyModel->list as $key => $iban) {
            echo $this->render('_iban', [
                'key' => $key,
                'isLastIban' => $key == count($ibanCompanyModel->list) - 1,
                'iban' => $iban
            ]) ?>
        <?php } ?>
    </div>

    <div class="form-row">
        <div class="col-lg-6 col-sm-12">
            <div class="row">
                <?php
                echo $form->field($companyModel, 'country_id', ['options' => [
                    'class' => 'form-group col-lg-4 col-sm-12',
                ]])->widget(Select2::className(), [
                    'data' => AppHelper::$names['country'],
                    'options' => [
                        'prompt' => Yii::t('crm', 'Select country...'),
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ])->label(Yii::t('crm', 'Country'));
                ?>
                <?php
                echo $form->field($companyModel, 'state_id',
                    [
                        'options' => [
                            'class' => 'form-group col-lg-4 col-sm-12'
                        ]
                    ])->widget(DepDrop::classname(), [
                    'data' => AppHelper::$names['state'],
                    'options' => [
                        'prompt' => Yii::t('crm', 'Select county...'),
                    ],
                    'type' => DepDrop::TYPE_SELECT2,
                    'pluginOptions' => [
                        'depends' => [
                            Html::getInputId($companyModel, 'country_id')
                        ],
                        'skipDep' => true,
                        'initialize' => true,
                        'initDepends' => [Html::getInputId($companyModel, 'country_id')],
                        'placeholder' => Yii::t('crm', 'Select county...'),
                        'url' => Url::to(['/location/state/get-states']),
                        'loadingText' => '',
                        'emptyMsg' => '',
                    ]
                ])->label(Yii::t('crm', 'County'));
                ?>
                <?php
                echo $form->field($companyModel, 'city_id',
                    [
                        'options' => [
                            'class' => 'form-group col-lg-4 col-sm-12'
                        ]
                    ])->widget(DepDrop::classname(), [
                    'data' => AppHelper::$names['city'],
                    'options' => [
                        'prompt' => Yii::t('crm', 'Select city...'),
                    ],
                    'type' => DepDrop::TYPE_SELECT2,
                    'pluginOptions' => [
                        'depends' => [
                            Html::getInputId($companyModel, 'country_id'),
                            Html::getInputId($companyModel, 'state_id')
                        ],
                        'initialize' => true,
                        'initDepends' => [Html::getInputId($companyModel, 'state_id')],
                        'skipDep' => true,
                        'placeholder' => Yii::t('crm', 'Select city...'),
                        'url' => Url::to(['/location/city/get-cities']),
                        'loadingText' => '',
                        'emptyMsg' => ''
                    ]
                ])->label(Yii::t('crm', 'City'));
                ?>
            </div>
        </div>
    </div>

    <div class="form-row">
        <?php echo $form->field($companyModel, 'address',
            [
                'options' => [
                    'class' => 'form-group col-6',
                ]
            ])->label(Yii::t('crm', 'Address'));
        ?>
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
                            Html::getInputId($companyModel, 'cui'),
                            Html::getInputId($companyModel, 'reg_number')
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
                            Html::getInputId($companyModel, 'cui'),
                            Html::getInputId($companyModel, 'reg_number'),
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
                            Html::getInputId($companyModel, 'cui'),
                            Html::getInputId($companyModel, 'reg_number'),
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
                    $url = Url::previous('company');

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
                    <?php echo Html::submitButton(
                        Yii::t('crm', $companyModel->isNewRecord ? '{icon} Add' : '{icon} Update', ['icon' => '<i class="far fa-save"></i>']),
                        [
                            'id' => 'submit_button',
                            'class' => 'btn btn-success'
                        ]
                    ); ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
$this->registerJs(
    "getChangedData();",
    View::POS_READY,
    'get-change-data'
);

$this->registerJs(
    "backFunction('{$confirmBackMsg}', '{$form->getId()}', '{$url}', 1400);",
    View::POS_LOAD,
    'back-function-handler'
);

$this->registerJs(
    "reloadFunction('{$confirmReloadMsg}', '{$form->getId()}');",
    View::POS_READY,
    'reload-function-handler'
);
?>

<script>
    let initNameValue, initCUIValue, initRegValue, initAddressValue;
    let countStateDropDown;
    var updateForm = false;
    var addedForm = false;

    /**
     * Check if inputs text type or dropdowns are all empties or not
     * @returns {boolean}
     * @author Anca P.
     * @since 19.07.2022
     */
    function checkFillFields() {
        if (addedForm) {
            return !!($('#company-name').val() && $('#company-tva').val() && $('#company-cui').val() && $('#company-reg_number').val());
        } else {
            return !!($('#company-name').val() && $('#company-cui').val() && $('#company-reg_number').val());
        }
    }

    window.addEventListener('load', function () {
        $('#reload_button').attr('disabled', true);
        $('#submit_button').attr('disabled', true);

        if (checkFillFields()) {
            updateForm = true;

            initNameValue = $('#company-name').val();
            initCUIValue = $('#company-cui').val();
            initRegValue = $('#company-reg_number').val();
            initAddressValue = $('#company-address').val()
        } else {
            addedForm = true;
        }

        countStateDropDown = 1;
    })

    /**
     * Disable or enable 'Reset' and 'Update'/'Add' buttons when a field has changed
     * @param fieldsEdit
     * @param type
     * @author Anca P.
     * @since 06.07.2022
     */
    function setChangedField(fieldsEdit, type = '') {
        if (fieldsEdit == 1) {
            $('#reload_button').attr('disabled', false)
            $('#submit_button').attr('disabled', false)
        } else if (type == 'state' && fieldsEdit > 2) {
            $('#reload_button').attr('disabled', false)
            $('#submit_button').attr('disabled', false)
        }
    }

    function getChangedData() {
        $('#company-name').on('keyup', function (e) {
            if ($(this).val() && $(this).val() === initNameValue) {
                $('#reload_button').attr('disabled', true);
                $('#submit_button').attr('disabled', true);
            } else if (!$(this).val()) {
                if (addedForm) {
                    $('#reload_button').attr('disabled', true);
                } else {
                    $('#reload_button').attr('disabled', false);
                }
                $('#submit_button').attr('disabled', true);
            } else {
                setChangedField(1);
            }
        });

        $('#company-tva').on('change', function (e) {
            setChangedField(1);
        });

        $('#company-cui').on('keyup', function (e) {
            if ($(this).val() && $(this).val() === initCUIValue) {
                $('#reload_button').attr('disabled', true);
                $('#submit_button').attr('disabled', true);
            } else if (!$(this).val()) {
                if (addedForm) {
                    $('#reload_button').attr('disabled', true);
                } else {
                    $('#reload_button').attr('disabled', false);
                }
                $('#submit_button').attr('disabled', true);
            } else {
                setChangedField(1);
            }
        });

        $('#company-reg_number').on('keyup', function (e) {
            if ($(this).val() && $(this).val() === initRegValue) {
                $('#reload_button').attr('disabled', true);
                $('#submit_button').attr('disabled', true);
            } else if (!$(this).val()) {
                if (addedForm) {
                    $('#reload_button').attr('disabled', true);
                } else {
                    $('#reload_button').attr('disabled', false);
                }
                $('#submit_button').attr('disabled', true);
            } else {
                setChangedField(1);
            }
        });

        $('#company-address').on('keyup', function (e) {
            if ($(this).val() && $(this).val() === initAddressValue) {
                $('#reload_button').attr('disabled', true);
                $('#submit_button').attr('disabled', true);
            } else if (!$(this).val()) {
                if (addedForm) {
                    $('#reload_button').attr('disabled', true);
                } else {
                    $('#reload_button').attr('disabled', false);
                }
                $('#submit_button').attr('disabled', true);
            } else {
                setChangedField(1);
            }
        });

        $('#iban-input-0').on('keyup', function (e) {
            setChangedField(1);
        });

        $('#iban-input-1').on('keyup', function (e) {
            setChangedField(1);
        });

        $('#company-country_id').on('change', function (e) {
            setChangedField(1);
        });

        $('#company-state_id').on('change', function (e) {
            countStateDropDown += 1;
            setChangedField(countStateDropDown, 'state');
        });
    }

    let ibans = {};

    /**
     * Add code for company base on name inserted by user
     *
     * @author Andrei I.
     * @since 11/05/2022
     */
    function generateCode() {
        let name = $('#company-name').val().toLowerCase().replaceAll(' ', '-')

        $.ajax({
            type: "POST",
            url: '<?php echo Url::to(['/crm/company/generate-code']) ?>',
            data: {
                name: name
            },
            success: function (data) {
                $('#company-code').attr('value', data)
            },
            error: function (XHR) {
                let msg = '<?php echo Yii::t('crm', 'No valid response received from server. Please contact an administrator!');?>';
                if (typeof XHR.responseJSON !== "undefined" && XHR.responseJSON !== '') {
                    if (typeof XHR.responseJSON.message !== "undefined" && XHR.responseJSON.message !== '') {
                        msg = XHR.responseJSON.message
                    }
                }
                bootbox.alert({
                    title: '<?php echo Yii::t('crm', "Error")?>',
                    message: msg,
                    className: 'error-message'
                })
            }
        });
    }

    /**
     * Add RO if company has TVA
     *
     * @author Andrei I.
     * @since 14/06/2022
     */
    function addRo(fieldID) {
        let tva = $('#company-tva' + fieldID).val();
        if ($('#company-tva').val() == 0) {
            if ($('#company-cui').val().indexOf('RO') === -1) {
                let cui = 'RO' + $('#company-cui').val();
                $('#company-cui').val(cui);
            }
        } else {
            if ($('#company-cui').val().indexOf('RO') !== -1) {
                let cui = $('#company-cui').val().replace('RO', '');
                $('#company-cui').val(cui);
            }
        }

        $.ajax({
            type: "GET",
            url: '<?php echo Url::to(['/crm/company/add-ro']) ?>',
            data: {
                tva: tva
            },
            success: function (data) {
                $('#company-cui').attr('value', data);
            },
            error: function (XHR) {
                let msg = '<?php echo Yii::t('crm', 'No valid response received from server. Please contact an administrator!');?>';
                if (typeof XHR.responseJSON !== "undefined" && XHR.responseJSON !== '') {
                    if (typeof XHR.responseJSON.message !== "undefined" && XHR.responseJSON.message !== '') {
                        msg = XHR.responseJSON.message
                    }
                }
                bootbox.alert({
                    title: '<?php echo Yii::t('crm', "Error")?>',
                    message: msg,
                    className: 'error-message'
                })
            }
        });
    }

    /**
     * Add RO if company has TVA
     *
     * @author Andrei I.
     * @since 14/06/2022
     */
    function addRoCui(fieldID) {
        let tva = $('#company-tva' + fieldID).val()
        let company_id = <?php echo $company_id; ?>;
        if ($('#company-tva').val() == 0) {
            if ($('#company-cui').val().indexOf('RO') === -1) {
                let cui = 'RO' + $('#company-cui').val();
                $('#company-cui').val(cui);
            }
        } else {
            if ($('#company-cui').val().indexOf('RO') !== -1) {
                let cui = $('#company-cui').val().replace('RO', '');
                $('#company-cui').val(cui);
            }
        }

        $.ajax({
            type: "GET",
            url: '<?php echo Url::to(['/crm/company/add-ro-cui']) ?>',
            data: {
                tva: tva,
                company_id: company_id
            },
            success: function (data) {
                $('#company-cui').attr('value', data)
            },
            error: function (XHR) {
                let msg = '<?php echo Yii::t('crm', 'No valid response received from server. Please contact an administrator!');?>';
                if (typeof XHR.responseJSON !== "undefined" && XHR.responseJSON !== '') {
                    if (typeof XHR.responseJSON.message !== "undefined" && XHR.responseJSON.message !== '') {
                        msg = XHR.responseJSON.message
                    }
                }
                bootbox.alert({
                    title: '<?php echo Yii::t('crm', "Error")?>',
                    message: msg,
                    className: 'error-message'
                })
            }
        });
    }

    /**
     * Set the value of dropdown TVA - if the cui starts with 'RO', then the TVA will be set to 'Da'
     * 22.08.2022
     */
    function checkCUI() {
        let cui = $('#company-cui').val();
        cui = formatCUI(cui);
        $('#company-cui').val(cui);

        if (cui.startsWith('RO', 0)) {
            $('#company-tva').val(0).trigger('change');
            return;
        }

        $('#company-tva').val(1).trigger('change');
    }

    function startsWith(startText, searchInText) {
        if (searchInText === '') {
            return false;
        }

        const regExpConst = new RegExp(`${startText}`, 'i');
        const result = searchInText.match(regExpConst);

        if(searchInText.match(regExpConst) && result.index === 0) {
            return true;
        }
    }

    function formatCUI(cui) {
        let formatedCUI = cui;
        formatedCUI = formatedCUI.toUpperCase();

        return formatedCUI;
    }

    /**
     * Function for replacing the add icon with remove icon
     *
     * @author Andrei I.
     * @since 27/05/2022
     */
    function changeAddIconToRemove(divToCloneID) {
        $(`#${divToCloneID}`).find('button').toggleClass('btn-success btn-danger')
            .attr('onclick', "removeIban(this)")
            .attr('title', '<?php echo Yii::t('crm', 'Remove IBAN') ?>').html('<i class="fas fa fa-minus"></i>')
    }

    /**
     * Create a new input to write a IBAN
     * First find the id and replace it with new increment id
     * After clone the html div for input field
     * Do not forget to appeal the function for replacing the add icon with remove icon
     *
     * @author Andrei I.
     * @since 27/05/2022
     */
    function addIban(key) {
        let el = $('#iban-container-' + key);
        let newEl = el.clone();
        let newKey = Object.keys(ibans).length;

        el.find('.iban_button')
            .html('<i class="fas fa fa-minus"></i>')
            .attr('class', 'btn btn-danger btn-flat iban-button')
            .attr('title', '<?php echo Yii::t('crm', 'Remove IBAN'); ?>')
            .attr('onclick', 'removeIban(' + key + ')');

        newEl.attr('id', 'iban-container-' + newKey);
        newEl.find('.iban_input')
            .val('')
            .attr('id', 'iban-input-' + newKey)
            .attr('data-iban-key', newKey);
        newEl.find('.iban_button')
            .attr('id', 'iban-button-' + newKey)
            .attr('title', '<?php echo Yii::t('crm', 'For adding a new IBAN, complete first this one'); ?>')
            .attr('onclick', 'addIban(' + newKey + ')')
            .prop('disabled', true);

        newEl.insertAfter(el);
        newEl.find('.iban_input').focus();

        ibans[newKey] = '';
        bindKeyUpEvent(newKey);
    }

    /**
     * Remove a input with IBAN, asking the user if he's sure he want to remove it
     *
     * @author Andrei I.
     * @since 27/05/2022
     */
    function removeIban(key) {
        bootbox.confirm({
            message: '<?php echo Yii::t('crm', "Are you sure you wand to delete all data of this IBAN?") ?>',
            buttons: {
                confirm: {
                    label: 'Da',
                    className: 'btn-danger'
                },
                cancel: {
                    label: 'Nu'
                }
            },
            callback: function (result) {
                if (result) {
                    $('#iban-container-' + key).remove();
                }
            }
        });
    }

    function bindKeyUpEvent(key) {
        $('#iban-input-' + key).on("keyup", function () {
            if ($(this).val() === '') {
                disableAddButton($(this).data('ibanKey'));
            } else {
                enableAddButton($(this).data('ibanKey'));
            }
        });
    }

    function enableAddButton(key) {
        let button = $('#iban-button-' + key);
        button.prop('disabled', false);
        button.prop('title', '<?php echo Yii::t('crm', 'Add new IBAN') ?>');
    }

    function disableAddButton(key) {
        let button = $('#iban-button-' + key);
        button.prop('disabled', true);
        button.prop('title', '<?php echo Yii::t('crm', 'For adding a new IBAN, complete first this one') ?>');
    }

    window.onload = function (e) {
        <?php foreach ($ibanCompanyModel->list as $key => $iban) { ?>
        ibans[<?php echo $key; ?>] = '<?php echo $iban; ?>';
        bindKeyUpEvent(parseInt(<?php echo $key; ?>));
        <?php } ?>
    }
</script>
