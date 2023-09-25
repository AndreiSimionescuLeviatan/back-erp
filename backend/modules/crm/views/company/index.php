<?php

use backend\modules\adm\models\User;
use backend\modules\crm\models\Company;
use common\components\AppHelper;
use kartik\grid\GridView;
use mdm\admin\components\Helper;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\crm\models\search\CompanySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('crm', 'Companies');
$this->params['breadcrumbs'][] = $this->title;

User::setUsers(true);

$hasPermission = AppHelper::checkPermissionViewDeletedEntities($_GET['CompanySearch']['deleted'] ?? '', 'activateCompany');
$toggleStatus = !$hasPermission ? 'checked' : '';
$activeDeletedEntities = isset($_GET['CompanySearch']['deleted']) && $_GET['CompanySearch']['deleted'] == 1 ? 1 : 0;
$viewEntitiesToggle = '<label class="personalized-toggle mb-0 mt-1">' .
    '<input id="switch_view_toggle_id" onchange="viewToggleChange()" type="checkbox" ' . $toggleStatus . '>' .
    '<span class="personalized-toggle-slider"></span>' .
    '</label>' .
    '<input type="hidden" id="toggle-status" name="CompanySearch[deleted]" value="' . $activeDeletedEntities . '">';

$headerAction = (Yii::$app->user->can('createCompany') ? Yii::t('crm', 'Actions') : '');
$columnVisibility = Yii::$app->user->can('updateCompany');
$columnPosition = 2;

$buttonVisibilityDelete = isset($_GET['CompanySearch']['deleted']) && $_GET['CompanySearch']['deleted'] == 1 ? 'd-none' : '';
$buttonVisibilityActivate = !isset($_GET['CompanySearch']['deleted']) || $_GET['CompanySearch']['deleted'] == 0 ? 'd-none' : '';
?>

<style>
    .select-checkbox-company {
        position: absolute;
        margin-top: 50px;
        margin-left: 15px
    }

    .floating-bar-btn {
        width: 130px;
    }

    .btn-width {
        width: 100px;
    }

    input[type="checkbox"] {
        width: 16px;
        height: 16px;
        border: 1px solid lightgrey;
        background: whitesmoke;
    }

    #card-multiple-validate-company {
        background-color: #2573AD;
        position: sticky;
        bottom: 0;
        z-index: 10;
    }

    .multiple-changes-company {
        display: none;
    }

    .bg-color-selected {
        background-color: #b7C3cb !important;
    }
</style>

<div class="company-index">

    <h1>
        <?= Html::encode($this->title) ?>
        <?php if (Yii::$app->user->can('createCompany')) {
            echo Html::a(Yii::t('crm', '{icon} Add', [
                'icon' => '<i class="fas fa-plus"></i>'
            ]), ['create'], ['class' => 'btn btn-sm btn-success btn-width mr-2']);
        }
        if (Yii::$app->user->can('importCompany')) {
            echo Html::a(Yii::t('crm', '{icon} Import', [
                'icon' => '<i class="fas fa-upload"></i>'
            ]), ['import'], ['class' => 'btn btn-sm btn-info btn-width']);
        }
        ?>
    </h1>

    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summary' => AppHelper::setGridViewTableLayout($searchModel, $dataProvider->getTotalCount())['summary'],
        'layout' => AppHelper::setGridViewTableLayout($searchModel, $dataProvider->getTotalCount())['layout'],
        'tableOptions' => AppHelper::setGridViewTableLayout($searchModel, $dataProvider->getTotalCount())['tableOptions'],
        'headerRowOptions' => ['id' => 'w0-headers'],
        'rowOptions' => function ($model) {

            return [
                'id' => "table_el_{$model->id}",
                'class' => 'tr-body',
                'data-status' => "{$model->deleted}"];
        },
        'columns' => [
            [
                'header' => Html::checkBox('selection_all', false, [
                    'class' => 'text-center checkbox company_rows select-checkbox-company',
                    'id' => 'selected-company',
                    'label' => 'Select',
                    'onchange' => "selectAllCompanies(this)",
                ]),
                'headerOptions' => ['class' => "text-center pt-2", 'width' => '80px'],
                'contentOptions' => function ($model) {
                    return [
                        'class' => "text-center checkbox company-checkbox",
                        'style' => 'vertical-align: middle; width: 80px; position: relative;',
                    ];
                },
                'format' => 'raw',
                'enableSorting' => false,
                'filter' => false,
                'attribute' => 'id',
                'value' => function ($model) {
                    return Html::checkBox('checkbox', false, [
                        'class' => 'text-center',
                        'id' => "checkbox_{$model->id}",
                    ]);
                }
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '100px'],
                'contentOptions' => ['class' => 'text-center', 'style' => ['width' => '100px']],
                'class' => 'yii\grid\ActionColumn',
                'template' => Helper::filterActionColumn('{view} {update}') . ' {delete-activate}',
                'header' => $headerAction,
                'visible' => $columnVisibility,
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        $ac = new ActionColumn();
                        if (Yii::$app->user->can('viewCompany')) {
                            return Html::a($ac->icons['eye-open'],
                                [
                                    'view', 'id' => $model->id,
                                    'page' => isset($_GET['page']) ? $_GET['page'] : null
                                ],
                                [
                                    'class' => 'btn btn-xs btn-info',
                                    'style' => 'width: 24px',
                                    'data-toggle' => 'tooltip',
                                    'title' => Yii::t('crm', 'View more details')
                                ]);
                        }
                        return null;
                    },
                    'update' => function ($url, $model, $key) {
                        $ac = new ActionColumn();
                        if (Yii::$app->user->can('updateCompany')) {
                            return Html::a($ac->icons['pencil'],
                                [
                                    'update', 'id' => $model->id,
                                    'page' => isset($_GET['page']) ? $_GET['page'] : null
                                ],
                                [
                                    'class' => 'btn btn-xs btn-warning text-white',
                                    'style' => 'width: 24px',
                                    'data-toggle' => 'tooltip',
                                    'title' => Yii::t('crm', 'Edit')
                                ]);
                        }
                        return null;
                    },
                    'delete-activate' => function ($url, $model, $key) use ($hasPermission) {
                        if (Yii::$app->user->can('deleteCompany')) {
                            $action = 'delete';
                            if ($hasPermission) {
                                $action = 'activate';
                            }

                            $url = Url::to(["/crm/company/{$action}", 'id' => $model->id]);

                            $confirmDeleteActivate = Yii::t('crm', 'Are you sure you want to {action} this {item}?',
                                [
                                    'action' => Yii::t('crm', $action),
                                    'item' => Yii::t('crm', 'company')
                                ]);
                            $ac = new ActionColumn();
                            return Html::button($action === 'delete' ? $ac->icons['trash'] : '<i class="fab fa-rev"></i>',
                                [
                                    'class' => $action === 'delete' ? 'btn btn-xs btn-danger' : 'btn btn-xs btn-success',
                                    'style' => 'color:white; width:24px',
                                    'data-toggle' => 'tooltip',
                                    'title' => $action === 'delete' ? Yii::t('crm', 'Delete') : Yii::t('crm', 'Activate'),
                                    'onClick' => 'deleteActivateRecord("' . $confirmDeleteActivate . '", "' . $url . '");'
                                ]);
                        }
                        return null;
                    }
                ]
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '90px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('crm', 'Id'),
                'attribute' => 'id'
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '150px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('crm', 'CUI'),
                'attribute' => 'cui'
            ],
            [
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('crm', 'Code'),
                'attribute' => 'code'
            ],
            [
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('crm', 'Name'),
                'attribute' => 'name'
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '150px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('crm', 'Added'),
                'attribute' => 'added_by',
                'format' => 'html',
                'value' => function ($model) {
                    return $model->added . "<br>" . (!empty(User::$users[$model->added_by]) ? User::$users[$model->added_by] : '-');
                },
                'filter' => Company::$filtersOptions['added_by'],
                'filterType' => GridView::FILTER_SELECT2,
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'prompt' => Yii::t('crm', 'Select user'),
                ]
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '150px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('crm', 'Updated'),
                'attribute' => 'updated_by',
                'format' => 'html',
                'value' => function ($model) {
                    return $model->updated . "<br>" . (!empty(User::$users[$model->updated_by]) ? User::$users[$model->updated_by] : '-');
                },
                'filter' => Company::$filtersOptions['updated_by'],
                'filterType' => GridView::FILTER_SELECT2,
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'prompt' => Yii::t('crm', 'Select user'),
                ]
            ]
        ],
        'pager' => [
            'options' => ['class' => 'pagination m-0 justify-content-end'],
            'linkContainerOptions' => ['class' => 'page-item'],
            'linkOptions' => ['class' => 'page-link'],
            'disableCurrentPageButton' => true,
            'disabledListItemSubTagOptions' => ['class' => 'page-link'],
            'firstPageLabel' => Yii::t('crm', 'First page'),
            'lastPageLabel' => Yii::t('crm', 'Last page')
        ]
    ]); ?>
</div>

<div class="card mb-0 multiple-changes-company" id="card-multiple-validate-company">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col">
                <?php
                echo Html::button(Yii::t('crm', '{icon} Activate', [
                    'icon' => '<i class="fas fa-check"></i>'
                ]), [
                    'id' => 'company-activate',
                    'class' => "btn btn-success floating-bar-btn {$buttonVisibilityActivate}",
                    'data-toggle' => 'tooltip',
                    'title' => Yii::t('crm', 'Activate'),
                    'onclick' => "setDeleteActivate(0)"
                ]);
                ?>
                <?php
                echo Html::button(Yii::t('crm', '{icon} Delete', [
                    'icon' => '<i class="fas fa-trash-alt"></i>'
                ]), [
                    'id' => 'company-delete',
                    'class' => "btn btn-danger floating-bar-btn {$buttonVisibilityDelete}",
                    'data-toggle' => 'tooltip',
                    'title' => Yii::t('crm', 'Delete'),
                    'onclick' => "setDeleteActivate(1)"
                ]);
                ?>
            </div>
        </div>
    </div>
</div>

<?php
if (Yii::$app->user->can('activateCompany')) {
    $this->registerJs(
        "activeDeletedLabel($activeDeletedEntities, '$viewEntitiesToggle', $columnPosition);",
        View::POS_READY,
        'active-deleted-label-custom-handler'
    );

    $this->registerJs(
        "viewToggleChange('CompanySearch[deleted]');",
        View::POS_READY,
        'view-toggle-change-handler'
    );
}

$this->registerJs(
    "setPageSize('CompanySearch[pageSize]');",
    View::POS_READY,
    'grid-view-page-size'
);

$this->registerJs(
    "bindClickOnTableRowsEvent();",
    View::POS_READY,
    'bind-click-on-table-rows-event'
);
?>

<script>
    let minCountPagination, maxCountPagination, totalCountRows = 0;
    let selectedCompaniesIDs = [];
    let totalSelectedCompanies = {
        validated: 0,
        not_validated: 0
    };

    function setDeleteActivate(status) {
        $.ajax({
            type: "POST",
            url: '<?php echo Url::to(['company/delete-activate']) ?>',
            data: {
                status: status,
                selectedCompaniesIDs: selectedCompaniesIDs,
            },
            success: function (data) {
            },
            error: function (error) {
                bootbox.alert('<?php echo Yii::t('crm', 'Error') ?>').addClass('common-error-message')
            },
        });
    }

    function bindClickOnTableRowsEvent() {
        $('.company-checkbox').on('click', function (el) {
            let id = el.target.id.replace('checkbox_', '');
            if ($('#checkbox_' + id).is(':checked')) {
                $('#checkbox_' + id).prop('checked', false);
            } else {
                $('#checkbox_' + id).prop('checked', true);
            }
        });

        $('.tr-body').on('click', function () {
            let companyID = $(this).attr('id').replace('table_el_', '');
            toggleCompany(companyID);
        });
    }

    function selectAllCompanies(el) {
        let checked = $(el).is(':checked');
        if (checked) {
            unCheckedAllBoxes();
            totalSelectedCompanies = {
                validated: 0,
                not_validated: 0
            };
            selectedCompaniesIDs = [];
            $('.tr-body').each(function () {
                let companyID = $(this).attr('id').replace('table_el_', '');
                toggleCompany(companyID);
            });
        } else {
            unCheckedAllBoxes();
            totalSelectedCompanies = {
                validated: 0,
                not_validated: 0
            };
            selectedCompaniesIDs = [];
            toggleFloatingBar();
        }

    }

    function toggleRow(companyID) {
        $('#table_el_' + companyID).removeClass('bg-color-selected');
        if ($('#checkbox_' + companyID).is(':checked')) {
            $('#table_el_' + companyID).addClass('bg-color-selected');
        }
    }

    function toggleDeleteButton() {
        $('#company-delete').addClass('d-none');
        if (totalSelectedCompanies['not_validated'] > 0 && totalSelectedCompanies['validated'] <= 0 && $('#switch_view_toggle_id').attr('checked')) {
            $('#company-delete').removeClass('d-none');
        }
    }

    function toggleActivateButton() {
        $('#company-activate').addClass('d-none');
        if ($('#switch_view_toggle_id').attr('checked')) {
            return;
        }
        $('#company-activate').removeClass('d-none');
    }

    function toggleFloatingBar() {
        if (totalSelectedCompanies['not_validated'] > 1 || totalSelectedCompanies['validated'] > 1) {
            $('.multiple-changes-company').removeClass('d-none').addClass('d-flex');
            toggleDeleteButton();
            toggleActivateButton();
            return;
        }
        $('.multiple-changes-company').removeClass('d-flex').addClass('d-none');
    }

    function toggleCompany(companyID) {
        if ($.inArray(companyID, selectedCompaniesIDs) !== -1) {
            selectedCompaniesIDs.splice(selectedCompaniesIDs.indexOf(companyID), 1);
        } else {
            selectedCompaniesIDs.push(companyID);
        }
        let tableRow = $('#table_el_' + companyID);
        if (tableRow.data('status') === 1) {
            if (tableRow.attr('class') !== 'tr-body w0 bg-color-selected') {
                totalSelectedCompanies['validated']++;
            } else {
                totalSelectedCompanies['validated']--;
            }
        } else if (tableRow.data('status') === 0) {
            if (tableRow.attr('class') !== 'tr-body w0 bg-color-selected') {
                totalSelectedCompanies['not_validated']++;
            } else {
                totalSelectedCompanies['not_validated']--;
            }
        }

        maxCountPagination = parseInt($('.max-rows').html());
        minCountPagination = parseInt($('.min-rows').html());
        totalCountRows = maxCountPagination - minCountPagination + 1;

        if (totalSelectedCompanies['validated'] + totalSelectedCompanies['not_validated'] !== totalCountRows) {
            $('#selected-company').prop('checked', false);
        } else {
            $('#selected-company').prop('checked', true);
        }

        toggleCheckbox(companyID);
        toggleRow(companyID);
        toggleFloatingBar();
    }

    function toggleCheckbox(companyID) {
        if ($('#checkbox_' + companyID).is(':checked')) {
            $('#checkbox_' + companyID).prop('checked', false);
            return;
        }
        $('#checkbox_' + companyID).prop('checked', true);
    }

    function unCheckedAllBoxes() {
        for (let i = 0; i <= selectedCompaniesIDs.length; ++i) {
            $('#checkbox_' + selectedCompaniesIDs[i]).prop('checked', false);
            $('#table_el_' + selectedCompaniesIDs[i]).removeClass('bg-color-selected');
        }
    }

    function checkAllCheckboxes() {
        for (let i = 0; i <= selectedCompaniesIDs.length; ++i) {
            $('#checkbox_' + selectedCompaniesIDs[i]).prop('checked', true);
            $('#table_el_' + selectedCompaniesIDs[i]).addClass('bg-color-selected');
        }
    }
</script>
