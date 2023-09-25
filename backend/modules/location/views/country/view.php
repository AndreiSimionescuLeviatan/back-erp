<?php

use backend\modules\adm\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\modules\location\models\Country */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('location', 'Countries'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="country-view">
    <div class="system-view">
        <div class="row">
            <div class="col-6">
                <h1><?php echo Html::encode($this->title) ?></h1>
                <?php $thW = '200px'; ?>
                <?php echo DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'label' => Yii::t('location', 'Id'),
                            'attribute' => 'id',
                            'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                        ],
                        [
                            'label' => Yii::t('location', 'Code'),
                            'attribute' => 'code',
                            'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                        ],
                        [
                            'label' => Yii::t('location', 'Name'),
                            'attribute' => 'name',
                            'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                        ],
                        [
                            'label' => Yii::t('location', 'Deleted'),
                            'attribute' => 'deleted',
                            'value' => $model->deleted == 0 ? Yii::t('location', '<span class="badge badge-success">NO</span>') : Yii::t('location', '<span class="badge badge-danger">YES</span>'),
                            'captionOptions' => ['class' => 'text-right', 'width' => $thW],
                            'contentOptions' => ['class' => 'align-middle'],
                            'format' => 'html'
                        ],
                        [
                            'label' => Yii::t('location', 'Added'),
                            'attribute' => 'added',
                            'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                        ],
                        [
                            'label' => Yii::t('location', 'Added By'),
                            'attribute' => 'added_by',
                            'captionOptions' => ['class' => 'text-right', 'width' => $thW],
                            'value' => function ($model) {
                                $addedBy = User::findOne($model->added_by);
                                if (!empty($model->added_by) && !empty($addedBy)) {
                                    return $addedBy->fullName();
                                } else {
                                    return '-';
                                }
                            }
                        ],
                        [
                            'label' => Yii::t('location', 'Updated'),
                            'attribute' => 'updated',
                            'captionOptions' => ['class' => 'text-right', 'width' => $thW],
                            'value' => empty($model->updated) ? '-' : $model->updated
                        ],
                        [
                            'label' => Yii::t('location', 'Updated By'),
                            'attribute' => 'updated_by',
                            'captionOptions' => ['class' => 'text-right', 'width' => $thW],
                            'value' => function ($model) {
                                $updatedBy = User::findOne($model->updated_by);
                                if (!empty($model->updated_by) && !empty($updatedBy)) {
                                    return $updatedBy->fullName();
                                } else {
                                    return '-';
                                }
                            }
                        ],
                    ],
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-6">
                <div class="row">
                    <div class="col-6">
                        <?php echo Html::a(
                            Yii::t('location', '{icon} Back',
                                [
                                    'icon' => '<i class="fas fa-chevron-circle-left"></i>'
                                ]
                            ), Url::previous('country'), ['class' => 'btn btn-info']);
                        ?>
                    </div>
                    <div class="col-6 text-right item_action_btns_container">
                        <?php
                        if (Yii::$app->user->can('activateCountry') && $model->deleted === 1) {
                            $url = Url::to(['activate', 'id' => $model->id]);
                            $confirmDeleteMultiple = Yii::t('location', 'Are you sure you want to activate this {item}?',
                                ['item' => Yii::t('location', 'level')]);;
                            echo Html::button(
                                Yii::t('location', '{icon} Activate',
                                    [
                                        'icon' => '<i class="far fa-save"></i>'
                                    ]
                                ),
                                [
                                    'class' => 'btn btn-success',
                                    'onClick' => 'deleteActivateRecord("' . $confirmDeleteMultiple . '", "' . $url . '");'
                                ]);
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>