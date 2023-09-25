<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\crm\models\BrandModel */
/* @var $existingModel backend\modules\crm\models\BrandModel */

$this->title = Yii::t('crm', 'Create Model');
$this->params['breadcrumbs'][] = ['label' => Yii::t('crm', 'Models'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="brand-model-create">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <?php echo $this->render('_form', [
        'model' => $model,
        'existingModel' => $existingModel,
    ]) ?>

</div>
