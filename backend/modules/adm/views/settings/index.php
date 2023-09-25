<?php

use backend\modules\adm\models\User;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\adm\models\forms\SettingsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('adm', 'Settings');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="settings-index">

    <h1>
        <?php echo Html::encode($this->title); ?>
        <?php
        if (Yii::$app->user->can('SuperAdmin'))
            echo Html::a(Yii::t('adm', 'Create Settings'), ['create'], ['class' => 'btn btn-sm btn-success']);
        ?>
    </h1>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "<div class='row mb-3 align-items-center'><div class='col-sm-6'>{summary}</div><div class='col-sm-6'>{pager}</div></div>{items}<div class='row mb-3 align-items-center'><div class='col-sm-6'>{summary}</div><div class='col-sm-6'>{pager}</div></div>",
        'tableOptions' => ['class' => 'table table-sm table-bordered table-striped table-valign-middle'],
        'columns' => [
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '100px'],
                'contentOptions' => ['class' => 'text-center', 'style' => ['min-width' => '100px']],
                'header' => Yii::t('adm', 'Actions'),
                'class' => 'yii\grid\ActionColumn',
                // 'template' => Helper::filterActionColumn('{view} {update} {delete}'),
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        $ac = new ActionColumn();
                        return Html::a($ac->icons['eye-open'], ['view', 'id' => $model->id], [
                            'class' => 'btn btn-xs btn-info',
                            'style' => 'width: 24px',
                            'data-toggle' => 'tooltip',
                            'title' => Yii::t('adm', 'View more details')
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        $ac = new ActionColumn();
                        return Html::a($ac->icons['pencil'], ['update', 'id' => $model->id], [
                            'class' => 'btn btn-xs btn-warning text-white',
                            'style' => 'width: 24px',
                            'data-toggle' => 'tooltip',
                            'title' => Yii::t('adm', 'Edit')
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        Url::to(['delete', 'id' => $model->id]);
                        $confirmDeleteMultiple = Yii::t('adm', 'Please check if this setting is not used anywhere in application because after delete application crashes may occur. Are you sure you want to delete this {item}?',
                            ['item' => Yii::t('adm', 'setting')]);
                        $ac = new ActionColumn();
                        return Html::button($ac->icons['trash'],
                            [
                                'class' => 'btn btn-xs btn-danger',
                                'style' => 'color:white; width:24px',
                                'data-toggle' => 'tooltip',
                                'title' => Yii::t('adm', 'Delete'),
                                'onClick' => 'deleteActivateRecord("' . $confirmDeleteMultiple . '", "' . $url . '");'
                            ]);
                    },
                ]
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '90px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('adm', 'Id'),
                'attribute' => 'id',
            ],
            [
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'attribute' => 'name'
            ],
            [
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['style' => 'white-space: pre-wrap'],
                'label' => Yii::t('adm', 'Description'),
                'attribute' => 'description'
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '150px'],
                'contentOptions' => ['class' => 'text-left'],
                'attribute' => 'value',
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '150px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('adm', 'Added'),
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
                'label' => Yii::t('adm', 'Updated'),
                'attribute' => 'updated',
                'format' => 'html',
                'value' => function ($model) {
                    return $model->updated . "<br>" . (!empty(User::$users[$model->updated_by]) ? User::$users[$model->updated_by] : '-');
                },
                'filter' => false,
            ],
        ],
        'pager' => [
            'options' => ['class' => 'pagination m-0 justify-content-end'],
            'linkContainerOptions' => ['class' => 'page-item'],
            'linkOptions' => ['class' => 'page-link'],
            'disableCurrentPageButton' => true,
            'disabledListItemSubTagOptions' => ['class' => 'page-link']
        ]
    ]); ?>
    <?php Pjax::end(); ?>

</div>
