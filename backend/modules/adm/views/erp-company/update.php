<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\adm\models\ErpCompany */

$this->title = Yii::t('adm', 'Update {name}', [
    'name' => $model->company->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('adm', 'Erp Companies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->company->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('adm', 'Update');
?>
<div class="erp-company-update">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
