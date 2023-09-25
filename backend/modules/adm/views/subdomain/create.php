<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\adm\models\Subdomain */

$this->title = Yii::t('adm', 'Create Subdomain');
$this->params['breadcrumbs'][] = ['label' => Yii::t('adm', 'Subdomains'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="subdomain-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
