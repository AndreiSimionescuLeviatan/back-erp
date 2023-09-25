<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\modules\adm\models\User */
/* @var $user_companies_names string | null */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => Yii::t('adm', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">
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
                        'label' => Yii::t('adm', 'Username'),
                        'attribute' => 'username',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'First name'),
                        'attribute' => 'first_name',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'Last name'),
                        'attribute' => 'last_name',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'E-mail'),
                        'attribute' => 'email',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'Companies'),
                        'attribute' => 'companies',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW],
                        'value' => function ($model) {
                            return !empty($model->companies) ?
                                implode(", ", ArrayHelper::getColumn($model->companies, 'name')) :
                                '-';
                        }
                    ],
                    [
                        'label' => Yii::t('adm', 'Status'),
                        'attribute' => 'status',
                        'value' =>
                            $model->status == 10 ?
                                Yii::t('adm', '<span class="badge badge-success">Active</span>') :
                                ($model->status == 0 ?
                                    Yii::t('adm', '<span class="badge badge-danger">Deleted</span>') :
                                    Yii::t('adm', '<span class="badge badge-warning">Inactive</span>')),
                        'format' => 'html',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'Created'),
                        'attribute' => 'created_at',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW],
                        'format' => ['date', 'php:d-m-Y']
                    ],
                    [
                        'label' => Yii::t('adm', 'Updated'),
                        'attribute' => 'updated_at',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW],
                        'format' => ['date', 'php:d-m-Y']
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
                            ['icon' => '<i class="fas fa-chevron-circle-left"></i>']
                        ), Url::previous('user'), ['class' => 'btn btn-info']);
                    ?>
                </div>
                <div class="col-6 text-right item_action_btns_container">
                    <?php echo Html::a(
                        Yii::t('adm', '{icon} Update',
                            ['icon' => '<i class="far fa-edit"></i>']
                        ), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?php
                    if ($model->status === 10) {
                        $url = Url::to(['delete', 'id' => $model->id]);
                        $confirmDeleteMultiple = Yii::t('adm', 'Are you sure you want to delete this {item}?',
                            ['item' => Yii::t('adm', 'user')]);
                        echo Html::button(
                            Yii::t('adm', '{icon} Delete',
                                ['icon' => '<i class="far fa-trash-alt"></i>']
                            ),
                            ['class' => 'btn btn-danger',
                                'onClick' => 'deleteActivateRecord("' . $confirmDeleteMultiple . '", "' . $url . '");']);
                    } else {
                        $url = Url::to(['activate', 'id' => $model->id]);
                        $confirmDeleteMultiple = Yii::t('adm', 'Are you sure you want to activate this {item}?',
                            ['item' => Yii::t('adm', 'user')]);;
                        echo Html::button(
                            Yii::t('adm', '{icon} Activate',
                                ['icon' => '<i class="far fa-save"></i>']
                            ),
                            ['class' => 'btn btn-success',
                                'onClick' => 'deleteActivateRecord("' . $confirmDeleteMultiple . '", "' . $url . '");']);
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>
