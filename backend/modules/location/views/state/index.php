<?php

use backend\modules\location\models\State;
use common\components\AppHelper;
use backend\modules\adm\models\User;
use mdm\admin\components\Helper;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\location\models\search\StateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('location', 'Counties');
$this->params['breadcrumbs'][] = $this->title;

$hasPermission = AppHelper::checkPermissionViewDeletedEntities($_GET['StateSearch']['deleted'] ?? '', 'activateCounty');
$toggleStatus = !$hasPermission ? 'checked' : '';
$activeDeletedEntities = isset($_GET['StateSearch']['deleted']) && $_GET['StateSearch']['deleted'] == 1 ? 1 : 0;
$viewEntitiesToggle = '<label class="personalized-toggle mb-0 mt-1">' .
    '<input id="switch_view_toggle_id" onchange="viewToggleChange()" type="checkbox" ' . $toggleStatus . '>' .
    '<span class="personalized-toggle-slider"></span>' .
    '</label>' .
    '<input type="hidden" id="toggle-status" name="StateSearch[deleted]" value="' . $activeDeletedEntities . '">';
?>

<div class="county-index">
    <h1>
        <?php echo Html::encode($this->title) ?>
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
                'contentOptions' => ['class' => 'text-center', 'style' => ['width' => '100px']],
                'class' => 'yii\grid\ActionColumn',
                'template' => Helper::filterActionColumn('{view} {activate}'),
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        $ac = new ActionColumn();
                        return Html::a($ac->icons['eye-open'], ['view', 'id' => $model->id], [
                            'class' => 'btn btn-xs btn-info',
                            'style' => 'width: 24px',
                            'data-toggle' => 'tooltip',
                            'title' => Yii::t('location', 'View more details')
                        ]);
                    },
                    'activate' => function ($url, $model, $key) use ($hasPermission) {
                        $action = '';
                        if ($hasPermission) {
                            $action = 'activate';
                        }
                        $url = Url::to(["/location/state/{$action}", 'id' => $model->id]);

                        $confirmDeleteActivate = Yii::t('location', 'Are you sure you want to {action} this {item}?',
                            [
                                'action' => Yii::t('location', $action),
                                'item' => Yii::t('location', 'county')
                            ]);
                        $ac = new ActionColumn();
                        if ($hasPermission) {
                            return Html::button('<i class="fab fa-rev"></i>',
                                [
                                    'class' => 'btn btn-xs btn-success',
                                    'style' => 'color:white; width:24px',
                                    'data-toggle' => 'tooltip',
                                    'title' => Yii::t('location', 'Activate'),
                                    'onClick' => 'deleteActivateRecord("' . $confirmDeleteActivate . '", "' . $url . '");'
                                ]);
                        }
                    },
                ],
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '90px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('location', 'Id'),
                'attribute' => 'id',
                'filterInputOptions' => [
                    'class' => 'form-control',
                    "oninput" => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');",
                ]
            ],
            [
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('location', 'Country'),
                'attribute' => 'country_id',
                'value' => function ($model) {
                    return !empty($model->country) ? $model->country->name : '-';
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => AppHelper::$names['country'],
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'prompt' => Yii::t('location', 'All'),
                ],
                'filterWidgetOptions' => [
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ]
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '90px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('location', 'Code'),
                'attribute' => 'code',
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'oninput' => "this.value = this.value.replace(//\d+|^\s+$[^a-zA-Z0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');",
                ]
            ],
            [
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('location', 'Name'),
                'attribute' => 'name',
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'oninput' => "this.value = this.value.replace(//\d+|^\s+$[^a-zA-Z0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');",
                ]
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '200px'],
                'contentOptions' => ['class' => 'text-center', 'style' => ['width' => '200px']],
                'label' => Yii::t('location', 'Added'),
                'attribute' => 'added_by',
                'format' => 'html',
                'value' => function ($model) {
                    return $model->added . "<br>" . (!empty(User::$users[$model->added_by]) ? User::$users[$model->added_by] : '-');
                },
                'filter' => State::$userAdded,
                'filterType' => GridView::FILTER_SELECT2,
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'prompt' => Yii::t('location', 'Select employee'),
                ],
            ],
        ],
        'pager' => [
            'options' => ['class' => 'pagination m-0 justify-content-end'],
            'linkContainerOptions' => ['class' => 'page-item'],
            'linkOptions' => ['class' => 'page-link'],
            'disableCurrentPageButton' => true,
            'disabledListItemSubTagOptions' => ['class' => 'page-link'],
            'firstPageLabel' => Yii::t('location', 'First page'),
            'lastPageLabel' => Yii::t('location', 'Last page')
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
    "viewToggleChange('StateSearch[deleted]');",
    View::POS_READY,
    'view-toggle-change-handler'
);

$this->registerJs(
    "setPageSize('StateSearch[pageSize]');",
    View::POS_READY,
    'grid-view-page-size'
);
?>
