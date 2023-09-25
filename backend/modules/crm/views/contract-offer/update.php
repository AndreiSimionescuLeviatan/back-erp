<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\crm\models\ContractOffer */

$this->title = Yii::t('crm', 'Update contract offer');
$this->params['breadcrumbs'][] = ['label' => Yii::t('crm', 'Contract offers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('crm', 'Update');
?>
<div class="contract-offer-update">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
