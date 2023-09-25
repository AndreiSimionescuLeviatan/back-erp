<?php

use backend\modules\adm\models\User;
use backend\modules\auto\models\Car;
use yii\helpers\Html;

/* @var $user */
/* @var $journeys */
?>

<div class="auto-unvalidated-journeys">
    <p> <?php echo Yii::t('auto', 'Hello') . ' ' . Html::encode($user->first_name); ?>,</p>

    <p>
        <?php
        echo Yii::t('auto', 'The list of users who have not validated their trips for the period') . ' ';
        $lastSunday = date('Y-m-d', strtotime('last Sunday'));
        $twoWeeksAgo = date('Y-m-d', strtotime('-2 weeks', strtotime($lastSunday))); ?>
        <b><?php echo Yii::t('auto', "{$twoWeeksAgo} - {$lastSunday}"); ?></b>
        <?php echo Yii::t('auto', 'is as follows:'); ?>
    </p>
    <table>
        <tr>
            <th><?php echo Yii::t('auto', 'Nr. unvalidated journeys'); ?></th>
            <th><?php echo Yii::t('auto', 'User name'); ?></th>
            <th><?php echo Yii::t('auto', 'Plate number'); ?></th>
        </tr>
        <?php foreach ($journeys as $key => $journey) {
            echo Html::beginTag('tr');
            echo Html::tag('td', Html::encode($journey['count']));
            echo Html::tag('td', Html::encode($journey['user']['last_name'] . ' ' . $journey['user']['first_name']));
            echo Html::tag('td', Html::encode($journey['plate_number']));
            echo Html::endTag('tr');
        } ?>
</div>
