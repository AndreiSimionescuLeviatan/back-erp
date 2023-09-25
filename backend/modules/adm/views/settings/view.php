<?php

use backend\modules\adm\models\User;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\modules\adm\models\Settings */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('adm', 'Settings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="settings-view">
    <div class="row">
        <div class="col-6">
            <h1 style="white-space: nowrap; width: 600px; overflow: hidden; text-overflow: ellipsis;"><?php echo Html::encode($this->title) ?></h1>
            <?php $thW = '200px'; ?>
            <?php echo DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'label' => Yii::t('adm', 'Id'),
                        'attribute' => 'id',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'Value'),
                        'attribute' => 'value',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'Name'),
                        'attribute' => 'name',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'Description'),
                        'attribute' => 'description',
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
                    ]
                ],
            ]) ?>
        </div>
    </div>
</div>
