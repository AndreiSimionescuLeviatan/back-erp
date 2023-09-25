<?php

use api\models\Car;
use api\models\User;

/** @var Car $model */
/** @var User $user */
/** @var $zoneModel */
/** @var $adjustedZoneModels */
/** @var $backgroundImage */
/** @var $date */
/** @var $stylePdf */
/** @var $postZoneOption */
/** @var $footer */
/** @var $handingCar */
/** @var $displayPage */
/** @var $accessories */
/** @var $updatedAccessories */


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
        .images {
            width: 100%;
            height: 100%;
        }

        .column-image-align {
            float: left;
            width: 24%;
            margin: 5px;
            margin-left: 20px;
        }

        .text-image-align {
            text-align: justify;
            font-size: 12px;
            font-weight: bold;
            padding-top: 10px;
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
        }

        @page {
            margin-top: 120px;
            margin-bottom: 100px;
        }

        .bg-image {
            width: 95%;
            height: 548px;
            display: inline-block;
            overflow: hidden;
            position: relative;
        }

        .bg-image.company_1 {
            padding-left: 35px;
            margin-left: -12px;
            width: 101%;
        }

        .bg-image.company_2 {
            padding-left: 55px;
        }

        .for-preview {
            margin-bottom: 10px;
            padding-right: 10px;
            margin-right: 20px;
            text-align: right;
            font-size: 15px;
            font-weight: normal;
        }

        .company_1 .for-preview {
            margin-top: 20%;
        }

        .company_2 .for-preview {
            margin-top: 16%;
        }

        .signatures-container th {
            padding-left: <?php echo $handingCar === 'check_in' ? '0;' : '75px;'?>;
        }

        .fs-1 {
            font-size: 1px;
        }

        .font-weight-bold {
            font-weight: bold;
        }

        .mb-0 {
            margin-bottom: 0;
        }

    </style>

    <div class="card" style="max-width: 380px">
        <div class="bg-image company_<?php echo $model->company_id; ?>">
            <p class="for-preview fs-1 mb-0">
                Nr.înreg. <span class="text-black">...............</span> din <span class="text-black">
            <?php echo $date ?>
        </span>
            </p>
            <p class="text-center font-weight-bold pt-1">
                Proces verbal de predare - primire
            </p>
            <p class="fs-1 mb-0">Nr. inmatriculare
                autovehicul
                <span class="font-weight-bold"><?php echo $model['plate_number'] ?></span></p>
            <p class="fs-1 mb-0">Locul predării:
                <span><?php echo $model['company']['address'] ?></span></p>
            <p class="fs-1 mb-0 font-weight-bold">PREDATOR:</p>
            <p class="fs-1 mb-0 text-justify">
                <?php if ($handingCar == 0) { ?>
                    <span class="font-weight-bold"><?php echo $model['company']['name'] ?></span>
                    , cu sediul social în <?php echo $model['company']['address'] ?>
                    , înregistrată la Registrul Comerțului sub numărul <?php echo $model['company']['reg_number'] ?>
                    , CUI <?php echo $model['company']['cui'] ?>
                    , reprezentată de Administratorul <?php echo $companyLegalAdminName[$model['company_id']] ?>.
                <?php } else { ?>
                    <span style='font-weight: bold;'><?php echo $user->fullName(); ?></span>
                <?php } ?>
            </p>
            <p class="fs-1 font-weight-bold mb-0">și</p>
            <p class="fs-1 font-weight-bold mb-0" style='padding-top:5px;'>
                PRIMITOR:</p>
            <p class="fs-1 text-justify mb-0">
                <?php if ($handingCar == 0) { ?>
                    <span class="font-weight-bold"><?php echo $user->fullName(); ?></span>
                <?php } else { ?>
                    <span class="font-weight-bold"><?php echo $model['company']['name'] ?></span>
                    , cu sediul social în <?php echo $model['company']['address'] ?>
                    , înregistrată la Registrul Comerțului sub numărul <?php echo $model['company']['reg_number'] ?>
                    , CUI <?php echo $model['company']['cui'] ?>
                    , reprezentată de Administratorul <?php echo $companyLegalAdminName[$model['company_id']] ?>.
                <?php } ?>
            </p>
            <p class="fs-1 pt-1 mb-0">
                <span class="font-weight-bold"> AUTOVEHICUL: </span> Numărul de înmatriculare
                <span class="font-weight-bold"><?php echo $model['plate_number'] ?>, </span>
                categoria
                <span class="font-weight-bold">AUTOTURISM M1 </span> marca
                <span class="font-weight-bold"><?php echo $model['brand']['name'] ?> </span>
                tipul
                <span class="font-weight-bold"><?php echo $model['brandModel']['name'] ?></span>.
            </p>
            <p class="fs-1 pt-1 mb-0">
                Numărul
                de km la bord <span class="font-weight-bold">------</span> km.</p>
            <p class="fs-1 pt-1">
                Împreună
                cu autovehiculul s-au predat și următoarele documente și accesorii: </p>
            <p class="fs-1 font-weight-bold mb-0" style='padding-top:5px;'>
                Documente:
            </p>
            <table style='width:95%; font-size: 12px; border: 1px solid black; border-collapse: collapse; vertical-align: 25px;'>
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
    </div>

    <div class="card" style="max-width: 380px">
        <div class="bg-image company_<?php echo $model->company_id; ?>">
            <p class="for-preview text-center font-weight-bold text-left" style='padding-top:5px;'>Starea de
                funcționare a mașinii</p>
            <p class="fs-1 mb-0 font-weight-bold mt-1 accessories">Accesorii:</p>
            <table style='width:95%; font-size: 12px; border: 1px solid black; border-collapse: collapse; vertical-align: 25px;'>
                <tr>
                    <th class="td-column-content">Denumire accesoriu</th>
                    <th class="column-header">Cantitate</th>
                    <th class="td-column-content">Observții</th>
                </tr>
                <?php
                $itemCount = 1;
                if (!empty($accessories)) {
                    foreach ($accessories as $item) {
                        if (!empty($updatedAccessories)) {
                            foreach ($updatedAccessories as $accessory) {
                                if ($accessory['id'] === $item['id']) { ?>
                                    <tr>
                                        <td class="td-column-content w-auto"><?php echo $accessory['name']; ?></td>
                                        <td class="column-header w-auto"><?php echo(!empty($accessory['count']) ? $accessory['count'] : 1) ?></td>
                                        <td class="td-column-content w-auto"><?php echo !empty($accessory['observation']) ? $accessory['observation'] : ' '; ?></td>
                                    </tr>
                                    <?php
                                    $item['name'] = null;
                                    $item['count'] = null;
                                    $item['observation'] = null;
                                }
                            } ?>
                            <tr>
                                <td class="td-column-content w-auto" style="border-bottom: none; border-top: none"><?php echo $item['name']; ?></td>
                                <td class="column-header w-auto" style="border-bottom: none; border-top: none"><?php echo(!empty($item['count']) ? $item['count'] : '') ?></td>
                                <td class="td-column-content w-auto"
                                    style="border-bottom: none; border-top: none"><?php echo !empty($item['observation']) ? $item['observation'] : ' '; ?></td>
                            </tr>
                        <?php } else {
                            ?>
                            <tr>
                                <td class="td-column-content w-auto" style="border-top: none"><?php echo $item['name']; ?></td>
                                <td class="column-header w-auto" style="border-top: none"><?php echo(!empty($item['count']) ? $item['count'] : 1) ?></td>
                                <td class="td-column-content w-auto" style="border-top: none"><?php echo !empty($item['observation']) ? $item['observation'] : ' '; ?></td>
                            </tr>
                            <?php
                            $itemCount++;
                        }
                    }
                }
                ?>
            </table>
            <p class="fs-1 pt-1">
                Primitorul a preluat autovehiculul în dotare cu cele amintite mai sus, în urma unui inventar</p>
            <p class="fs-1 mb-0 font-weight-bold" style='padding-top:5px;'>Stare de
                funcționare:</p>
            <table style='width:95%; font-size: 12px; border: 1px solid black; border-collapse: collapse; vertical-align: 25px;'>
                <?php
                foreach ($zoneModel as $status) {
                    if ($status['field'] == 'general_observations') {
                        break;
                    }
                    if (!empty($adjustedZoneModels)) {
                        foreach ($adjustedZoneModels as $zone) {
                            if ($zone['zone_id'] === $status['carZone']['zone_id'] && $status['carZone']['zoneOption']['text'] && $status['label']) { ?>
                                <tr>
                                    <th class="td-column-content p-0"
                                        style="border-bottom: none; border-top: none"><?php echo $status['label'] ?></th>
                                    <td class="td-column-content p-0"
                                        style="border-bottom: none; border-top: none"><?php echo (!empty($zone['text'])) ? $zone['text'] : '' ?></td>
                                </tr>
                                <?php
                                $status['label'] = null;
                                $status['carZone']['zoneOption']['text'] = null;
                            }
                        } ?>
                        <tr>
                            <th class="td-column-content"
                                style="border-top: none;"><?php echo $status['label'] ?></th>
                            <td class="td-column-content"
                                style="border-top: none;"><?php echo $status['carZone']['zoneOption']['text'] ?></td>
                        </tr>
                    <?php } else { ?>
                        <tr>
                            <th class="td-column-content"><?php echo $status['label'] ?></th>
                            <td class="td-column-content"><?php echo $status['carZone']['zoneOption']['text'] ?></td>
                        </tr>
                    <?php }
                } ?>
            </table>
            <?php
            $displayObservations = 'block;';
            if (empty($zoneModel[7]['carZone']['observations'])) {
                $displayObservations = 'none;';
            }
            ?>
            <p class="fs-1 mb-0" style='display:<?php echo $displayObservations ?> '>
                Observații:
            <p class="fs-1 mb-0" style='display:<?php echo $displayObservations ?> '>
                <?php echo !empty($zoneModel[7]['carZone']['observations']) ? $zoneModel[7]['carZone']['observations'] : ''; ?>
            </p>
            <p class="pt-1 fs-1 mb-0">
                Prezentul formular a fost încheiat azi, <?php echo $date ?> într-un nr. de 2 exemplare, din care 1
                ramâne la predator și 1 la primitor.
            </p>
        </div>
    </div>

<?php
//identify if any zone has photos
$hasZoneImages = false;
foreach ($zoneModel as $zone) {
    if (array_key_exists($zone['id'], $postZoneOption) && !empty($postZoneOption[$zone['id']]['zone_photo'])) {
        $hasZoneImages = true;
        break;
    }
}
?>
<?php if ($hasZoneImages) { ?>
    <div class="card" style="width: 380px; max-width: 380px">
        <div class="bg-image pb-5 company_<?php echo $model->company_id; ?>" style="padding-top: 70px;">
            <p class="mt-1 font-weight-bold text-center">
                Imagini stare de funcționare
            </p>
            <?php
            foreach ($zoneModel as $carZone) {
                if (!empty($postZoneOption[$carZone['id']]) && !empty($postZoneOption[$carZone['id']]['zone_photo'])) {
                    ?>
                    <div class="column-image-align">
                        <span class="text-image-align"><?php echo substr($carZone['label'], 0, 11); ?></span>
                        <img src='<?php echo $postZoneOption[$carZone['id']]['zone_photo']; ?>'
                             alt="<?php echo $carZone['label']; ?>"
                             class="align-image">
                    </div>
                <?php }
            } ?>
        </div>
    </div>
<?php } ?>