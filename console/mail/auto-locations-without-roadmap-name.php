<?php


/* @var $user */
/* @var $locations */

use yii\helpers\Html;

?>

<div>
    <p> <?php echo Yii::t('auto', 'Hello') . ' ' . Html::encode($user->first_name); ?>,</p>

    <p>
        <?php
        echo Yii::t('auto', 'The list of locations that do not have a roadmap name and are included in the journeys validated by the user during the period '); ?>
        <b><?php echo Yii::t('auto', '{dateFrom} - {dateTo}', [
                'dateFrom' => date('d.m.Y', strtotime('-1 weeks')),
                'dateTo' => date('d.m.Y', strtotime('-1 day')),
            ]); ?></b>
        <?php echo Yii::t('auto', 'is as follows:'); ?>
    </p>
    <table>
        <tr>
            <th><?php echo Yii::t('auto', 'Location Name'); ?></th>
            <th><?php echo Yii::t('auto', 'User name'); ?></th>
            <th><?php echo Yii::t('auto', 'Plate number'); ?></th>
        </tr>
        <?php foreach ($locations as $location) {
            echo Html::beginTag('tr');
            echo Html::tag('td', Html::encode($location['name']));
            echo Html::tag('td', Html::encode($location['last_name'] . ' ' . $location['first_name']));
            echo Html::tag('td', Html::encode($location['plate_number']));
            echo Html::endTag('tr');
        } ?>
    </table>
</div>
