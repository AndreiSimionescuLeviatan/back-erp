<?php

use backend\modules\adm\models\User;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $articles
 * @var $equipments
 * @var $receiver
 * @var $changesStartTime
 * @var $changesStopTime
 * @var $specialityID
 */
?>

<div class="qty-changes-notification">
    <p><?php echo Yii::t('app', 'Hello') . ' ' . Html::encode($receiver->first_name); ?>,</p>

    <p><?php echo Yii::t('app', 'The following items/equipment were created in the interval') . ' '
            . $changesStartTime . ' - ' . $changesStopTime
            . ' ' . Yii::t('app', 'and requires validation') . ' '; ?>
    </p>

    <p><b><?php echo Yii::t('app', 'Articles') . ':'; ?></b></p>
    <ol>

        <?php foreach ($articles as $article) {
            $articleText = $article['speciality']['name'] . ' - ' . $article['name'] . ' - ' . User::$users[$article['added_by']];
            $codeFilterLinkArticle = Url::toRoute([
                '/build/item-validate/index',
                'itemType' => 1,
                'ItemValidateSearch[id]' => $article['id'],
                'ItemValidateSearch[speciality_id]' => $specialityID,
            ]);

            echo Html::beginTag('li');
            echo Html::a(Html::encode($articleText), $codeFilterLinkArticle);
            echo Html::endTag('li');
        } ?>
    </ol>
    <br>
    <p><b><?php echo Yii::t('app', 'Equipments') . ':'; ?></b></p>
    <ol>
        <?php foreach ($equipments as $equipment) {
            $equipmentText = $equipment['speciality']['name'] . ' - ' . $equipment['short_name'] . ' - ' . User::$users[$equipment['added_by']];
            $codeFilterLinkEquipment = Url::toRoute([
                '/build/equipment/index',
                'EquipmentSearch[code]' => $equipment['code'],
                'speciality' => $specialityID
            ]);

            echo Html::beginTag('li');
            echo Html::a(Html::encode($equipmentText), $codeFilterLinkEquipment);
            echo Html::endTag('li');
        } ?>
    </ol>

    <p>
        <?php echo Yii::t('app', 'View all articles created in the last hour') . ' - ';
        $articleLinkAll = Url::toRoute(['/build/item-validate/index',
            'itemType' => 1,
            'ItemValidateSearch[start_date]' => $changesStartTime,
            'ItemValidateSearch[end_date]' => $changesStopTime,
            'ItemValidateSearch[speciality_id]' => $specialityID,
        ]);
        echo Html::a('Link', $articleLinkAll); ?>
    </p>

    <p>
        <?php echo Yii::t('app', 'View all equipments created in the last hour') . ' - ';
        $equipmentLinkAll = Url::toRoute(['/build/equipment/index',
            'EquipmentSearch[start_date]' => $changesStartTime,
            'EquipmentSearch[end_date]' => $changesStopTime,
            'speciality' => $specialityID,
        ]);
        echo Html::a('Link', $equipmentLinkAll); ?>
    </p>

    <p><?php echo Yii::t('app', 'A wonderful day') . '!'; ?>
        <br><?php echo Yii::t('app', 'Econfaire ID Team'); ?></p>
</div>
