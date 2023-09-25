<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\modules\adm\models\User;

/* @var $this yii\web\View */
/* @var $model backend\modules\adm\models\User */
/* @var $updatePswModel \backend\modules\adm\models\forms\UpdatePswForm */

$this->title = $model->fullName();
$this->params['breadcrumbs'][] = ['label' => Yii::t('adm', 'User'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->title = Yii::t('adm', 'User {fullName}', ['fullName' => $model->fullName()]);
?>
<div class="company-view">
    <div class="row">
        <div class="col-lg-6 col-sm-12">
            <h1><?php echo Html::encode($this->title) ?></h1>
            <?php $thW = '150px' ?>
            <?php echo DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'label' => Yii::t('adm', 'ID'),
                        'attribute' => 'id',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'Username'),
                        'attribute' => 'username',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'Email'),
                        'attribute' => 'email',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'First Name'),
                        'attribute' => 'first_name',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ],
                    [
                        'label' => Yii::t('adm', 'Last name'),
                        'attribute' => 'last_name',
                        'captionOptions' => ['class' => 'text-right', 'width' => $thW]
                    ]
                ],
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-sm-12">
            <?php echo $this->render('_update_psw_form', [
                'model' => $model,
                'updatePswModel' => $updatePswModel,
            ]) ?>
        </div>
    </div>
</div>
