<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $data
 * @var $receiver
 */
?>

<div class="fam-validate">
    <p><?php echo Yii::t('app', 'Hello') . ' ' . Html::encode($receiver->first_name) . ','; ?></p>

    <p><?php echo Yii::t('app', 'We remind you that there are FAMs that require validation') . ' ' .
            Yii::t('app', 'To complete the validation, please access the links in the description') . ':'; ?></p>
    <?php
    $name = '';
    foreach ($data as $item) {
        $projectSpeciality = "<p><b>{$item['project']}:</b></p>";
        if ($name != $projectSpeciality) {
            echo $projectSpeciality;
        }
        $name = $projectSpeciality;

        $text = $item['fam'];

        $link = Url::toRoute([
            '/fam/fam-version/validate',
            'id' => $item['fam_version_id'],
            'validate' => 1
        ]);

        echo Html::a(Html::encode($text), $link, ['style' => 'margin-left: 40px;']);
        ?> <br>
    <?php } ?>

    <p><?php echo Yii::t('app', 'A wonderful day') . '!'; ?>
        <br><?php echo Yii::t('app', 'Econfaire ID Team'); ?></p>
</div>