<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\adm\models\search\ErpCompanySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="erp-company-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?php echo $form->field($model, 'id'); ?>
    <?php echo $form->field($model, 'company_id'); ?>
    <?php echo $form->field($model, 'general_manager_id'); ?>
    <?php echo $form->field($model, 'deputy_general_manager_id'); ?>
    <?php echo $form->field($model, 'technical_manager_id'); ?>
    <?php echo $form->field($model, 'executive_manager_id'); ?>
    <?php echo $form->field($model, 'added'); ?>
    <?php echo $form->field($model, 'added_by'); ?>
    <?php echo $form->field($model, 'updated'); ?>

    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('adm', 'Search'), ['class' => 'btn btn-primary']); ?>
        <?php echo Html::resetButton(Yii::t('adm', 'Reset'), ['class' => 'btn btn-outline-secondary']); ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
