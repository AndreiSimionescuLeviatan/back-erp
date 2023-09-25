<?php

use yii\bootstrap4\Html;
use yii\helpers\Url;

// Text input basic
$form->field($model, 'code')->textInput(['maxlength' => true]);

// Text input custom (class/label/id)
$form->field($model, 'code', [
    'options' => [
        'class' => 'form-group col-6',
        'id' => 'id-unique',
    ]
])->textInput(['maxlength' => true])->label(Yii::t('app', 'Code'));

// Custom submit button
Html::submitButton(Yii::t('app', $model->isNewRecord ? 'Save new article' : 'Update article'), ['class' => 'btn btn-success']);

// Custom submit with back button
?>
<div class="form-group m-0 pb-3 text-right">
    <?php echo Html::a(
        Yii::t('app', 'Cancel'),
        Url::previous(),
        ['class' => 'btn btn-primary']
    ); ?>
    <?php echo Html::submitButton(Yii::t('app', $model->isNewRecord ? 'Save new article' : 'Update article'), ['class' => 'btn btn-success']); ?>
</div>