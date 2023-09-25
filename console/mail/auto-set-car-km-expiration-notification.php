<?php

use yii\helpers\Html;

/* @var $documentExpire */
/* @var $user */
/* @var $plateNumber */
/* @var $isCarKm */
/* @var $isCarConsumption */

?>

<div class="set-km-notification">
    <p><?php echo Yii::t('auto', 'Hello') . " " . Html::encode($user->first_name); ?>,</p>
    <p><?php
    if ($isCarKm && $isCarConsumption){?>
        <?php echo Yii::t('auto', 'Please enter the number of kilometers and the consumption from the board for the plate number {plateNumber} into the app.', ['plateNumber' => $plateNumber]);
    } else if ($isCarConsumption){?>
        <?php echo Yii::t('auto', 'Please enter the consumption from the board for the plate number {plateNumber} into the app.', ['plateNumber' => $plateNumber]);
    } else if ($isCarKm) {?>
        <?php echo Yii::t('auto', 'Please enter the number of kilometers from the board for the plate number {plateNumber} into the app.', ['plateNumber' => $plateNumber]);
    }?></p>
    <p><?php echo Yii::t('auto', 'Best Regards!'); ?> <br><?php echo Yii::t('auto', 'Team Econfaire ID.'); ?></p>
</div>