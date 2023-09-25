<?php
/** @var $car */
/** @var $employee */
/** @var $backgroundImage */
/** @var $styleEmpowering */
/** @var $regNumber */

use api\models\Company;
use backend\modules\adm\models\User;

?>

<style>
    @page {
        background-image: url(<?php echo $backgroundImage ?>);
        background-image-resize: 6;
        margin-top: 120px;
        margin-bottom: 100px;
    }
</style>

<div class="bg-image" style='width: 95%; height: 90%;   display: inline-block;overflow: hidden; position: relative;'>

    <h2 class="for-preview"
        style='text-align: right; font-size: 15px; font-weight: normal; padding-right: 10px; color: <?php echo $styleEmpowering[0] ?>'>
        Nr.înreg. <span style="color: black"><?php echo $regNumber; ?></span> din <span
                style="color: black"><?php echo date('d-m-Y') ?></span>
    </h2>

    <h2 style='text-align: center; font-size: 20px; font-weight: bold; padding-top: 10px;'>Împuternicire</h2>

    <h2 style='text-align: left; font-size: 15px; font-weight: normal; padding-left: 75px;'>
        Prin prezenta societatea <span style='font-weight: bold;'><?php echo $car['company']['name'] ?></span>,
        CUI <?php echo $car['company']['cui'] ?>, <?php echo $car['company']['reg_number'] ?>, cu sediul în <?php echo $car['company']['address'] ?>,
        împuternicește pe <span style='font-weight: bold;'><?php echo $employee->fullName() ?></span>, care se legitimează cu CI seria <?php echo $employee['identity_card_series'] ?> nr. <?php echo $employee['identity_card_number'] ?>,
        eliberat de - , la data de - să circule pe teritoriul României cu autoturismul marca
        <?php echo $car['brand']['name'] ?> <?php echo $car['brandModel']['name'] ?> cu nr. de indetificare <?php echo $car['vin'] ?>.
    </h2>

    <table style='width:100%; margin-top: 50px;'>
        <tr>
            <th style='width: 150px; padding-left: 75px; text-align: left;'>ADMINISTRATOR,</th>
        </tr>
        <tr>
            <th style='text-align: left; font-weight: bold; vertical-align: top; padding-left: 75px'>
                <?php if (strpos(Company::companyLegalAdminName($car['company_id'], true), 'base64') !== false) { ?>
                    <img src="<?php echo Company::companyLegalAdminName($car['company_id'], true); ?>" alt="" width=120 height=120>
                <?php } else {
                    echo Company::companyLegalAdminName($car['company_id']);
                } ?>
            </th>
        </tr>
    </table>
</div>


