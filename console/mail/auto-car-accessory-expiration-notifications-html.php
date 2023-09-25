<?php

use yii\helpers\Html;

/* @var $notificationContent */
/* @var $user */

?>

<div class="car-accessory-expire-notification">
    <p><?php echo Yii::t('app', 'Hello') . ' ' . Html::encode($user->first_name) ?>,</p>

    <p><?php echo Yii::t('app', 'The following accessories are expiring') . ':'?></p>
    <ol>
        <?php
        foreach ($notificationContent as $item) {
            echo Html::tag('li', Html::a(Html::encode($item)));
        }
        ?>
    </ol>
    <p><?php echo Yii::t('app', 'A wonderful day') . '!'?> <br><?php echo Yii::t('app', 'Econfaire ID Team')?></p>
</div>
