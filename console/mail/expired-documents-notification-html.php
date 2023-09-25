<?php

use yii\helpers\Html;

/* @var $documentExpire */
/* @var $user */

?>

<div class="expired-documents-notification">
    <p>Bună <?php echo Html::encode($user->first_name) ?>,</p>

    <p>Următoarele documente auto sunt expirate:</p>
    <ol>
        <?php
        foreach ($documentExpire as $item) {
            echo Html::tag('li', $item);
        }
        ?>
    </ol>
    <p>O zi minunată! <br>Echipa Econfaire ID</p>
</div>
