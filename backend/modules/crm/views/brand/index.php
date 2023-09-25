<?php

use backend\modules\adm\models\User;
use backend\modules\crm\models\Brand;
use common\components\AppHelper;
use mdm\admin\components\Helper;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\crm\models\search\BrandSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
User::setUsers();
$this->title = Yii::t('crm', 'Brands');
$this->params['breadcrumbs'][] = $this->title;
User::setUsers(true);
$hasPermission = AppHelper::checkPermissionViewDeletedEntities($_GET['BrandSearch']['deleted'] ?? '', 'activateBrand');
$toggleStatus = !$hasPermission ? 'checked' : '';
$activeDeletedEntities = isset($_GET['BrandSearch']['deleted']) && $_GET['BrandSearch']['deleted'] == 1 ? 1 : 0;
$viewEntitiesToggle = '<label class="personalized-toggle mb-0 mt-1">' .
    '<input id="switch_view_toggle_id" onchange="viewToggleChange()" type="checkbox" ' . $toggleStatus . '>' .
    '<span class="personalized-toggle-slider"></span>' .
    '</label>' .
    '<input type="hidden" id="toggle-status" name="BrandSearch[deleted]" value="' . $activeDeletedEntities . '">';
?>
<div class="brand-index">
    <h1>
        <?php echo Html::encode($this->title) ?>
        <?php
        if (Yii::$app->user->can('createBrand'))
            echo Html::a(Yii::t('crm', 'Create Brand'), ['create'], ['class' => 'btn btn-sm btn-success']);
        ?>
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
                'header' => Yii::t('crm', 'Actions'),
                'class' => 'yii\grid\ActionColumn',
                'template' => Helper::filterActionColumn('{view} {update} {delete}'),
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
                    'delete' => function ($url, $model, $key) use ($hasPermission) {
                        $action = 'delete';
                        if ($hasPermission) {
                            $action = 'activate';
                        }
                        $url = Url::to(["/crm/brand/{$action}", 'id' => $model->id]);

                        $confirmDeleteActivate = Yii::t('crm', 'Are you sure you want to {action} this {item}?',
                            [
                                'action' => Yii::t('crm', $action),
                                'item' => Yii::t('crm', 'brand')
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
                ]
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '75px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('crm', 'Id'),
                'attribute' => 'id',
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'oninput' => "this.value = this.value.replace(/[^0-9\.]+/g, '').replace(/(\..*?)\..*/g, '$1');",
                ]
            ],
            [
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('crm', 'Name'),
                'attribute' => 'name',
                'filterInputOptions' => [
                    'class' => 'form-control replace-apostrophe',
                    'oninput' => "this.value = this.value.replace(//\d+|^\s+$[^a-zA-Z0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');",
                ]
            ], [
                'headerOptions' => ['class' => 'text-center', 'width' => '150px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('crm', 'Added'),
                'attribute' => 'added_by',
                'format' => 'html',
                'value' => function ($model) {
                    return $model->added . "<br>" . (!empty(User::$users[$model->added_by]) ? User::$users[$model->added_by] : '-');
                },
                'filter' => Brand::$addedBy,
                'filterType' => GridView::FILTER_SELECT2,
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
                'filter' => Brand::$updatedBy,
                'filterType' => GridView::FILTER_SELECT2,
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
    "viewToggleChange('BrandSearch[deleted]');",
    View::POS_READY,
    'view-toggle-change-handler'
);

$this->registerJs(
    "setPageSize('BrandSearch[pageSize]');",
    View::POS_READY,
    'grid-view-page-size'
);

?>
