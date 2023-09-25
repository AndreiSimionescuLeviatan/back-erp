<?php

use backend\modules\adm\models\User;
use backend\modules\hr\models\Employee;
use common\components\AppHelper;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use mdm\admin\components\Helper;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\adm\models\search\ErpCompanySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('adm', 'Erp companies');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="erp-company-index">

    <h1>
        <?php echo Html::encode($this->title) ?>
        <?php echo Html::a(Yii::t('adm', 'Create Erp Company'), ['create'], ['class' => 'btn btn-success']) ?>
    </h1>

    <?php
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "<div class='row mb-3 align-items-center'><div class='col-sm-6'>{summary}</div><div class='col-sm-6'>{pager}</div></div>{items}<div class='row mb-3 align-items-center'><div class='col-sm-6'>{summary}</div><div class='col-sm-6'>{pager}</div></div>",
        'tableOptions' => AppHelper::setGridViewTableLayout($searchModel, $dataProvider->getTotalCount())['tableOptions'],
        'headerRowOptions' => ['id' => 'w0-headers'],
        'columns' => [
            [
                'headerOptions' => ['class' => 'text-center', 'width' => Yii::$app->params['columnWidthAction']],
                'contentOptions' => ['class' => 'text-center', 'width' => Yii::$app->params['columnWidthAction']],
                'class' => 'yii\grid\ActionColumn',
                'template' => Helper::filterActionColumn('{update} {view} {delete}'),
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        $ac = new ActionColumn();
                        return Html::a($ac->icons['pencil'], ['update', 'id' => $model->id], [
                            'class' => 'btn btn-xs btn-warning',
                            'style' => 'color:white; width:24px',
                            'data-toggle' => 'tooltip',
                            'title' => Yii::t('adm', 'Edit')
                        ]);
                    },
                    'view' => function ($url, $model, $key) {
                        $ac = new ActionColumn();
                        return Html::a($ac->icons['eye-open'], ['view', 'id' => $model->id], [
                            'class' => 'btn btn-xs btn-info',
                            'style' => 'color:white; width:24px',
                            'data-toggle' => 'tooltip',
                            'title' => Yii::t('adm', 'View more details')
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        $url = Url::to(["/adm/erp-company/delete", 'id' => $model->id]);
                        $confirmDelete = Yii::t('adm', 'Are you sure you want to delete this erp company?');
                        $ac = new ActionColumn();
                        return Html::button($ac->icons['trash'],
                            [
                                'class' => 'btn btn-xs btn-danger',
                                'style' => 'color:white; width:24px',
                                'data-toggle' => 'tooltip',
                                'title' => Yii::t('adm', 'Delete'),
                                'onClick' => 'deleteActivateRecord("' . $confirmDelete . '", "' . $url . '");'
                            ]);
                    },
                ],
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => Yii::$app->params['columnWidthId']],
                'contentOptions' => ['class' => 'text-center', 'width' => Yii::$app->params['columnWidthId']],
                'label' => Yii::t('adm', 'Id'),
                'attribute' => 'id',
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                ],
            ],
            [
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('adm', 'Company'),
                'attribute' => 'company_id',
                'value' => 'company.name'
            ],
            [
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('adm', 'General manager'),
                'attribute' => 'general_manager_id',
                'value' => function ($model) {
                    return !empty(Employee::$employees[$model->general_manager_id]) ? Employee::$employees[$model->general_manager_id] : '-';
                }
            ],
            [
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('adm', 'Deputy general manager'),
                'attribute' => 'deputy_general_manager_id',
                'value' => function ($model) {
                    return !empty(Employee::$employees[$model->deputy_general_manager_id]) ? Employee::$employees[$model->deputy_general_manager_id] : '-';
                }
            ],
            [
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('adm', 'Technical manager'),
                'attribute' => 'technical_manager_id',
                'value' => function ($model) {
                    return !empty(Employee::$employees[$model->technical_manager_id]) ? Employee::$employees[$model->technical_manager_id] : '-';
                }
            ],
            [
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('adm', 'Executive manager'),
                'attribute' => 'executive_manager_id',
                'value' => function ($model) {
                    return !empty(Employee::$employees[$model->executive_manager_id]) ? Employee::$employees[$model->executive_manager_id] : '-';
                }
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => Yii::$app->params['columnWidthAddedUpdated']],
                'contentOptions' => ['class' => 'text-center', 'width' => Yii::$app->params['columnWidthAddedUpdated']],
                'label' => Yii::t('adm', 'Added'),
                'attribute' => 'added_by',
                'format' => 'html',
                'value' => function ($model) {
                    return $model->added . "<br>" . (!empty(User::$users[$model->added_by]) ? User::$users[$model->added_by] : '-');
                }
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => Yii::$app->params['columnWidthAddedUpdated']],
                'contentOptions' => ['class' => 'text-center', 'width' => Yii::$app->params['columnWidthAddedUpdated']],
                'label' => Yii::t('adm', 'Updated'),
                'attribute' => 'updated_by',
                'format' => 'html',
                'value' => function ($model) {
                    return $model->updated . "<br>" . (!empty(User::$users[$model->updated_by]) ? User::$users[$model->updated_by] : '-');
                }
            ]
        ]
    ]); ?>

</div>
