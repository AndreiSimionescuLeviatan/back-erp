<?php

use backend\modules\adm\models\User;
use backend\modules\crm\models\Brand;
use backend\modules\crm\models\BrandModel;
use common\components\AppHelper;
use kartik\grid\GridViewInterface;
use mdm\admin\components\Helper;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\crm\models\search\BrandModelSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('crm', 'Models');
$this->params['breadcrumbs'][] = $this->title;
$hasPermission = AppHelper::checkPermissionViewDeletedEntities($_GET['BrandModelSearch']['deleted'] ?? '', 'activateModel');
$toggleStatus = !$hasPermission ? 'checked' : '';
$activeDeletedEntities = isset($_GET['BrandModelSearch']['deleted']) && $_GET['BrandModelSearch']['deleted'] == 1 ? 1 : 0;
$viewEntitiesToggle = '<label class="personalized-toggle mb-0 mt-1">' .
    '<input id="switch_view_toggle_id" onchange="viewToggleChange()" type="checkbox" ' . $toggleStatus . '>' .
    '<span class="personalized-toggle-slider"></span>' .
    '</label>' .
    '<input type="hidden" id="toggle-status" name="BrandModelSearch[deleted]" value="' . $activeDeletedEntities . '">';
?>
<div class="brand-index">
    <h1>
        <?php echo Html::encode($this->title) ?>
        <?php
        //        if (Yii::$app->user->can('createModel'))
        echo Html::a(Yii::t('crm', 'Create Model'), ['create'], ['class' => 'btn btn-sm btn-success']);
        //        ?>
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
                'headerOptions' => ['class' => 'text-center', 'width' => '110px'],
                'contentOptions' => ['class' => 'text-center', 'style' => ['min-width' => '110px']],
                'header' => Yii::$app->user->can('activateModel') ? $viewEntitiesToggle : '',
                'class' => 'yii\grid\ActionColumn',
                'template' => Helper::filterActionColumn('{view} {update} {delete-activate}'),
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
                    'update' => function ($url, $model, $key) {
                        $ac = new ActionColumn();
                        return Html::a($ac->icons['pencil'], ['update', 'id' => $model->id], [
                            'class' => 'btn btn-xs btn-warning text-white',
                            'style' => 'width: 24px',
                            'data-toggle' => 'tooltip',
                            'title' => Yii::t('crm', 'Edit')
                        ]);
                    },
                    'delete-activate' => function ($url, $model, $key) use ($hasPermission) {
                        $action = 'delete';
                        if ($hasPermission) {
                            $action = 'activate';
                        }
                        $url = Url::to([$action, 'id' => $model->id]);

                        $confirmDeleteActivate = Yii::t('crm', 'Are you sure you want to {action} this {item}?',
                            ['item' => Yii::t('crm', 'model'), 'action' => Yii::t('crm', $action)]);
                        $ac = new ActionColumn();
                        return Html::button($action === 'delete' ? $ac->icons['trash'] : '<i class="fab fa-rev"></i>',
                            [
                                'class' => $action === 'delete' ? 'btn btn-xs btn-danger' : 'btn btn-xs btn-success',
                                'style' => 'color:white; width:24px',
                                'data-toggle' => 'tooltip',
                                'title' => $action === 'delete' ? Yii::t('crm', 'Delete') : Yii::t('crm', 'Activate'),
                                'onClick' => 'deleteActivateRecord("' . $confirmDeleteActivate . '", "' . $url . '");'
                            ]);

                    },
                ]
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '75px'],
                'contentOptions' => ['class' => 'text-center'],
                'filterInputOptions' => [
                    'class' => 'form-control id_filter',
                    'type' => 'number',
                ],
                'label' => Yii::t('crm', 'Id'),
                'attribute' => 'id',
            ],
            [
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('crm', 'Brand'),
                'attribute' => 'brand_id',
                'value' => function ($model) {
                    return !empty(Brand::$brand[$model->brand_id]) ? Brand::$brand[$model->brand_id] : '-';
                },
                'filter' => Brand::$brand,
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'prompt' => Yii::t('crm', 'All'),
                ]
            ],
            [
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'oninput' => "this.value = this.value.replace(//\d+|^\s+$[^a-zA-Z0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                ],
                'label' => Yii::t('crm', 'Name'),
                'attribute' => 'name',
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
                'filter' => BrandModel::$addedBy,
                'filterType' => GridViewInterface::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'prompt' => Yii::t('crm', 'Select employee'),
                ],
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
                'filter' => BrandModel::$updatedBy,
                'filterType' => GridViewInterface::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'prompt' => Yii::t('crm', 'Select employee'),
                ],
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

<?php
$this->registerJs(
    "activeDeletedLabel($activeDeletedEntities, '$viewEntitiesToggle');",
    View::POS_READY,
    'active-deleted-label-custom-handler'
);

$this->registerJs(
    "viewToggleChange('BrandModelSearch[deleted]');",
    View::POS_READY,
    'view-toggle-change-handler'
);

$this->registerJs(
    "preventInvalidInputsInsert('.id_filter', 2147483647);",
    View::POS_READY,
    'prevent-invalid-inputs-insert'
);

$this->registerJs(
    "setPageSize('BrandModelSearch[pageSize]');",
    View::POS_READY,
    'grid-view-page-size'
);

?>
