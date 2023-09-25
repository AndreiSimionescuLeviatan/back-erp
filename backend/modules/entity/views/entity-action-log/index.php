<?php

use backend\modules\entity\models\Domain;
use backend\modules\entity\models\Entity;
use backend\modules\entity\models\EntityAction;
use backend\modules\entity\models\EntityActionLog;
use kartik\grid\GridView;
use yii\helpers\Html;
use common\components\AppHelper;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\entity\models\search\EntityActionLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('entity', 'History find&replace');
$this->params['breadcrumbs'][] = $this->title;
?>

<style>

    .kv-table-header {
        top: 0 !important;
        z-index: 1;
    }

    .entity-operation {
        float: left;
        padding: 5px;
        border-top: 1px solid #0003;
    }

</style>


<div class="history-entity-action">
    <h1>
        <?php echo Html::encode($this->title) ?>
        <?php echo Html::a(Yii::t('app', 'Entities replace'), ['generic-entity-action/index'], ['class' => 'btn btn-sm btn-success']); ?>
    </h1>
    <?php
    $gridColumns = [
        [
            'class' => 'kartik\grid\ExpandRowColumn',
            'width' => '50px',
            'value' => function ($model, $key, $index, $column) {
                return GridView::ROW_COLLAPSED;
            },
            'detail' => function ($model, $key, $index, $column) {
                return Yii::$app->controller->renderPartial('_view_action_operation', [
                    'operations' => EntityActionLog::findByActionId($model['id'])
                ]);
            },
            'headerOptions' => [
                'class' => 'kartik-sheet-style',
                'id' => 'th-expand'
            ],
            'expandOneOnly' => false,
            'enableRowClick' => true,
            'detailAnimationDuration' => 100,
            'expandIcon' => '<span class="far fa-caret-square-right"></span>',
            'collapseIcon' => '<span class="far fa-caret-square-down"></span>'
        ],
        [
            'headerOptions' => [
                'class' => 'text-center domain-name-section',
                'width' => '100px',
                'id' => 'th-domain-name'
            ],
            'contentOptions' => ['class' => 'text-left'],
            'label' => Yii::t('entity', 'Domain'),
            'attribute' => 'domain_id',
            'value' => function ($model) {
                return $model['domain_description'];
            },
            'filter' => Domain::$names,
            'filterType' => GridView::FILTER_SELECT2,
            'filterInputOptions' => [
                'class' => 'form-control',
                'prompt' => Yii::t('entity', 'Select domain')
            ],
            'filterWidgetOptions' => [
                'pluginOptions' => [
                    'allowClear' => true
                ]
            ]
        ],
        [
            'headerOptions' => [
                'class' => 'text-center entity-name-section',
                'id' => 'th-entity-name',
                'max-width' => '120px'
            ],
            'contentOptions' => ['class' => 'text-left', 'style' => ['max-width' => '120px']],
            'label' => Yii::t('entity', 'Entity'),
            'attribute' => 'entity_id',
            'value' => function ($model) {
                return $model['entity_description'];
            },
            'filter' => Entity::$names,
            'filterType' => GridView::FILTER_SELECT2,
            'filterInputOptions' => [
                'class' => 'form-control',
                'prompt' => Yii::t('entity', 'Select entity')
            ],
            'filterWidgetOptions' => [
                'pluginOptions' => [
                    'allowClear' => true
                ]
            ]
        ],
        [
            'headerOptions' => [
                'class' => 'text-center entity_new_id',
                'id' => 'th-entity-new-id',
                'max-width' => '70px'
            ],
            'contentOptions' => ['class' => 'text-left', 'style' => ['max-width' => '70px']],
            'label' => Yii::t('app', 'ID'),
            'attribute' => 'entity_new_id',
        ],
        [
            'headerOptions' => [
                'class' => 'text-center description-section',
                'id' => 'th-description'
            ],
            'contentOptions' => ['class' => 'text-left', 'style' => ['max-width' => '150px']],
            'label' => Yii::t('app', 'Description'),
            'attribute' => 'description',
            'value' => function () {
                return '';
            },
        ],
        [
            'headerOptions' => [
                'class' => 'text-center old-value-section',
                'id' => 'th-old-value',
                'style' => ['min-width' => '50px']
            ],
            'contentOptions' => ['class' => 'text-left', 'style' => ['min-width' => '50px']],
            'label' => Yii::t('entity', 'Old value'),
            'attribute' => 'old_value',
            'value' => function () {
                return '';
            },
        ],
        [
            'headerOptions' => [
                'class' => 'text-center new-value-section',
                'id' => 'th-new-value',
                'style' => ['min-width' => '50px']
            ],
            'contentOptions' => ['class' => 'text-left', 'style' => ['min-width' => '50px']],
            'label' => Yii::t('entity', 'New value'),
            'attribute' => 'new_value',
            'value' => function () {
                return '';
            },
        ],
        [
            'headerOptions' => [
                'class' => 'text-center old-value-section',
                'id' => 'th-old-code'
            ],
            'contentOptions' => ['class' => 'text-left'],
            'label' => Yii::t('entity', 'Old code'),
            'attribute' => 'old_code',
            'value' => function () {
                return '';
            },
        ],
        [
            'headerOptions' => [
                'class' => 'text-center new-value-section',
                'id' => 'th-new-code'
            ],
            'contentOptions' => ['class' => 'text-left'],
            'label' => Yii::t('entity', 'New code'),
            'attribute' => 'new_code',
            'value' => function () {
                return '';
            },
        ],
        [
            'headerOptions' => [
                'class' => 'text-center entity-added-section',
                'width' => '150px',
                'id' => 'th-added'
            ],
            'contentOptions' => ['class' => 'text-left'],
            'label' => Yii::t('app', 'Added'),
            'attribute' => 'added',
        ],
        [
            'headerOptions' => [
                'class' => 'text-center',
                'width' => '150px',
                'id' => 'th-added_by'
            ],
            'contentOptions' => ['class' => 'text-center', 'style' => ['width' => '150px']],
            'label' => Yii::t('app', 'Added By'),
            'attribute' => 'added_by',
            'format' => 'html',
            'filter' => EntityAction::$filtersOptions['added_by'],
            'filterType' => GridView::FILTER_SELECT2,
            'filterInputOptions' => [
                'class' => 'form-control',
                'prompt' => ucfirst(Yii::t('entity', 'filter'))
            ],
            'filterWidgetOptions' => [
                'pluginOptions' => [
                    'allowClear' => true
                ]
            ]
        ],
    ];
    echo GridView::widget([
        'id' => 'kv-grid-demo',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summary' => AppHelper::setGridViewTableLayout($searchModel, $dataProvider->getTotalCount())['summary'],
        'layout' => AppHelper::setGridViewTableLayout($searchModel, $dataProvider->getTotalCount())['layout'],
        'tableOptions' => AppHelper::setGridViewTableLayout($searchModel, $dataProvider->getTotalCount())['tableOptions'],
        'columns' => $gridColumns,
        'pager' => [
            'options' => ['class' => 'pagination m-0 justify-content-end'],
            'linkContainerOptions' => ['class' => 'page-item'],
            'linkOptions' => ['class' => 'page-link'],
            'disableCurrentPageButton' => true,
            'disabledListItemSubTagOptions' => ['class' => 'page-link'],
            'firstPageLabel' => Yii::t('app', 'First page'),
            'lastPageLabel' => Yii::t('app', 'Last page')
        ],
        'headerContainer' => ['style' => 'top:50px', 'class' => 'kv-table-header'],
        'floatHeader' => true,
        'floatPageSummary' => true,
        'floatFooter' => false,
        'pjax' => false,
        'responsive' => false,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'hover' => true,
        'showPageSummary' => false,
        'persistResize' => false
    ]); ?>

</div>

<?php

$this->registerJs(
    "setPageSize('EntityActionLogSearch[pageSize]');",
    View::POS_READY,
    'grid-view-page-size'
);

?>

<script>

    let columnsIDs = {
        'th-expand': 'left-offset',
        'th-domain-name': 'entity-operation-domain',
        'th-entity-name': 'entity-operation-entity',
        'th-entity-new-id': 'entity-operation-entity-old-id',
        'th-description': 'entity-operation-description',
        'th-old-value': 'entity-operation-old-value',
        'th-new-value': 'entity-operation-new-value',
        'th-old-code': 'entity-operation-old-code',
        'th-new-code': 'entity-operation-new-code',
        'th-added': 'entity-operation-added',
        'th-added_by': '',
    };

    window.addEventListener('load', (event) => {
        setTimeout(() => {
            setEntitiesPositionAndWidth();
        }, 1000);

        $('#kv-grid-demo').on('kvexprow:toggle', function (event, ind, key, extra, state) {
            setTimeout(() => {
                setEntitiesPositionAndWidth();
            }, 500);
        });

        $('#kv-grid-demo').on('kvexprow:toggleAll', function (event, extra, state) {
            setTimeout(() => {
                setEntitiesPositionAndWidth();
            }, 500);
        });

        for (let x in columnsIDs) {
            new ResizeObserver(() => {
                setEntitiesPositionAndWidth();
            }).observe(document.getElementById(x));
        }
    });

    window.addEventListener('resize', function (event) {
        setEntitiesPositionAndWidth();
    }, true);

    function setEntitiesPositionAndWidth()
    {
        let resourceLeftMargin = 2;
        for (let x in columnsIDs) {
            if (columnsIDs[x] == 'left-offset') {
                resourceLeftMargin += parseFloat($('#' + x).outerWidth());
                continue;
            }
            if (columnsIDs[x] == '') {
                continue;
            }
            $('.' + columnsIDs[x]).outerWidth(parseFloat($('#' + x).outerWidth()));
        }
        $('.entity-operation-col').css({'padding-left': resourceLeftMargin + 'px'});
    }

</script>
