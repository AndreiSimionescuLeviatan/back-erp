<?php
use yii\helpers\Html;

/* @var $user */
?>

<div class="set-km-notification">
    <p><?php echo Yii::t('auto', 'Hello') . " " . Html::encode($user->first_name); ?>,</p>
    <p><?php echo Yii::t('auto', 'For generating the roadmap, please enter the signature from the sidebar of the application.') ?></p>
    <p><?php echo Yii::t('auto', 'Best Regards!'); ?> <br><?php echo Yii::t('auto', 'Team Econfaire ID.'); ?></p>
</div>


