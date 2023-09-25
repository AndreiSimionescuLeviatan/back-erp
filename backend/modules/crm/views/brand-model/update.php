<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\crm\models\BrandModel */
/* @var $existingModel backend\modules\crm\models\BrandModel */

$this->title = Yii::t('crm', 'Update Model: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('crm', 'Models'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
?>
<div class="brand-model-update">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <?php echo $this->render('_form', [
        'model' => $model,
        'existingModel' => $existingModel,
    ]) ?>

</div>
