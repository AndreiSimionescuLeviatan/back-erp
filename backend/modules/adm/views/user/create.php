<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\adm\models\User */

$this->title = Yii::t('adm', 'Create User');
$this->params['breadcrumbs'][] = ['label' => Yii::t('adm', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create">

    <h1><?php echo Html::encode($this->title) ?></h1>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
