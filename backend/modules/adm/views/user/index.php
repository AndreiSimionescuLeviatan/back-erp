<?php

use backend\modules\adm\models\ErpCompany;
use backend\modules\adm\models\User;
use yii\grid\ActionColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\adm\models\search\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('adm', 'Users');
$this->params['breadcrumbs'][] = $this->title;

$userStatus = [
    0 => Yii::t('adm', 'Deleted'),
    9 => Yii::t('adm', 'Inactive'),
    10 => Yii::t('adm', 'Active')
];

?>
<div class="user-index">

    <h1>
        <?php echo Html::encode($this->title) ?>
        <?php echo Html::a(Yii::t('adm', 'Create User'), ['create'], ['class' => 'btn btn-sm btn-success']) ?>

    </h1>
    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '90px'],
                'contentOptions' => ['class' => 'text-center', 'style' => ['min-width' => '30px']],
                'header' => Yii::t('adm', 'Actions'),
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete} <br>{change-user-psw}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        $ac = new ActionColumn();
                        return Html::a($ac->icons['eye-open'], ['view', 'id' => $model->id], [
                            'class' => 'btn btn-xs btn-info',
                            'style' => 'color:white; width:24px',
                            'data-toggle' => 'tooltip',
                            'title' => Yii::t('adm', 'View more details')
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        $ac = new ActionColumn();
                        return Html::a($ac->icons['pencil'], ['update', 'id' => $model->id], [
                            'class' => 'btn btn-xs btn-warning',
                            'style' => 'color:white; width:24px',
                            'data-toggle' => 'tooltip',
                            'title' => Yii::t('adm', 'Edit')
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        Url::to(['delete', 'id' => $model->id]);
                        $confirmDeleteMultiple = Yii::t('adm', 'Are you sure you want to delete this {item}?',
                            ['item' => Yii::t('adm', 'user')]);
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
                    'change-user-psw' => function ($url, $model, $key) {
                        if (Yii::$app->user->can('SuperAdmin')) {
                            $promptTitle = Yii::t('adm', 'Change user password');
                            $promptMessage = Yii::t('adm', 'Change the password for user <b>{userFullName}</b> with email address <b>{email}</b>?',
                                [
                                    'userFullName' => $model->fullName(),
                                    'email' => $model->email
                                ]);
                            return Html::button('<i class="fas fa-lock"></i>',
                                [
                                    'class' => 'btn btn-xs btn-danger',
                                    'style' => 'color:white; width:24px',
                                    'data-toggle' => 'tooltip',
                                    'title' => Yii::t('adm', 'Change user password'),
                                    'onClick' => 'changeUserPsw("' . $promptTitle . '", "' . $promptMessage . '", "' . $url . '");'
                                ]);
                        } else {
                            return '';
                        }
                    },
                ],
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '50px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('adm', 'Id'),
                'attribute' => 'id'
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '90px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('adm', 'Username'),
                'attribute' => 'username'
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '100px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('adm', 'First name'),
                'attribute' => 'first_name'
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '100px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('adm', 'Last name'),
                'attribute' => 'last_name'
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '70px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('adm', 'E-mail'),
                'attribute' => 'email'
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '70px'],
                'contentOptions' => ['class' => 'text-center align-middle'],
                'label' => Yii::t('adm', 'Status'),
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->status == 0) {
                        return Html::button(User::getStatus($model->status), ['class' => 'btn badge badge-danger']);
                    }
                    if ($model->status == 9) {
                        return Html::button(User::getStatus($model->status), ['class' => 'btn badge badge-secondary']);
                    }
                    if ($model->status == 10) {
                        return Html::button(User::getStatus($model->status), ['class' => 'btn badge badge-success']);
                    }
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $userStatus,
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'prompt' => Yii::t('adm', 'All'),
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
                'label' => Yii::t('adm', 'Company'),
                'attribute' => 'userErpCompaniesNames',
                'format' => 'raw',
                'value' => function ($model) {
                    if (!empty($model->companies)) {
                        $companies = ArrayHelper::getColumn($model->companies, 'name');
                        if (!empty($companies)) {
                            $buttons = [];
                            foreach ($companies as $key => $company) {
                                $buttons[] = Html::button($company, ['id' => $model->companies[$key]->id, 'class' => 'btn btn-xs btn-info', 'onclick' => "filterBySelectedCompany({$model->companies[$key]->id})"]);
                            }
                            return implode('<br>', $buttons);
                        }
                    }
                    return '-';
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => ErpCompany::$names,
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'prompt' => Yii::t('adm', 'All'),
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
                'label' => Yii::t('adm', 'Created'),
                'attribute' => 'created_at',
                'filter' => false,
                'format' => ['date', 'php:d-m-Y']
            ],
            [
                'headerOptions' => ['class' => 'text-center', 'width' => '90px'],
                'contentOptions' => ['class' => 'text-center'],
                'label' => Yii::t('adm', 'Updated'),
                'attribute' => 'updated_at',
                'filter' => false,
                'format' => ['date', 'php:d-m-Y']
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
</div>

<script>
    function filterBySelectedCompany(companyId) {
        let newUrl = new URL(window.location.href);
        let selectedValue = companyId;
        if (selectedValue !== '') {
            newUrl.searchParams.set('UserSearch[userErpCompaniesNames]', selectedValue);
        } else {
            newUrl.searchParams.delete('UserSearch[userErpCompaniesNames]');
        }
        window.location.href = newUrl.href;
    }

    <?php if (Yii::$app->user->can('SuperAdmin')) {?>
    function changeUserPsw(promptTitle, promptMsg, url) {
        $('.item_action_btns_container a, .item_action_btns_container button').addClass('disabled');
        bootbox.prompt({
            title: promptTitle,
            message: promptMsg,
            buttons: {
                confirm: {
                    label: <?php echo Yii::t('adm', '\'Change\'');?>,
                    className: 'btn-danger'
                },
                cancel: {
                    label: <?php echo Yii::t('adm', '\'Quit\'');?>,
                }
            },
            inputType: 'password',
            callback: function (result) {
                if (result) {
                    $.post(url, {
                        newPassword: result,
                        newPasswordConfirm: result,
                    });
                } else {
                    $('.item_action_btns_container a, .item_action_btns_container button').removeClass('disabled');
                }
            }
        });
    }
    <?php } ?>
</script>
