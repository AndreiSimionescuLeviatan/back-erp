<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\crm\models\search\CompanySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="company-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

   <?php echo $form->field($model, 'id') ?>

   <?php echo $form->field($model, 'code') ?>

   <?php echo $form->field($model, 'name') ?>

   <?php echo $form->field($model, 'cui') ?>

    <?php // echo $form->field($model, 'reg_number') ?>

    <?php // echo $form->field($model, 'legal_administrator') ?>

    <?php // echo $form->field($model, 'country_id') ?>

    <?php // echo $form->field($model, 'city_id') ?>

    <?php // echo $form->field($model, 'address') ?>

    <?php // echo $form->field($model, 'deleted') ?>

    <?php // echo $form->field($model, 'added') ?>

    <?php // echo $form->field($model, 'added_by') ?>

    <?php // echo $form->field($model, 'updated') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <div class="form-group">
       <?php echo Html::submitButton(Yii::t('crm', 'Search'), ['class' => 'btn btn-primary']) ?>
       <?php echo Html::resetButton(Yii::t('crm', 'Reset'), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
