<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\crm\models\Brand */
/* @var $entityDomainModel \backend\modules\crm\models\EntityDomain */
/* @var $existingBrand backend\modules\crm\models\Brand */

$this->title = Yii::t('crm', 'Update Brand');
$this->params['breadcrumbs'][] = ['label' => Yii::t('crm', 'Brands'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('crm', 'Update');
?>
<div class="brand-update">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <?php echo $this->render('_form', [
        'model' => $model,
        'entityDomainModel' => $entityDomainModel,
        'existingBrand' => $existingBrand,
    ]) ?>

</div>
