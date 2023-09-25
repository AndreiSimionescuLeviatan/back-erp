<?php

use backend\modules\adm\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\modules\crm\models\ContractOffer */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('crm', 'Contract Offers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="contract-offer-view">
    <div class="system-view">
        <div class="row">
            <div class="col-6">
                <h1><?php echo Html::encode($this->title) ?></h1>
                <?php $thW = '200px'; ?>
                <?php echo DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'label' => Yii::t('crm', 'Id'),
                            'attribute' => 'id',
                            'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                        ],
                        [
                            'captionOptions' => ['class' => 'text-right', 'width' => $thW],
                            'label' => Yii::t('crm', 'Company'),
                            'attribute' => 'company_id',
                            'value' => function ($model) {
                                return !empty($model->company) ? $model->company->name : '-';
                            },
                        ],
                        [
                            'label' => Yii::t('crm', 'Code'),
                            'attribute' => 'code',
                            'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                        ],
                        [
                            'label' => Yii::t('crm', 'Name'),
                            'attribute' => 'name',
                            'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                        ],
                        [
                            'label' => Yii::t('crm', 'Description'),
                            'attribute' => 'description',
                            'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                        ],
                        [
                            'label' => Yii::t('crm', 'Deleted'),
                            'attribute' => 'deleted',
                            'value' => $model->deleted == 0 ?
                                Yii::t('crm', '<span class="badge badge-success">NO</span>') :
                                Yii::t('crm', '<span class="badge badge-danger">YES</span>'),
                            'captionOptions' => ['class' => 'text-right', 'width' => $thW],
                            'contentOptions' => ['class' => 'align-middle'],
                            'format' => 'html'
                        ],
                        [
                            'label' => Yii::t('crm', 'Added'),
                            'attribute' => 'added',
                            'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                        ],
                        [
                            'label' => Yii::t('crm', 'Added By'),
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
                            'label' => Yii::t('crm', 'Updated'),
                            'attribute' => 'updated',
                            'captionOptions' => ['class' => 'text-right', 'width' => $thW],
                            'value' => empty($model->updated) ? '-' : $model->updated
                        ],
                        [
                            'label' => Yii::t('crm', 'Updated By'),
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
                            Yii::t('crm', '{icon} Back',
                                [
                                    'icon' => '<i class="fas fa-chevron-circle-left"></i>'
                                ]
                            ), Url::previous('page'), ['class' => 'btn btn-info']);
                        ?>
                    </div>
                    <div class="col-6 text-right item_action_btns_container">
                        <?php if (Yii::$app->user->can('updateContractOffer')) {
                            echo Html::a(
                                Yii::t('crm', '{icon} Update',
                                    [
                                        'icon' => '<i class="far fa-edit"></i>'
                                    ]
                                ), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']);
                        } ?>
                        <?php if ($model->deleted === 0) {
                            if (Yii::$app->user->can('deleteContractOffer')) {
                                $url = Url::to(['delete', 'id' => $model->id]);
                                $confirmDeleteMultiple = Yii::t('crm', 'Are you sure you want to delete this {item}?',
                                    ['item' => Yii::t('crm', 'contract-offer')]);
                                echo Html::button(
                                    Yii::t('crm', '{icon} Delete',
                                        [
                                            'icon' => '<i class="far fa-trash-alt"></i>'
                                        ]
                                    ),
                                    [
                                        'class' => 'btn btn-danger',
                                        'onClick' => 'deleteActivateRecord("' . $confirmDeleteMultiple . '", "' . $url . '");'
                                    ]);
                            }
                        } else {
                            if (Yii::$app->user->can('activateContractOffer')) {
                                $url = Url::to(['activate', 'id' => $model->id]);
                                $confirmDeleteMultiple = Yii::t('crm', 'Are you sure you want to activate this {item}?',
                                    ['item' => Yii::t('crm', 'contract-offer')]);;
                                echo Html::button(
                                    Yii::t('crm', '{icon} Activate',
                                        [
                                            'icon' => '<i class="far fa-save"></i>'
                                        ]
                                    ),
                                    [
                                        'class' => 'btn btn-success',
                                        'onClick' => 'deleteActivateRecord("' . $confirmDeleteMultiple . '", "' . $url . '");'
                                    ]);
                            }
                        } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
