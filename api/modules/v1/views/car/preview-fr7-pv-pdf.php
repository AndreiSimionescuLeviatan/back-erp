<?php

use api\models\Car;
use api\models\Company;
use api\models\User;
use backend\modules\hr\models\Employee;

/** @var Car $model */
/** @var User $user */
/** @var $zoneModel */
/** @var $adjustedZoneModels */
/** @var $backgroundImage */
/** @var $date */
/** @var $stylePdf */
/** @var $stylePdf */
/** @var $postZoneOption */
/** @var $footer */
/** @var $handingCar */
/** @var $displayPage */
/** @var $accessories */
/** @var $post */
/** @var $updatedAccessories */
/** @var $carKm */
/** @var $regNumber */

/**
 * @todo Move to params or other place not to repeat
 */

$documents = [
    'Taxa de drum: Rovinieta' => 'vignette_valid_until',
    'Certificat de inspecție tehnica ITP' => 'itp_valid_until',
    'Polița de asigurare RCA' => 'rca_valid_until',
    'Polița de asigurare CASCO' => 'casco_valid_until',
];

$genderIdentification = '';
$employee = Employee::find()->where("user_id = {$user->id}")->one();
if (!empty($employee)) {
    if ($employee->gender == 0) {
        $genderIdentification = 'identificat/ă ';
    } elseif ($employee->gender == 1) {
        $genderIdentification = 'identificat ';
    } else {
        $genderIdentification = 'identificată ';
    }
}

?>

    <style>
        img {
            height: 90px;
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

        .signatures-container th {
            padding-left: <?php echo $handingCar === 'check_in' ? '0;' : '75px;'?>;
        }

        .fs-1 {
            font-size: 1px;
        }

        .font-weight-bold {
            font-weight: bold;
        }

        .fr7-table {
            border-collapse: collapse;
            font-size: 10px;
        }

        .pdf-page {
            background-repeat: no-repeat;
            background-size: 24rem;
            background-position: center;
            height: 32rem;
        }

        .min-height25 {
            min-height: 25px!important;
        }
    </style>

    <div class="card pv-card">
        <div class="pdf-page card-content card-content-padding">
            <div class="card-header"></div>
            <p class="fs-1 float-right">
                <span class="nr-reg">Nr.înreg. </span>
                <span class="text-black"><?php echo $regNumber; ?></span>
                <span class="nr-reg">din</span>
                <span class="text-black"><?php echo $date ?></span>
            </p>
            <br/>
            <p class="font-weight-bold text-align-center">
                Proces verbal de predare - primire
            </p>
            <p class="fs-1 no-margin-bottom">
                Nr. inmatriculare autovehicul
                <span class="font-weight-bold"><?php echo $model['plate_number'] ?></span>
            </p>
            <p class="fs-1 no-margin-bottom">
                Locul predării:
                <span><?php echo $model['company']['address'] ?></span>
            </p>
            <p class="fs-1 no-margin-bottom font-weight-bold">
                PREDATOR:
            </p>
            <p class="fs-1 no-margin-bottom text-justify" style="padding-left: 25px">
                <?php if ($handingCar == 'check_in') { ?>
                    <span class="font-weight-bold"><?php echo $model['company']['name'] ?></span>
                    , cu sediul social în <?php echo $model['company']['address'] ?>
                    , înregistrată la Registrul Comerțului sub numărul <?php echo $model['company']['reg_number'] ?>
                    , CUI <?php echo $model['company']['cui'] ?>
                    , reprezentată de Administratorul <?php echo Company::companyLegalAdminName($model['company']['id']); ?>.
                <?php } else { ?>
                    <span class="font-weight-bold"><?php
                        echo $user->fullName() . ', ' . $genderIdentification .
                            'prin CI, seria ' . $employee->identity_card_series .
                            ', numărul ' . $employee->identity_card_number . '.'; ?></span>
                <?php } ?>
            </p>
            <p class="fs-1 font-weight-bold no-margin-bottom">
                și
            </p>
            <p class="fs-1 font-weight-bold no-margin-bottom">
                PRIMITOR:
            </p>
            <p class="fs-1 text-justify no-margin-bottom" style="padding-left: 25px">
                <?php if ($handingCar == 'check_in') { ?>
                    <span class="font-weight-bold"><?php echo $user->fullName() . ', ' . $genderIdentification .
                            'prin CI, seria ' . $employee->identity_card_series .
                            ', numărul ' . $employee->identity_card_number . '.'; ?>
                    </span>
                <?php } else { ?>
                    <span class="font-weight-bold"><?php echo $model['company']['name'] ?></span>
                    , cu sediul social în <?php echo $model['company']['address'] ?>
                    , înregistrată la Registrul Comerțului sub numărul <?php echo $model['company']['reg_number'] ?>
                    , CUI <?php echo $model['company']['cui'] ?>
                    , reprezentată de Administratorul <?php echo Company::companyLegalAdminName($model['company']['id']); ?>.
                <?php } ?>
            </p>
            <p class="fs-1 pt-1 no-margin-bottom">
                <span class="font-weight-bold"> AUTOVEHICUL: </span> Numărul de înmatriculare
                <span class="font-weight-bold"><?php echo $model['plate_number'] ?>, </span>
                categoria
                <span class="font-weight-bold">AUTOTURISM M1 </span> marca
                <span class="font-weight-bold"><?php echo $model['brand']['name'] ?> </span>
                tipul
                <span class="font-weight-bold"><?php echo $model['brandModel']['name'] ?></span>.
            </p>
            <p class="fs-1 pt-1 no-margin-bottom">
                Numărul de km la bord
                <span class="font-weight-bold"><?php echo $carKm ?></span>
                km.
            </p>
            <p class="fs-1 pt-1">
                Împreună cu autovehiculul s-au predat și următoarele documente și accesorii:
            </p>
            <p class="fs-1 font-weight-bold">
                Documente:
            </p>
            <table class="fr7-table width-100">
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

    <div class="card pv-card">
        <div class="pdf-page card-content card-content-padding">
            <div class="card-header <?php echo count($accessories) > 11 ? 'min-height25' : ''; ?>"></div>
            <p class="fs-1 font-weight-bold m-0">
                Accesorii:
            </p>
            <table class="fr7-table width-100">
                <tr>
                    <th class="td-column-content">Denumire accesoriu</th>
                    <th class="column-header">Cantitate</th>
                    <th class="td-column-content">Observații</th>
                </tr>
                <?php
                if (!empty($accessories)) {
                    foreach ($accessories as $accessory) {
                        ?>
                        <tr>
                            <td class="td-column-content w-auto"><?php echo $accessory['name']; ?></td>
                            <td class="column-header w-auto"><?php echo isset($post['accessories'][$accessory['id']]) ? $post['accessories'][$accessory['id']]['quantity'] : $accessory['count'] ?></td>
                            <td class="td-column-content w-auto"><?php
                                if (isset($post['accessories'][$accessory['id']]['observation'])) {
                                    echo $post['accessories'][$accessory['id']]['observation'];
                                } else if (!empty($accessory['observations'])) {
                                    echo $accessory['observations'];
                                } else {
                                    echo ' ';
                                } ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
            <p class="fs-1 pt-1">
                Primitorul a preluat autovehiculul în dotare cu cele amintite mai sus, în urma unui inventar
            </p>
            <p class="fs-1 font-weight-bold">
                Stare de funcționare:
            </p>
            <table class="fr7-table width-100">
                <tr>
                    <th class="td-column-content">Denumire element</th>
                    <th class="column-header">Stare de functionare</th>
                </tr>
                <?php
                foreach ($zoneModel as $status) {
                    if ($status['field'] == 'general_observations') {
                        continue;
                    }
                    ?>
                    <tr>
                        <th class="td-column-content"><?php echo $status['label'] ?></th>
                        <td class="td-column-content">
                            <?php echo isset($post['car_zone'][$status['id']]) ? $post['car_zone'][$status['id']]['text'] : $status['carZone']['zoneOption']['text']; ?></td>
                    </tr>
                <?php } ?>
            </table>
            <?php
            $displayObservations = 'none;';
            if (isset($post['car_zone'][8]['observations']) && !empty($post['car_zone'][8]['observations'])) { //check if observations from post are set and not empty
                $displayObservations = 'block;';
                $observation = $post['car_zone'][8]['observations'];
            } elseif (isset($post['car_zone'][8]['observations'])) { //check if the observations from post are empty
                $observation = '';
            } elseif (!empty($zoneModel[7]['carZone']['observations'])) { //check if the observations from db are not empty
                $displayObservations = 'block;';
                $observation = $zoneModel[7]['carZone']['observations'];
            } else {
                $observation = '';
            }
            ?>
            <p class="fs-1 no-margin-bottom" style='display:
            <?php echo $displayObservations ?> '>
                Observații:
            <p class="fs-1 no-margin-bottom" style='display:
            <?php echo $displayObservations ?> '>
                <?php echo $observation ?>

            </p>
            <p class="pt-1 fs-1 margin-bottom">
                Prezentul formular a fost încheiat azi, <?php echo $date ?> într-un nr. de 2 exemplare, din care 1
                ramâne la predator și 1 la primitor.
            </p>
        </div>
    </div>

<?php
$hasZoneImages = false;
$nrPhotos = 0;
$photos = [];
foreach ($zoneModel as $carZone) {
    if (!empty($postZoneOption[$carZone['id']]) && !empty($postZoneOption[$carZone['id']]['zone_photo'])) {
        foreach ($postZoneOption[$carZone['id']]['zone_photo'] as $photo) {
            if ($photo != '') {
                $nrPhotos++;
                $photos[] = [
                    'photo' => $photo,
                    'zone' => $carZone['label']
                ];
                $hasZoneImages = true;
            }
        }
    }
}
?>
<?php if ($hasZoneImages) {
    $nrPhotos / 9 > 1 ? $nrPages = floor($nrPhotos / 9) + 1 : $nrPages = 1;
    $photoPerPage = 0;
    $currentPhoto = 0;
    for ($i = 0; $i < $nrPages; $i++) {
        $photoPerPage + 9 >= $nrPhotos ? $photoPerPage = $nrPhotos : $photoPerPage += 9;
        ?>
        <div class="card width-100 pv-card">
            <div class="pdf-page card-content card-content-padding">
                <div class="card-header"></div>
                <p class="font-weight-bold text-align-center">
                    Imagini stare de funcționare
                </p>
                <?php
                for ($j = $currentPhoto; $j < $photoPerPage; $j++) { ?>
                        <div class="column-image-align">
                                    <span class="text-image-align">
                                        <?php echo substr($photos[$j]['zone'], 0, 11); ?>
                                    </span>
                            <img src='<?php echo $photos[$j]['photo']; ?>'
                                 alt="<?php echo $photos[$j]['zone']; ?>"
                                 class="align-image">
                        </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php $currentPhoto += 9;
    }
} ?>