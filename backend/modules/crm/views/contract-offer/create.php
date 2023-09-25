<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\crm\models\ContractOffer */

$this->title = Yii::t('crm', 'Create contract offer');
$this->params['breadcrumbs'][] = ['label' => Yii::t('crm', 'Contract offers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="contract-offer-create">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
