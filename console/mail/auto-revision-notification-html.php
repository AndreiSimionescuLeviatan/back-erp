<?php

use yii\helpers\Html;

/* @var $notificationContent */
/* @var $user */

?>

<div class="revision-notification">
    <p><?php echo Yii::t('cmd-auto', 'Hello') . ' ' . Html::encode($user->first_name) ?>,</p>

    <p><?php echo Yii::t('cmd-auto', 'For the following cars the revision must be done') . ':'?></p>
    <ol>
        <?php
        foreach ($notificationContent as $item) {
            echo Html::tag('li', $item);
        }
        ?>
    </ol>
    <p><?php echo Yii::t('cmd-auto', 'A wonderful day') . '!'?> <br><?php echo Yii::t('app', 'Econfaire ID Team')?></p>
</div>
