<?php

use yii\helpers\Html;

/* @var $documentExpire */
/* @var $user */

?>

<div class="documents-without-exp-date-notification">
    <p>Bună <?php echo Html::encode($user->first_name) ?>,</p>

    <p>Următoarele documente auto <b>nu au o dată validă de expirare</b>:</p>
    <ol>
        <?php
        foreach ($documentExpire as $item) {
            echo Html::tag('li', $item);
        }
        ?>
    </ol>
    <p>O zi minunată! <br>Echipa Econfaire ID</p>
</div>
