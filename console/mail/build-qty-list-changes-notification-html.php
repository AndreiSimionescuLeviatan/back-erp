<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $receiver */
/* @var $changesList [] */

/**
 *  * Buna FIRST_NAME,
 *
 * S-au facut modificari la urmatoarele liste de cantati:
 *
 * 1. Proiect1 - Obiect1 - Specialitate2 (va fi un link)
 * -> link care va face navigarea in pagina "quantity-list-changes"
 * -> la nivel de pagina vor fi filtrate doar insert-urile din intervalul orar
 *
 * 2. Proiect1 - Obiect2 - Specialitate2
 *
 * 3. Proiect1 - Obiect3 - Specialitate2
 *
 * O zi minunata!
 * Echipa Econfaire ID
 */
?>
<div class="qty-changes-notification">
    <p><?php echo Yii::t('app', 'Hello') . ' ' . Html::encode($receiver->first_name); ?>,</p>

    <p><?php echo Yii::t('app', 'Changes have been made to the following lists of quantities') . ':'; ?></p>
    <ol>
        <?php
        foreach ($changesList as $item) {
            echo Html::tag('li', Html::a(Html::encode($item['text']), $item['href']));
        }
        ?>
    </ol>
    <p><?php echo Yii::t('app', 'A wonderful day') . '!'; ?> <br><?php echo Yii::t('app', 'Econfaire ID Team'); ?></p>
</div>
