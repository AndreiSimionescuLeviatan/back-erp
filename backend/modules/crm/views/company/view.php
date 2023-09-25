<?php

use backend\modules\adm\models\User;
use backend\modules\crm\models\IbanCompany;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\modules\crm\models\Company */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('crm', 'Companies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
    input[type="radio"] {
        display: none;
    }

    label:not(.form-check-label):not(.custom-file-label) {
        font-weight: 400 !important;
    }
</style>

<div class="company-view">
    <div class="row">
        <div class="col-6">
            <h1><?php echo Html::encode($this->title) ?></h1>
            <?php $thW = '150px'; ?>
            <?php echo DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'label' => Yii::t('crm', 'Id'),
                        'attribute' => 'id',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
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
                        'label' => Yii::t('crm', 'Short Name'),
                        'attribute' => 'short_name',
                        'value' => function ($model) {
                                return !empty($model->short_name) ? $model->short_name : '-';
                            },
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('crm', 'TVA'),
                        'attribute' => 'tva',
                        'value' => function ($model) {
                            if ($model->tva === null || $model->tva === 2) {
                                return '-';
                            } else {
                                return $model->tva == 0 ? Yii::t('crm', '<span class="badge badge-primary">YES</span>') : Yii::t('crm', '<span class="badge badge-info">NO</span>');
                            }
                        },
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW],
                        'contentOptions' => ['class' => 'align-middle'],
                        'format' => 'html'
                    ],
                    [
                        'label' => Yii::t('crm', 'CUI'),
                        'attribute' => 'cui',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('crm', 'Trade register'),
                        'attribute' => 'reg_number',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('crm', 'IBAN'),
                        'attribute' => 'ibanCompanies',
                        'format' => 'raw',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW],
                        'value' => function ($model) {
                            $models = IbanCompany::find()
                                ->select('iban')
                                ->where('company_id = :company_id', [':company_id' => $model->id])
                                ->all();

                            $form = ActiveForm::begin();
                            ActiveForm::end();

                            $iban = $form->field($model, 'ibanCompanies',
                                [
                                    'options' => [
                                        'id' => false,
                                        'class' => 'form-group col-6 mb-n2',
                                    ]
                                ])->radioList(ArrayHelper::map($model['ibanCompanies'], 'id', 'iban'))->label(false);

                            return !empty($models) ? $iban : '-';
                        },
                    ],
                    [
                        'label' => Yii::t('crm', 'Country'),
                        'attribute' => 'country_id',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW],
                        'value' => !empty($model->country->name) ? $model->country->name : '-',
                    ],
                    [
                        'label' => Yii::t('crm', 'County'),
                        'attribute' => 'state_id',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW],
                        'value' => !empty($model->state->name) ? $model->state->name : '-',
                    ],
                    [
                        'label' => Yii::t('crm', 'City'),
                        'attribute' => 'city_id',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW],
                        'value' => !empty($model->city->name) ? $model->city->name : '-',
                    ],
                    [
                        'label' => Yii::t('crm', 'Address'),
                        'attribute' => 'address',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('crm', 'Deleted'),
                        'attribute' => 'deleted',
                        'value' => $model->deleted == 0 ? Yii::t('crm', '<span class="badge badge-success">NO</span>') : Yii::t('crm', '<span class="badge badge-danger">YES</span>'),
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
                        ), Url::previous('company'), ['class' => 'btn btn-info']);
                    ?>
                </div>
                <div class="col-6 text-right item_action_btns_container">
                    <?php echo Html::a(
                        Yii::t('crm', '{icon} Update',
                            [
                                'icon' => '<i class="far fa-edit"></i>'
                            ]
                        ), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?php
                    if ($model->deleted === 0 && Yii::$app->user->can('deleteCompany')) {
                        $url = Url::to(['delete', 'id' => $model->id]);
                        $confirmDeleteMultiple = Yii::t('crm', 'Are you sure you want to delete this {item}?',
                            ['item' => Yii::t('crm', 'company')]);
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
                    } else if (Yii::$app->user->can('activateCompany')) {
                        $url = Url::to(['activate', 'id' => $model->id]);
                        $confirmDeleteMultiple = Yii::t('crm', 'Are you sure you want to activate this {item}?',
                            ['item' => Yii::t('crm', 'company')]);
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
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>
