<?php

use backend\modules\adm\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\modules\adm\models\ErpCompany */

$this->title = $model->company->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('adm', 'Erp companies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="erp-company-view">
    <div class="row">
        <div class="col-6">
            <h1><?php echo Html::encode($this->title) ?></h1>
            <?php $thW = '150px'; ?>
            <?php echo DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'label' => Yii::t('adm', 'Id'),
                        'attribute' => 'id',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'Company'),
                        'attribute' => 'company_id',
                        'value' => $model->company->name,
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'General manager'),
                        'attribute' => 'general_manager_id',
                        'value' => $model->generalManager->fullName(),
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'Deputy general manager'),
                        'attribute' => 'deputy_general_manager_id',
                        'value' => !empty($model->deputyGeneralManager) ? $model->deputyGeneralManager->fullName() : '-',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'Technical manager'),
                        'attribute' => 'technical_manager_id',
                        'value' => !empty($model->technicalManager) ? $model->technicalManager->fullName() : '-',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'Executive manager'),
                        'attribute' => 'executive_manager_id',
                        'value' => !empty($model->executiveManager) ? $model->executiveManager->fullName() : '-',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'Added'),
                        'attribute' => 'added',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'Added By'),
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
                        'label' => Yii::t('adm', 'Updated'),
                        'attribute' => 'updated',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW],
                        'value' => empty($model->updated) ? '-' : $model->updated
                    ],
                    [
                        'label' => Yii::t('adm', 'Updated By'),
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
                        Yii::t('adm', '{icon} Back',
                            [
                                'icon' => '<i class="fas fa-chevron-circle-left"></i>'
                            ]
                        ), Url::previous('erp_company'), ['class' => 'btn btn-info']);
                    ?>
                </div>
                <div class="col-6 text-right item_action_btns_container">
                    <?php
                    if ($model->deleted === 0) {
                        $url = Url::to(['delete', 'id' => $model->id]);
                        $confirmDeleteMultiple = Yii::t('adm', 'Are you sure you want to delete this erp company?');
                        echo Html::button(
                            Yii::t('adm', '{icon} Delete',
                                [
                                    'icon' => '<i class="far fa-trash-alt"></i>'
                                ]
                            ),
                            [
                                'class' => 'btn btn-danger',
                                'onClick' => 'deleteActivateRecord("' . $confirmDeleteMultiple . '", "' . $url . '");'
                            ]);
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>


</div>
