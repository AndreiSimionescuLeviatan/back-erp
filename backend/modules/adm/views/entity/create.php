<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\adm\models\Entity */

$this->title = Yii::t('adm', 'Create Entity');
$this->params['breadcrumbs'][] = ['label' => Yii::t('adm', 'Entities'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="entity-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
