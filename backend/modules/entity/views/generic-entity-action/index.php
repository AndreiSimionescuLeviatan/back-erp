<?php

use backend\modules\entity\models\Domain;
use backend\modules\entity\models\Entity;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $dataProvider */
/* @var $domainId */
/* @var $entityId */
/* @var $categoryIds */
/* @var $specialities */
/* @var $specialityId */

$this->title = Yii::t('entity', 'Replace');
$this->params['breadcrumbs'][] = ['label' => Yii::t('entity', 'History find&replace'), 'url' => ['entity-action-log/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="article-replace">
    <h1>
        <?php echo Html::encode($this->title); ?>
    </h1>

    <?php $form = ActiveForm::begin([
        'fieldConfig' => [
            'errorOptions' => ['class' => 'error invalid-tooltip'],
            'labelOptions' => ['class' => 'control-label'],
        ],
        'successCssClass' => '',
    ]); ?>
    <div class="row">
        <div class="col-md-6">

            <div class="form-group required">
                <label class="control-label" for="domain_id"><?php echo Yii::t('entity', 'Domain'); ?></label>
                <?php echo Select2::widget([
                    'name' => 'domain_id',
                    'data' => Domain::$names,
                    'options' => [
                        'id' => 'domain_id',
                        'placeholder' => Yii::t('entity', 'Select domain'),
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ]
                ]); ?>
            </div>

            <div class="form-group required">
                <label class="control-label" for="entity_id"><?php echo Yii::t('entity', 'Entity'); ?></label>
                <?php echo DepDrop::widget([
                    'name' => 'entity_id',
                    'type' => DepDrop::TYPE_SELECT2,
                    'data' => [],
                    'options' => [
                        'id' => 'entity_id',
                        'class' => 'different_search_method',
                        'placeholder' => Yii::t('entity', 'Select entity'),
                        'onChange' => 'loadLocationReplace()',
                    ],
                    'pluginOptions' => [
                        'depends' => ['domain_id'],
                        'skipDep' => false,
                        'placeholder' => Yii::t('entity', 'Select entity'),
                        'url' => Url::to(['/entity/generic-entity-action/get-entities']),
                        'loadingText' => Yii::t('entity', 'Select entity') . '...',
                        'emptyMsg' => Yii::t('entity', 'Select entity'),
                    ]
                ]); ?>
            </div>

            <div class="form-group control-speciality required d-none">
                <label class="control-label"
                       for="speciality_id"><?php echo Yii::t('entity', 'Speciality'); ?></label>
                <?php echo Select2::widget([
                    'name' => 'speciality_id',
                    'data' => $specialities,
                    'options' => [
                        'id' => 'speciality_id',
                        'placeholder' => Yii::t('entity', 'Select speciality'),
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ]
                ]); ?>
            </div>

            <div class="form-group required">
                <label class="control-label" for="new_entity"><?php echo Yii::t('entity', 'New entity'); ?></label>
                <?php echo DepDrop::widget([
                    'name' => 'new_entity',
                    'type' => DepDrop::TYPE_SELECT2,
                    'data' => [],
                    'options' => [
                        'id' => 'new_entity',
                        'class' => 'different_search_method',
                        'placeholder' => Yii::t('entity', 'Select new entity'),
                    ],
                    'pluginOptions' => [
                        'depends' => ['entity_id'],
                        'initDepends' => ['domain_id', 'entity_id'],
                        'initialize' => false,
                        'skipDep' => false,
                        'placeholder' => Yii::t('entity', 'Select new entity'),
                        'url' => Url::to(['/entity/generic-entity-action/get-entities-value-replace']),
                        'loadingText' => Yii::t('entity', 'Select new entity') . '...',
                        'emptyMsg' => Yii::t('entity', 'Select new entity'),
                        'params' => ['domain_id', 'speciality_id'],
                    ]
                ]); ?>
            </div>

            <div class="form-group required">
                <label class="control-label" for="old_entity_ids"><?php echo Yii::t('entity', 'Old entities'); ?></label>
                <?php echo DepDrop::widget([
                    'name' => 'old_entities',
                    'type' => DepDrop::TYPE_SELECT2,
                    'data' => [],
                    'options' => [
                        'id' => 'old_entity_ids',
                        'class' => 'different_search_method',
                        'placeholder' => Yii::t('entity', 'Select old entities'),
                    ],
                    'select2Options' => [
                        'pluginOptions' => [
                            'multiple' => true,
                        ],
                        'toggleAllSettings' => [
                            'selectLabel' => '<i class="far fa-square mr-1 me-1"></i>' . Yii::t('entity', 'Select all'),
                            'unselectLabel' => '<i class="far fa-square mr-1 me-1"></i>' . Yii::t('entity', 'Unselect all'),
                        ],
                    ],
                    'pluginOptions' => [
                        'depends' => ['entity_id'],
                        'initDepends' => ['domain_id', 'entity_id'],
                        'initialize' => false,
                        'skipDep' => false,
                        'placeholder' => Yii::t('entity', 'Select old entities'),
                        'url' => Url::to(['/entity/generic-entity-action/get-entities-value-replace']),
                        'loadingText' => Yii::t('entity', 'Select old entities') . '...',
                        'emptyMsg' => Yii::t('entity', 'Select old entities'),
                        'params' => ['domain_id', 'speciality_id'],
                    ]
                ]); ?>
            </div>
        </div>

        <div class="col-md-6 grid_entity_description d-none" id="grid_entity_description">
            <?php echo $this->render('_grid_entity_description', [
                'dataProvider' => $dataProvider,
            ]); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="row">
                <div class="form-group col-auto ">
                    <?php
                    $confirmBackMsg = Yii::t('entity', "The data will be lost. Are you sure you want to leave?");
                    $url = Url::to(['entity-action-log/index']);

                    echo Html::button(Yii::t('build', '{icon} Back', ['icon' => '<i class="fas fa-chevron-circle-left"></i>']), [
                        'id' => 'back_button',
                        'class' => 'btn btn-info'
                    ]);
                    ?>
                </div>
                <div class="from-group col-auto ml-auto text-right">
                    <?php echo Html::submitButton(
                        Yii::t('app', '{icon} Save', ['icon' => '<i class="far fa-save"></i>']), [
                        'id' => 'button_save',
                        'class' => 'btn btn-success',
                        'style' => 'float: right'
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
    "loadDefaultValue();",
    View::POS_READY,
    'load-defaul-value-handler'
);

?>
<script>

    let defaultEntityId = '<?php echo $entityId; ?>';
    let defaultCategoryIds = '<?php echo $categoryIds; ?>';
    let entitiesVisibleSpeciality = [parseInt( <?php echo Entity::BUILD_ARTICLE_ID; ?>), parseInt( <?php echo Entity::BUILD_PACKAGE_ID; ?>)];
    let defaultSpecialityId = '<?php echo $specialityId; ?>';

    function loadDefaultValue()
    {
        $('#domain_id').on('change', function (event, id, value) {
            $(`.control-speciality`).addClass('d-none');
            if (defaultEntityId.length === 0) {
                return;
            }
            $('#entity_id').trigger('change').trigger('depdrop:change');
        });

        $('#entity_id').on('depdrop:afterChange', function (event, id, value) {
            let specialityValue = null;
            if (defaultSpecialityId.length !== 0) {
                specialityValue = defaultSpecialityId;
                defaultSpecialityId = '';
            }
            $('#speciality_id').val(specialityValue).trigger('change');
            if (defaultEntityId.length === 0) {
                return;
            }
            $('#entity_id').val(defaultEntityId).trigger('change').trigger('depdrop:change');
            defaultEntityId = '';
        });

        $('#new_entity').on('depdrop:afterChange', function (event, id, value) {
            clearValuesToReplace();
        });

        $('#old_entity_ids').on('depdrop:afterChange', function (event, id, value) {
            clearValuesToReplace();
        });

        $('#speciality_id').on('change', function (event, id, value) {
            $('#entity_id').trigger('change').trigger('depdrop:change');
        });

        let domainId = '<?php echo $domainId; ?>';
        if (domainId.length === 0) {
            return;
        }
        $('#domain_id').val(domainId).trigger('change').trigger('depdrop:change');
    }

    function clearValuesToReplace()
    {
        let entityId = $('#entity_id').val();
        let specialityId = $('#speciality_id').val();

        if (entityId.length === 0) {
            return;
        }

        if (
            specialityId.length === 0
            && entitiesVisibleSpeciality.includes(parseInt(entityId))
        ) {
            $('#new_entity, #old_entity_ids').empty();
        }
    }

    function loadLocationReplace()
    {
        let entityId = $('#entity_id').val();
        if (entityId === null || entityId.length === 0) {
            $(`#grid_entity_description`).html('');
            return;
        }

        if (entitiesVisibleSpeciality.includes(parseInt(entityId))) {
            $(`.control-speciality`).removeClass('d-none');
        } else {
            $(`.control-speciality`).addClass('d-none');
        }

        $.ajax({
            type: "POST",
            url: '<?php echo Url::to(['/entity/generic-entity-action/get-location-replace']); ?>',
            data: {
                entityId: entityId,
            },
            success: function (data) {
                if (data) {
                    $('#grid_entity_description').removeClass('d-none');
                    $(`#grid_entity_description`).html(data);
                    insertLabelCheck();
                    setDefaultEntityChange();
                } else {
                    $('#grid_entity_description').addClass('d-none');
                }
            },
            error: function (XHR) {
                $('#grid_entity_description').addClass('d-none');
                let msg = '<?php echo Yii::t('entity', 'No valid response received from server. Please contact an administrator!'); ?>';
                displayRequestError(XHR, msg);
            },
        });
    }

    function insertLabelCheck()
    {
        let labelCheck = document.createElement("label");
        labelCheck.innerText = '<?php echo Yii::t('entity', 'Check'); ?>';
        let checkboxAll = document.getElementsByName("selection_all")[0];
        let thCheck = document.getElementById("th-check");
        thCheck.insertBefore(labelCheck, checkboxAll);
    }

    function setDefaultEntityChange()
    {
        if (defaultCategoryIds.length === 0) {
            return;
        }

        let gridRows = $('#kv-grid-demo tbody tr');
        let categoryIds = defaultCategoryIds.split(",");

        if (gridRows.length === categoryIds.length) {
            $('.select-on-check-all').prop('checked', true);
        }

        categoryIds.forEach(function (entityId) {
            let tr = $('#kv-grid-demo').find('tr[data-key="' + entityId + '"]');
            if (tr.length > 0) {
                tr.addClass('table-danger');
                let checkbox = $('#kv-grid-demo').find('#td-checkbox-' + entityId);
                checkbox.prop('checked', true);
            }
        });

        defaultCategoryIds = '';
    }

    function displayRequestError(XHR, msg)
    {
        if (
            typeof XHR.responseJSON !== "undefined"
            && XHR.responseJSON !== ''
            && typeof XHR.responseJSON.message !== "undefined"
            && XHR.responseJSON.message !== ''
        ) {
            msg = XHR.responseJSON.message;
        }
        bootbox.alert({
            title: '<?php echo Yii::t('entity', "Error"); ?>',
            message: msg,
            className: 'error-message'
        });
    }

</script>
