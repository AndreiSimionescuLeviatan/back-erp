<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\adm\models\ErpCompany */

$this->title = Yii::t('adm', 'Create Erp Company');
$this->params['breadcrumbs'][] = ['label' => Yii::t('adm', 'Erp companies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="erp-company-create">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
