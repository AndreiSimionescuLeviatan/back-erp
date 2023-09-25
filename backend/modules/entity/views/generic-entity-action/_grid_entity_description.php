<?php

use kartik\grid\GridView;

/* @var $dataProvider */
?>

<?php $gridColumns = [
    [
        'class' => 'kartik\grid\CheckboxColumn',
        'headerOptions' => [
            'class' => 'kartik-sheet-style',
            'id' => 'th-check'
        ],
        'checkboxOptions' => function ($model) {
            return [
                'id' => 'td-checkbox-' . $model['id'],
                'value' => $model['id'],
            ];
        },
        'contentOptions' => function ($model) {
            return [
                'class' => 'kv-grid-demo text-center',
                'id' => 'td-label-' . $model['id'],
                'data-key' => $model['id'],
            ];
        },
    ],
    [
        'headerOptions' => [
            'class' => 'text-center',
            'id' => 'th-name'
        ],
        'contentOptions' => [
            'class' => 'text-left',
        ],
        'label' => Yii::t('entity', 'Name entity'),
        'attribute' => 'name'
    ]
]; ?>

<?php echo GridView::widget([
    'id' => 'kv-grid-demo',
    'dataProvider' => $dataProvider,
    'columns' => $gridColumns,
    'tableOptions' => ['class' => 'table table-sm table-bordered table-striped table-valign-middle'],
    'rowOptions' => function ($model, $key, $index, $grid) {
        return [
            'data' => ['key' => $model['id']],
        ];
    },
    'summary' => '',
    'headerContainer' => ['style' => 'top:50px', 'class' => 'kv-table-header'],
    'floatHeader' => false,
    'floatPageSummary' => false,
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

