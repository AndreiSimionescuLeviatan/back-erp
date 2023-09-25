<?php

use backend\modules\adm\models\User;
use backend\modules\crm\models\Company;
use common\components\AppHelper;
use mdm\admin\components\Helper;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\crm\models\search\ContractOfferSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('crm', 'Contract offers');
$this->params['breadcrumbs'][] = $this->title;

$dropDownsWidth = '220px';

$hasPermission = AppHelper::checkPermissionViewDeletedEntities($_GET['ContractOfferSearch']['deleted'] ?? '', 'activateContractOffer');
$toggleStatus = !$hasPermission ? 'checked' : '';
$activeDeletedEntities = isset($_GET['CompanySearch']['deleted']) && $_GET['ContractOfferSearch']['deleted'] == 1 ? 1 : 0;
$viewEntitiesToggle = '<label class="personalized-toggle mb-0 mt-1">' .
    '<input id="switch_view_toggle_id" onchange="viewToggleChange()" type="checkbox" ' . $toggleStatus . '>' .
    '<span class="personalized-toggle-slider"></span>' .
    '</label>' .
    '<input type="hidden" id="toggle-status" name="ContractOfferSearch[deleted]" value="' . $activeDeletedEntities . '">';
?>

<div class="contract-offer-index">
    <h1>
        <?php echo Html::encode($this->title) ?>
        <?php if (Yii::$app->user->can('createContractOffer')) {
            echo Html::a(Yii::t('crm', 'Create'), ['create'], ['class' => 'btn btn-sm btn-success']);
        } ?>
    </h1>

    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summary' => AppHelper::setGridViewTableLayout($searchModel, $dataProvider->getTotalCount())['summary'],
        'layout' => AppHelper::setGridViewTableLayout($searchModel, $dataProvider->getTotalCount())['layout'],
        'tableOptions' => AppHelper::setGridViewTableLayout($searchModel, $dataProvider->getTotalCount())['tableOptions'],
        'headerRowOptions' => ['id' => 'w0-headers'],
        'columns' => [
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '100px'],
                'contentOptions' => ['class' => 'text-center', 'style' => ['min-width' => '100px']],
                'class' => 'yii\grid\ActionColumn',
                'template' => Helper::filterActionColumn('{view} {update}') . ' {delete-activate}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        $ac = new ActionColumn();
                        return Html::a($ac->icons['eye-open'], ['view', 'id' => $model->id], [
                            'class' => 'btn btn-xs btn-info',
                            'style' => 'width: 24px',
                            'data-toggle' => 'tooltip',
                            'title' => Yii::t('crm', 'View more details')
                        ]);
                    },
                    'update' => function ($url, $model, $key) use ($hasPermission) {
                        if (!$hasPermission) {
                            $ac = new ActionColumn();
                            return Html::a($ac->icons['pencil'], ['update', 'id' => $model->id], [
                                'class' => 'btn btn-xs btn-warning text-white',
                                'style' => 'width: 24px',
                                'data-toggle' => 'tooltip',
                                'title' => Yii::t('crm', 'Edit')
                            ]);
                        }
                        return false;
                    },
                    'delete-activate' => function ($url, $model, $key) use ($hasPermission) {
                        $action = 'delete';
                        if ($hasPermission) {
                            $action = 'activate';
                        }
                        $url = Url::to(["/crm/contract-offer/{$action}", 'id' => $model->id]);

                        $confirmDeleteActivate = Yii::t('crm', 'Are you sure you want to {action} this {item}?',
                            [
                                'action' => Yii::t('crm', $action),
                                'item' => Yii::t('crm', 'contract-offer')
                            ]);
                        $ac = new ActionColumn();
                        if (
                            ($action === 'activate' && Yii::$app->user->can('activateContractOffer')) ||
                            $action === 'delete' && Yii::$app->user->can('deleteContractOffer')) {
                            return Html::button($action === 'delete' ? $ac->icons['trash'] : '<i class="fab fa-rev"></i>',
                                [
                                    'class' => $action === 'delete' ? 'btn btn-xs btn-danger' : 'btn btn-xs btn-success',
                                    'style' => 'color:white; width:24px',
                                    'data-toggle' => 'tooltip',
                                    'title' => $action === 'delete' ? Yii::t('crm', 'Delete') : Yii::t('crm', 'Activate'),
                                    'onClick' => 'deleteActivateRecord("' . $confirmDeleteActivate . '", "' . $url . '");'
                                ]);
                        }
                        return false;
                    },
                ],
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '90px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('crm', 'Id'),
                'attribute' => 'id',
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => $dropDownsWidth],
                'label' => Yii::t('crm', 'Company'),
                'contentOptions' => ['class' => 'text-center'],
                'attribute' => 'company_id',
                'value' => function ($model) {
                    return !empty($model->company) ? $model->company->name : '-';
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => Company::$names,
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'prompt' => Yii::t('crm', 'All'),
                ],
                'filterWidgetOptions' => [
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ]
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
                'attribute' => 'added',
                'format' => 'html',
                'value' => function ($model) {
                    return $model->added . "<br>" . (!empty(User::$users[$model->added_by]) ? User::$users[$model->added_by] : '-');
                },
                'filter' => false,
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '150px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('crm', 'Updated'),
                'attribute' => 'updated',
                'format' => 'html',
                'value' => function ($model) {
                    return $model->updated . "<br>" . (!empty(User::$users[$model->updated_by]) ? User::$users[$model->updated_by] : '-');
                },
                'filter' => false,
            ]
        ],
        'pager' => [
            'options' => ['class' => 'pagination m-0 justify-content-end'],
            'linkContainerOptions' => ['class' => 'page-item'],
            'linkOptions' => ['class' => 'page-link'],
            'disableCurrentPageButton' => true,
            'disabledListItemSubTagOptions' => ['class' => 'page-link']
        ]
    ]); ?>
</div>

<?php
$this->registerJs(
    "activeDeletedLabel($activeDeletedEntities, '$viewEntitiesToggle');",
    View::POS_READY,
    'active-deleted-label-handler'
);

$this->registerJs(
    "viewToggleChange('ContractOfferSearch[deleted]');",
    View::POS_READY,
    'view-toggle-change-handler'
);

$this->registerJs(
    "setPageSize('ContractOfferSearch[pageSize]');",
    View::POS_READY,
    'grid-view-page-size'
);
?>
