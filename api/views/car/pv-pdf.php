<?php

use backend\modules\adm\models\User;

/** @var $model */
/** @var $zoneModel */
/** @var $backgroundImage */
/** @var $date */
/** @var $stylePdf */
/** @var $sign */
/** @var $footer */
/** @var $handingCar */
/** @var $displayPage */
/** @var $accessories */

/**
 * @todo Move to params or other place not to repeat
 */
$companyLegalAdminName = [
    1 => 'Gheorghe Andreea Alexandra',
    2 => 'Podaru Cătălin',
    3 => 'Podaru Alina Mariana',
];

$documents = [
    'Taxa de drum: Rovinieta' => 'vignette_valid_until',
    'Certificat de inspecție tehnica ITP' => 'itp_valid_until',
    'Polița de asigurare RCA' => 'rca_valid_until',
    'Polița de asigurare CASCO' => 'casco_valid_until',
]
?>

<style>

    .column-image-align {
        float: left;
        width: 25%;
        padding-left: 50px;
        padding-top: 50px;
        margin: 5px;
    }

    .text-image-align {
        text-align: justify;
        font-size: 12px;
        font-weight: bold;
        padding-top: 15px;
        padding-left: 35px;
    }

    .column-header {
        border: 1px solid black;
        text-align: center;
    }

    .td-column-content {
        border: 1px solid black;
    }

    .align-image {
        padding-top: 5px;
        width: 100%;
        height: 100%;
    }

    @page {
        background-image: url(<?php echo $backgroundImage ?>);
        background-image-resize: 6;
        margin-top: 120px;
        margin-bottom: 100px;
    }

</style>

<div class="bg-image"
     style='width: 95%; height: 90%;   display: inline-block;overflow: hidden; position: relative;'>
    <h2 class="for-preview"
        style='text-align: right; font-size: 15px; font-weight: normal; padding-right: 10px; color: <?php echo $stylePdf[0] ?>'>
        Nr.înreg. <span style="color: black">...............</span> din
        <span style="color: black"><?php echo $date ?></span></h2>
    <h2 style='text-align: center; font-size: 20px; font-weight: bold; padding-top: 10px;'>Proces verbal de predare
        -
        primire</h2>
    <h2 style='text-align: left; font-size: 15px; font-weight: normal; padding-left: 75px;'>Nr. inmatriculare
        autovehicul
        <span style="font-weight: bold"><?php echo $model['plate_number'] ?></span></h2>
    <h2 style='text-align: left; font-size: 15px; font-weight: normal; padding-left: 75px;'>Locul predării:
        <span><?php echo $model['company']['address'] ?></span></h2>
    <h2 style='text-align: left; font-size: 15px; font-weight: bold; padding-left: 75px;'>PREDATOR:</h2>
    <h2 style='text-align: justify; font-size: 15px; font-weight: normal; padding-left: 130px;'>
        <?php if ($handingCar === 'check_in') { ?>
            <span style='font-weight: bold;'><?php echo $model['company']['name'] ?></span>
            , cu sediul social în <?php echo $model['company']['address'] ?>
            , înregistrată la Registrul Comerțului sub numărul <?php echo $model['company']['reg_number'] ?>
            , CUI <?php echo $model['company']['cui'] ?>
            , reprezentată de Administratorul <?php echo $companyLegalAdminName[$model['company_id']]; ?>.
        <?php } else { ?>
            <span style='font-weight: bold;'><?php echo User::$users[$model['updated_by']]['first_name'] . ' ' . User::$users[$model['updated_by']]['last_name']; ?></span>
        <?php } ?>
    </h2>

    <h2 style='text-align: left; font-size: 15px; font-weight: bold; padding-left: 75px;'>și</h2>
    <h2 style='text-align: left; font-size: 15px; font-weight: bold; padding-left: 75px; padding-top:5px;'>
        PRIMITOR:</h2>
    <h2 style='text-align: justify; font-size: 15px; font-weight: normal; padding-left: 130px;'>
        <?php if ($handingCar === 'check_in') { ?>
            <span style='font-weight: bold;'><?php echo User::$users[$model['updated_by']]['first_name'] . ' ' . User::$users[$model['updated_by']]['last_name']; ?></span>
        <?php } else { ?>
            <span style='font-weight: bold;'><?php echo $model['company']['name'] ?></span>
            , cu sediul social în <?php echo $model['company']['address'] ?>
            , înregistrată la Registrul Comerțului sub numărul <?php echo $model['company']['reg_number'] ?>
            , CUI <?php echo $model['company']['cui'] ?>
            , reprezentată de Administratorul <?php echo $companyLegalAdminName[$model['company_id']]; ?>.
        <?php } ?>
    </h2>
    <h2 style='text-align: left; font-size: 15px; font-weight: normal; padding-left: 75px; padding-top:10px;'>
        <span style='font-weight: bold;'> AUTOVEHICUL: </span> Numărul de înmatriculare
        <span style='font-weight: bold;'><?php echo $model['plate_number'] ?>, </span>
        categoria
        <span style='font-weight: bold;'>AUTOTURISM M1 </span> marca
        <span style='font-weight: bold;'><?php echo $model['brand']['name'] ?> </span>
        tipul
        <span style='font-weight: bold;'><?php echo $model['brandModel']['name'] ?></span>.
    </h2>
    <h2 style='text-align: left; font-size: 15px; font-weight: normal; padding-left: 75px; padding-top: 10px;'>
        Numărul
        de km la bord <span style='font-weight: bold;'>------</span> km.</h2>
    <h2 style='text-align: left; font-size: 15px; font-weight: normal; padding-left: 75px; padding-top: 10px;'>
        Împreună
        cu autovehiculul s-au predat și următoarele documente și accesorii: </h2>
    <h2 style='text-align: left; font-size: 15px; font-weight: bold; padding-left: 75px; padding-top:5px;'>
        Documente:</h2>
    <table style='width:95%; font-size: 12px; border: 1px solid black; border-collapse: collapse; margin-left: 100px; vertical-align: 25px;'>
        <tr>
            <th class="column-header">Nr.Crt</th>
            <th class="td-column-content">Denumire document</th>
            <th class="td-column-content">Observații</th>
        </tr>
        <?php
        $count = 1;
        foreach ($documents as $key => $columnTable) { ?>
            <tr>
                <td class="column-header"><?php echo $count . '.' ?></td>
                <td class="td-column-content"><?php echo $key ?></td>
                <td class="td-column-content">Valabilitate până la
                    <?php echo explode(' ', $model['carDocuments'][$columnTable])[0]; ?></td>
            </tr>
            <?php
            $count++;
        } ?>
    </table>
</div>

<div class="bg-image" style='width: 95%; height: 90%;   display: inline-block;overflow: hidden; position: relative;'>
    <h2 class="for-preview"
        style='text-align: left; font-size: 15px; font-weight: bold; padding-left: 75px; padding-top:5px;'>
        Accesorii:</h2>
    <table style='width:95%; font-size: 12px; border: 1px solid black; border-collapse: collapse; margin-left: 100px; vertical-align: 25px;'>
        <tr>
            <th class="td-column-content">Denumire accesoriu</th>
            <th class="column-header">Cantitate</th>
            <th class="td-column-content">Observții</th>
        </tr>
        <?php
        $itemCount = 1;
        if (!empty($accessories)) {
            foreach ($accessories as $item) {
                ?>
                <tr>
                    <td class="td-column-content w-auto"><?php echo $item['name']; ?></td>
                    <td class="column-header w-auto"><?php echo (!empty($item['count']) ? $item['count'] : 1) ?></td>
                    <td class="td-column-content w-auto"><?php echo !empty($item['observation']) ? $item['observation'] : ' '; ?></td>
                </tr>
                <?php
                $itemCount++;
            }
        }
        ?>
    </table>
    <h2 style='text-align: left; font-size: 15px; font-weight: normal; padding-left: 75px; padding-top: 10px;'>
        Primitorul a
        preluat autovehiculul în dotare cu cele amintite mai sus, în urma unui inventar</h2>
    <h2 style='text-align: left; font-size: 15px; font-weight: bold; padding-left: 75px; padding-top:5px;'>Stare de
        funcționare:</h2>

    <table style='width:95%; margin-left: 100px;font-size: 12px; border: 1px solid black; border-collapse: collapse; vertical-align: 25px;'>
        <?php
        foreach ($zoneModel as $status) {
            if ($status['field'] === 'general_observations') {
                continue;
            }
            ?>
            <tr>
                <th class="td-column-content"><?php echo $status['label'] ?></th>
                <td class="td-column-content"><?php echo $status['carZone']['zoneOption']['text']; ?></td>
            </tr>
        <?php } ?>
    </table>
    <?php
    $displayObservations = 'block;';
    if (empty($zoneModel[7]['carZone']['observations'])) {
        $displayObservations = 'none;';
    }
    ?>
    <h2 style='text-align: left; font-size: 15px; font-weight: normal; padding-left: 75px; display:<?= $displayObservations ?> '>
        Observații:</h2>
    <h2 style='text-align: left; font-size: 15px; font-weight: normal; padding-left: 75px; display:<?= $displayObservations ?> '><?php echo $zoneModel[7]['carZone']['observations'] ?></h2>
    <h2 style='text-align: justify; font-size: 11px; font-weight: normal; padding-left: 75px; padding-top:10px;'>
        Prezentul
        formular a fost încheiat azi, <?php echo $date ?> într-un nr. de 2 exemplare, din care 1 ramâne la predator și 1
        la
        primitor.
    </h2>

    <table style='width:100%; margin-top: 50px;'>
        <tr>
            <th style='width: 150px; padding-left: 75px; font-weight: bold; text-align: left;'>Am predat</th>
            <th style='width: 150px; padding-left: 75px; font-weight: bold; text-align: right;'>Am preluat</th>
        </tr>
        <tr>
            <th style='text-align: left; font-weight: bold; vertical-align: top; padding-left: <?php $handingCar === 'check_in' ? $signPadding = '75px;' : $signPadding = '0;';
            echo $signPadding ?>'>
                <?php if ($handingCar === 'check_out') { ?>
                    <img style="padding-left: 75px" src="<?php echo $sign; ?>" alt="sign" width=120 height=120>
                <?php } else { ?>
                    <?php echo $model['company']->name; ?>
                <?php } ?>
            </th>
            <th style='text-align: right; font-weight: bold; vertical-align: top'>
                <?php if ($handingCar === 'check_out') { ?>
                    <?php echo $model['company']->name; ?>
                <?php } else { ?>
                    <img src="<?php echo $sign; ?>" alt="sign" width=120 height=120>
                <?php } ?>
            </th>
        </tr>
    </table>
</div>

<?php
$displayPage = 'none';

for ($i = 0; $i < count($zoneModel); $i++) {
    if (!empty($zoneModel[$i]['carZone']['zone_photo'])) {
        $displayPage = 'inline-block';
    }
}
?>

<div class="bg-image"
     style='width: 100%; height: 100%; display: <?php echo $displayPage ?>;overflow: hidden; position: relative;'>
    <h2 style='text-align: center; font-size: 20px; font-weight: bold; padding-top: 10px;'>Imagini stare de
        funcționare</h2>

    <?php
    foreach ($zoneModel as $carZone) {
        if (!empty($carZone['carZone']['zone_photo'])) { ?>
            <div class="column-image-align">
                <span class="text-image-align"><?= $carZone['label'] ?></span>
                <img src='<?= Yii::getAlias($carZone['carZone']['zone_photo']) ?>' alt="<?= $carZone['label'] ?>"
                     class="align-image">
            </div>
        <?php }
    }
    ?>
</div>
