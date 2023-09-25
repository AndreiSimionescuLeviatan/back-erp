<?php

use backend\modules\crm\models\IbanCompany;
use yii\helpers\Html;

/* @var $key */
/* @var $iban */
/* @var $isLastIban */
?>

<div id="iban-container-<?php echo $key; ?>" class="input-group col-12 mb-3 p-0">
    <?php
    echo Html::input('text', "IbanCompanies[]", $iban, [
        'class' => 'form-control text-uppercase mr-3 iban_input',
        'id' => "iban-input-{$key}",
        'data-iban-key' => $key
    ]);
    echo Html::beginTag('span', [
        'class' => 'input-group-append'
    ]);
    ?>
    <?php if (!$isLastIban) { ?>
        <?php echo Html::button('<i class="fas fa fa-minus"></i>', [
            'id' => "iban-button-{$key}",
            'class' => 'btn btn-danger btn-flat iban_button',
            'title' => Yii::t('crm', 'Remove IBAN'),
            'onClick' => "removeIban({$key})",
        ]);
    } ?>
    <?php if ($isLastIban) {
        echo Html::button('<i class="fas fa fa-plus"></i>', [
            'id' => "iban-button-{$key}",
            'class' => 'btn btn-success btn-flat iban_button',
            'title' => Yii::t('crm', 'For adding a new IBAN, complete first this one'),
            'disabled' => !($iban != ''),
            'onClick' => "addIban({$key})",
        ]);
    } ?>
    <?php echo Html::endTag('span'); ?>
</div>