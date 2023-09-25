<?php

use yii\helpers\Html;

/* @var $documentExpire */
/* @var $user */

?>

<div class="document-expire-notification">
    <p>Bună <?php echo Html::encode($user->first_name) ?>,</p>

    <p>Următoarele documente sunt în curs de expirare:</p>
    <ol>
        <?php
        foreach ($documentExpire as $item) {
            echo Html::tag('li', Html::a(Html::encode($item)));
        }
        ?>
    </ol>
    <p>O zi minunată! <br>Echipa Econfaire ID</p>
</div>
