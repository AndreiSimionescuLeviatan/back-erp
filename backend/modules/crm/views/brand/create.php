<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\crm\models\Brand */
/* @var $entityDomainModel \backend\modules\crm\models\EntityDomain */
/* @var $existingBrand backend\modules\crm\models\Brand */

$this->title = Yii::t('crm', 'Create Brand');
$this->params['breadcrumbs'][] = ['label' => Yii::t('crm', 'Brands'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="brand-create">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <?php echo $this->render('_form', [
        'model' => $model,
        'entityDomainModel' => $entityDomainModel,
        'existingBrand' => $existingBrand,
    ]) ?>

</div>
