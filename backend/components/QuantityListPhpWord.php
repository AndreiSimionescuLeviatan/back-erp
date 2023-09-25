<?php

namespace backend\components;

use backend\modules\build\models\EquipmentQuantity;
use backend\modules\build\models\QuantityList;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use Yii;
use yii\db\Exception;

class QuantityListPhpWord extends PhpWord
{
    public $fontStyleWithBold12 = [];
    public $fontStyleWithoutBold12 = [];
    public $fontStyleWithBoldSize14 = [];
    public $fontStyleWithoutBoldSize14 = [];
    public $fontStyleWithBold20 = [];
    public $fontStyleWithUnderLine = [];
    public $fontStyleWithUnderLineWithBold = [];
    public $fontStyleWithBoldArial12 = [];
    public $fontStyleCalibriLight16 = [];
    public $fancyTableStyle = [];
    public $cellRowSpan = [];
    public $textAlignCentered = [];
    public $textAlignJustify = [];
    public $textAlignLeft = [];
    public $cell1 = null;
    public $cell2 = null;
    public $cell3 = null;
    public $cell4 = null;
    public $section = null;
    public $tableHead = null;
    public $tableNr = null;
    public $compliance = null;
    public $designAndBuild = '';
    public $fileName = '';
    public $nrCrtArchitecture = 0;
    public $nrCrtArchitectureLink = 0;


    /**
     * seteaza fonturile textului din document
     */
    public function setDocFonts()
    {
        $this->fontStyleWithBold12 = array('name' => 'Times New Roman', 'size' => 12, 'bold' => true);
        $this->fontStyleWithoutBold12 = array('name' => 'Times New Roman', 'size' => 12, 'bold' => false);
        $this->fontStyleWithBoldSize14 = array('name' => 'Times New Roman', 'size' => 14, 'bold' => true);
        $this->fontStyleWithoutBoldSize14 = array('name' => 'Times New Roman', 'size' => 14, 'bold' => false);
        $this->fontStyleWithBold20 = array('name' => 'Times New Roman', 'size' => 20, 'bold' => true);
        $this->fontStyleWithUnderLine = array('name' => 'Times New Roman', 'size' => 12, 'underline' => 'single');
        $this->fontStyleWithUnderLineWithBold = array('name' => 'Times New Roman', 'size' => 12, 'underline' => 'single', 'bold' => true);
        $this->fontStyleWithBoldArial12 = array('name' => 'Arial', 'size' => 12, 'bold' => true);
        $this->fontStyleCalibriLight16 = array('name' => 'Calibri Light', 'size' => 16, 'bold' => false, 'color' => '#2F5496');
    }

    /**
     * seteaza stilul tabelului
     */
    public function setDocTableStyle()
    {
        $this->fancyTableStyle = array('borderSize' => 6, 'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,);
        $this->cellRowSpan = array('vMerge' => 'restart', 'valign' => 'top');
        $this->textAlignCentered = array('alignment' => Jc::CENTER);
        $this->textAlignJustify = array('alignment' => Jc::BOTH);
        $this->textAlignLeft = array('alignment' => Jc::START);
    }

    /**
     * seteaza headingurile documentului
     */
    public function setDocHeadings()
    {
        $this->addNumberingStyle(
            'hNum',
            array('type' => 'multilevel', 'levels' => array(
                array('pStyle' => 'Heading1', 'format' => 'decimal', 'text' => '%1'),
                array('pStyle' => 'Heading2', 'format' => 'decimal', 'text' => '%1.%2')
            )
            )
        );
        $this->addTitleStyle(1, array('name' => 'Times New Roman', 'size' => 14, 'bold' => true), $this->textAlignCentered);
        $this->addTitleStyle(2, array('name' => 'Times New Roman', 'size' => 14), $this->textAlignLeft);
    }

    /**
     * seteaza imaginea de header a documentului
     */
    public function setDocImageHeader()
    {
        $this->section = $this->addSection();
        $header = $this->section->addHeader();
        $table = $header->addTable();
        $table->addRow();
        $table->addCell(4500)->addImage(Yii::getAlias('@backend') . '/web/images/leviatan_docx_header.jpg',
            array(
                'width' => 450,
                'height' => 70,
                'marginTop' => round(Converter::cmToPixel(-0.5)),
                'alignment' => Jc::END
            )
        );
    }

    /**
     * @param $equipmentQuantityModel
     * @throws Exception
     * verific daca modelul cu lista de cantitati are date in EquipmentQuantity
     */
    public function validateData($equipmentQuantityModel)
    {
        if (empty($equipmentQuantityModel)) {
            throw new Exception(Yii::t('app', 'You have no data entered in this quantity list'));
        }
    }

    /**
     * set file name
     */
    public function setFileName($equipmentQuantityModel)
    {
        $this->fileName = "Fise-Tehnice-" . date('Y_m_d_H_i_s') . ".docx";

        if (!empty($equipmentQuantityModel->project) && !empty($equipmentQuantityModel->object) && !empty($equipmentQuantityModel->speciality)) {
            $this->fileName = "Fise-Tehnice_{$equipmentQuantityModel->project->code}_{$equipmentQuantityModel->object->code}_{$equipmentQuantityModel->speciality->name}_" . date('Y-m-d-H-i-s') . '.docx';
        }
    }

    /**
     * @throws Exception
     * valideaza datele de intrare
     */
    public function validateDataInput($equipmentQuantityModel)
    {
        if ($equipmentQuantityModel->speciality === null) {
            throw new Exception(Yii::t('app', 'The specialty field is empty'));
        }
        if ($equipmentQuantityModel->project === null) {
            throw new Exception(Yii::t('app', 'The project field is empty'));
        }
        if ($equipmentQuantityModel->building === null) {
            throw new Exception(Yii::t('app', 'The building field is empty'));
        }
        if ($equipmentQuantityModel->equipment === null) {
            throw new Exception(Yii::t('app', 'The equipment field is empty'));
        }
        if ($equipmentQuantityModel->equipmentType === null) {
            throw new Exception(Yii::t('app', 'The equipment type field is empty'));
        }
    }

    /**
     * this function return object full name or name
     * @param $equipmentQuantityModel
     * @return string
     * Added htmlspecialchars to solve some export problems that appears when the text contains some characters like `<`
     */
    public function setObjectName($equipmentQuantityModel)
    {
        $objectName = '';
        if (!empty($equipmentQuantityModel->building)) {
            $objectName = $equipmentQuantityModel->building->name;
            if ($equipmentQuantityModel->building->full_name !== '' && $equipmentQuantityModel->building->full_name !== '-' && $equipmentQuantityModel->building->full_name !== null) {
                $objectName = $equipmentQuantityModel->building->full_name;
            }
        }

        return htmlspecialchars($objectName);
    }

    /**
     * set if is design&build type or auction
     * @param $quantityListType
     * @return void
     */
    public function setQtyListType($quantityListType)
    {
        $this->designAndBuild = 'auction';
        if ((int)$quantityListType === 2) {
            $this->designAndBuild = 'design_build';
        }
    }

    /**
     * @param $equipmentQuantityModel
     * seteaza pagina de inceput a documentului
     * add object name
     * Added htmlspecialchars to solve some export problems that appears when the text contains some characters like `<`
     */
    public function setDocCover($equipmentQuantityModel)
    {
        if ($equipmentQuantityModel->project->full_name != '-')
            $projectName = $equipmentQuantityModel->project->full_name;
        else
            $projectName = $equipmentQuantityModel->project->name;

        $this->section->addTextBreak(2);
        $this->section->addText(htmlspecialchars($projectName), $this->fontStyleWithBold20, $this->textAlignCentered);
        $this->section->addTextBreak(6);

        $qtyListPhase = '-';
        if (!empty($equipmentQuantityModel->quantityList))
            $qtyListPhase = QuantityList::getQuantityListTypeName($equipmentQuantityModel->quantityList->quantity_list_type);

        $this->section->addText(htmlspecialchars("FAZA: {$qtyListPhase}"), $this->fontStyleWithBold12, $this->textAlignCentered);
        $this->section->addTextBreak(2);

        $this->section->addText("COD PROIECT: ", $this->fontStyleWithBold12, $this->textAlignCentered);
        $this->section->addText(htmlspecialchars("{$equipmentQuantityModel->project->code}"), $this->fontStyleWithBold12, $this->textAlignCentered);
        $this->section->addTextBreak(3);

        $this->section->addText('FIȘE TEHNICE ', $this->fontStyleWithoutBoldSize14, $this->textAlignCentered);
        $this->section->addTextBreak(5);

        $typeName = $equipmentQuantityModel->equipmentType->name;
        if ($equipmentQuantityModel->speciality->code == Yii::$app->params['specialityCodeList']['arhitectura']) {
            if ($equipmentQuantityModel->equipment_type_id == 1) {
                $typeName = 'Echipamente';
            } else {
                $typeName = 'Dotari';
            }
        }
        $this->section->addText(
            htmlspecialchars("SPECIALITATEA: {$equipmentQuantityModel->speciality->name} - {$typeName}"),
            $this->fontStyleWithBoldSize14,
            $this->textAlignCentered
        );

        $this->section->addText(
            "OBIECT: {$this->setObjectName($equipmentQuantityModel)}",
            $this->fontStyleWithBoldSize14,
            $this->textAlignCentered
        );

        $this->addNewPage();

    }

    /**
     * @param EquipmentQuantity $equipmentQuantityModel
     * seteza diferite informatii in partea de sus a pagini de cuprins
     * Added htmlspecialchars to solve some export problems that appears when the text contains some characters like `<`
     */
    public function setDocQuickLinksTopInfo(EquipmentQuantity $equipmentQuantityModel)
    {
        if ($equipmentQuantityModel->project->full_name != '-')
            $projectName = $equipmentQuantityModel->project->full_name;
        else
            $projectName = $equipmentQuantityModel->project->name;

        $this->section->addText('FORMULARUL F5', $this->fontStyleWithBold12);
        $this->section->addTextBreak(1);

        $p1 = $this->section->addTextRun([
            'spaceAfter' => Converter::pointToTwip(0),
            'spacing' => 140,
            'lineHeight' => 1.1,
        ]);
        $p1->addText(htmlspecialchars("{$equipmentQuantityModel->speciality->name} - {$equipmentQuantityModel->equipmentType->name}"), $this->fontStyleWithBold12);
        $p1->addTextBreak();
        $p1->addText('Denumire proiect: ', $this->fontStyleWithoutBold12);
        $p1->addText(htmlspecialchars($projectName), $this->fontStyleWithBold12);
        $p1->addTextBreak();
        $p1->addText("Cod proiect: ", $this->fontStyleWithoutBold12);
        $p1->addText(htmlspecialchars("{$equipmentQuantityModel->project->code}"), $this->fontStyleWithBold12);
        $p1->addTextBreak();
        $p1->addText('OBIECT: ', $this->fontStyleWithoutBold12);
        $p1->addText($this->setObjectName($equipmentQuantityModel), $this->fontStyleWithBold12);

        $this->section->addTextBreak(2);
    }

    /**
     * @param EquipmentQuantity[] $equipmentQuantityModel
     * displays the list of data sheets in the document
     * @return void
     * Added htmlspecialchars to solve some export problems that appears when the text contains some characters like `<`
     */

    public function setDocQuickLinks($equipmentQuantityModel, $nrCrtList)
    {

        $this->section->addText('FIȘE TEHNICE', $this->fontStyleWithBold12);
        $this->nrCrtArchitecture = 0;
        $dataSheetList = $this->section->addTextRun([
            'spaceAfter' => Converter::pointToTwip(0),
            'spacing' => 140,
            'lineHeight' => 1,
        ]);
        foreach ($equipmentQuantityModel as $model) {
            $nrCrt = $nrCrtList["{$model->source}_{$model->equipment_type_id}"][$model->id];
            $dataSheetList->addText("FIȘĂ TEHNICĂ NR. {$nrCrt} - ", $this->fontStyleWithoutBold12);
            $dataSheetList->addText(htmlspecialchars($model->equipment->short_name), $this->fontStyleWithBold12);
            $dataSheetList->addTextBreak();
        }
    }

    /**
     * @param $nrCrt
     * @param $equipmentQuantityModel
     * seteaza "headul" fisei tehnice
     * Added htmlspecialchars to solve some export problems that appears when the text contains some characters like `<`
     */
    public function setDataSheetHeader($nrCrt, $equipmentQuantityModel)
    {
        if ($equipmentQuantityModel->project->full_name != '-')
            $projectName = $equipmentQuantityModel->project->full_name;
        else
            $projectName = $equipmentQuantityModel->project->name;


        $p1 = $this->section->addTextRun([
            'spaceAfter' => Converter::pointToTwip(0),
            'spacing' => 140,
            'lineHeight' => 1.1,
        ]);

        $name = "{$equipmentQuantityModel->speciality->name} - {$equipmentQuantityModel->equipmentType->name}";
        if ($equipmentQuantityModel->speciality->code == Yii::$app->params['specialityCodeList']['arhitectura']) {
            if ($equipmentQuantityModel->equipment_type_id == 1) {
                $name = "{$equipmentQuantityModel->speciality->name} - Echipamente";
            } else {
                $name = "{$equipmentQuantityModel->speciality->name} - Dotari";
            }
        }

        $p1->addText('FORMULARUL F5', $this->fontStyleWithBold12);
        $p1->addTextBreak();
        $p1->addText(htmlspecialchars("{$name}"), $this->fontStyleWithUnderLineWithBold);
        $p1->addTextBreak();
        $p1->addText('Denumire proiect: ', $this->fontStyleWithBold12);
        $p1->addText(htmlspecialchars($projectName), $this->fontStyleWithoutBold12);
        $p1->addTextBreak();
        $p1->addText('OBIECT: ', $this->fontStyleWithoutBold12);
        $p1->addText($this->setObjectName($equipmentQuantityModel), $this->fontStyleWithoutBold12);
        $this->section->addTextBreak(2);

        $this->section->addTitle("FIȘĂ TEHNICĂ NR. {$nrCrt}", 1);
        $p2 = $this->section->addTextRun([
            'spaceAfter' => Converter::pointToTwip(0),
            'spacing' => 140,
            'lineHeight' => 1.1,
        ]);
        $p2->addText('Utilajul, echipamentul tehnologic', $this->fontStyleWithoutBold12);
        $this->section->addTitle($equipmentQuantityModel->equipment->short_name, 2);
    }

    /**
     * @param $table
     * adauga celule pentru tabel
     */
    public function addTableCells($table)
    {
        $table->addRow();
        $this->cell1 = $table->addCell(700, $this->cellRowSpan);
        $this->cell2 = $table->addCell(3400, $this->cellRowSpan);
        $this->cell3 = $table->addCell(3400, $this->cellRowSpan);
        $this->cell4 = $table->addCell(1500, $this->cellRowSpan);
    }

    /**
     * seteaza headul tabelului
     */
    public function setTableHead()
    {
        $this->tableHead = $this->cell1->addTextRun($this->textAlignCentered);
        $this->tableHead->addText('Nr . crt . ', $this->fontStyleWithBold12);
        $this->tableHead = $this->cell2->addTextRun($this->textAlignCentered);
        $this->tableHead->addText('Specificaţii tehnice impuse prin Caietul de sarcini', $this->fontStyleWithBold12);
        $this->tableHead = $this->cell3->addTextRun($this->textAlignCentered);
        $this->tableHead->addText('Corespondenţa propunerii tehnice cu specificaţiile tehnice impuse prin Caietul de sarcini', $this->fontStyleWithBold12);
        $this->tableHead = $this->cell4->addTextRun($this->textAlignCentered);
        $this->tableHead->addText('Furnizor', $this->fontStyleWithBold12);

    }

    /**
     * seteaza randul cu Nr.Crt al tabelului
     */
    public function setTableHeadNr()
    {
        $this->tableNr = $this->cell1->addTextRun($this->textAlignCentered);
        $this->tableNr->addText('0', $this->fontStyleWithBold12);
        $this->tableNr = $this->cell2->addTextRun($this->textAlignCentered);
        $this->tableNr->addText('1', $this->fontStyleWithBold12);
        $this->tableNr = $this->cell3->addTextRun($this->textAlignCentered);
        $this->tableNr->addText('2', $this->fontStyleWithBold12);
        $this->tableNr = $this->cell4->addTextRun($this->textAlignCentered);
        $this->tableNr->addText('3', $this->fontStyleWithBold12);
    }

    /**
     * return name of the brand/provider if is auction type
     * @return string
     * Added htmlspecialchars to solve some export problems that appears when the text contains some characters like `<`
     */
    public function setProvider()
    {
        if ($this->designAndBuild === 'auction') {
            if (!empty($model->equipment->brand) && !empty($model->equipment->brand->name)) {
                return htmlspecialchars($model->equipment->brand->name);
            }
        }
        return '';
    }

    /**
     * set text with enters spaces
     * @return void
     */
    public function setWrapText($string, $cell)
    {
        $count = 0;
        foreach (explode("\n", $string) as $word) {
            $count++;
            if (!empty($word)) {
                $word = str_replace('•', '-', $word);
                $cell->addText(preg_replace(array('/\t/'), ' ', $word), $this->fontStyleWithoutBold12);
                if ($count !== count(explode("\n", $string))) {
                    $cell->addTextBreak();
                }
            }
        }
    }

    /**
     * @param EquipmentQuantity $model
     * adauga randul cu parametri tehnici al tabelului
     * Display technical params on proposed column when qty list type is 1 "licitație"
     * Added htmlspecialchars to solve some export problems that appears when the text contains some characters like `<`
     */
    public function addTableTechnicalParamRow($model)
    {
        $specificDetails = EquipmentQuantity::getSpecificDetails(['equipmentId' => $model->equipment->id, 'equipmentQuantityId' => $model->id]);
        $techParams = htmlspecialchars($specificDetails['technical_parameters']);
        $proposedTechParams = '';
        if (!empty($model->quantityList) && !empty($model->quantityList->quantity_list_type) && $model->quantityList->quantity_list_type == 1)
            $proposedTechParams = $techParams;

        $techParamsCol = $this->cell1->addTextRun($this->textAlignCentered);
        $techParamsCol->addText('1', $this->fontStyleWithBold12);
        $techParamsCol = $this->cell2->addTextRun();
        $techParamsCol->addText('Parametri tehnici şi funcţionali:', $this->fontStyleWithBold12);
        $techParamsCol->addTextBreak();
        $this->setWrapText($techParams, $techParamsCol);
        $techParamsCol = $this->cell3->addTextRun();
        $techParamsCol->addText('Parametri tehnici şi funcţionali:', $this->fontStyleWithBold12);
        $techParamsCol->addTextBreak();
        $this->setWrapText($proposedTechParams, $techParamsCol);
        $techParamsCol = $this->cell4->addTextRun($this->textAlignCentered);
        $techParamsCol->addText($this->setProvider(), $this->fontStyleWithoutBold12);

    }

    /**
     * @param EquipmentQuantity $model
     * adauga randul cu specificatii de performanta al tabelului
     * Display performance specifications params on proposed column when qty list type is 1 "licitație"
     * Added htmlspecialchars to solve some export problems that appears when the text contains some characters like `<`
     */
    public function addTableSpecsParamRow($model)
    {
        $specificDetails = EquipmentQuantity::getSpecificDetails(['equipmentId' => $model->equipment->id, 'equipmentQuantityId' => $model->id]);
        $specParams = htmlspecialchars($specificDetails['performance_specs_ssm']);

        $proposedSpecParams = '';
        if (!empty($model->quantityList) && !empty($model->quantityList->quantity_list_type) && $model->quantityList->quantity_list_type == 1)
            $proposedSpecParams = $specParams;

        $specParamsCol = $this->cell1->addTextRun($this->textAlignCentered);
        $specParamsCol->addText('2', $this->fontStyleWithBold12);
        $specParamsCol = $this->cell2->addTextRun();
        $specParamsCol->addText('Specificaţii de performanţă şi condiţii privind siguranţa în exploatare:', $this->fontStyleWithBold12);
        $specParamsCol->addTextBreak();
        $this->setWrapText($specParams, $specParamsCol);
        $specParamsCol = $this->cell3->addTextRun();
        $specParamsCol->addText('Specificaţii de performanţă şi condiţii privind siguranţa în exploatare:', $this->fontStyleWithBold12);
        $specParamsCol->addTextBreak();
        $this->setWrapText($proposedSpecParams, $specParamsCol);
        $specParamsCol = $this->cell4->addTextRun($this->textAlignCentered);
        $specParamsCol->addText($this->setProvider(), $this->fontStyleWithoutBold12);
    }

    /**
     * @param EquipmentQuantity $model
     * adauga randul cu conditii al tabelului
     * Display compliance requirements params on proposed column when qty list type is 1 "licitație"
     * Added htmlspecialchars to solve some export problems that appears when the text contains some characters like `<`
     */
    public function addTableConditionsRow($model)
    {
        $specificDetails = EquipmentQuantity::getSpecificDetails(['equipmentId' => $model->equipment->id, 'equipmentQuantityId' => $model->id]);
        $complianceParams = htmlspecialchars($specificDetails['compliance_conditions_stas']);

        $proposedComplianceParams = '';
        if (!empty($model->quantityList) && !empty($model->quantityList->quantity_list_type) && $model->quantityList->quantity_list_type == 1)
            $proposedComplianceParams = $complianceParams;

        $complianceParamsCol = $this->cell1->addTextRun($this->textAlignCentered);
        $complianceParamsCol->addText('3', $this->fontStyleWithBold12);
        $complianceParamsCol = $this->cell2->addTextRun();
        $complianceParamsCol->addText('Condiţii privind  conformitatea cu standardele relevante:', $this->fontStyleWithBold12);
        $complianceParamsCol->addTextBreak();
        $this->setWrapText($complianceParams, $complianceParamsCol);
        $complianceParamsCol = $this->cell3->addTextRun();
        $complianceParamsCol->addText('Condiţii privind  conformitatea cu standardele relevante:', $this->fontStyleWithBold12);
        $complianceParamsCol->addTextBreak();
        $this->setWrapText($proposedComplianceParams, $complianceParamsCol);
        $complianceParamsCol = $this->cell4->addTextRun($this->textAlignCentered);
        $complianceParamsCol->addText($this->setProvider(), $this->fontStyleWithoutBold12);
    }

    /**
     * @param EquipmentQuantity $model
     * adauga randul cu conditii de garantie al tabelului
     * Display warranty conditions params on proposed column when qty list type is 1 "licitație"
     * Added htmlspecialchars to solve some export problems that appears when the text contains some characters like `<`
     */
    public function addTableWarrantyCondRow($model)
    {
        $specificDetails = EquipmentQuantity::getSpecificDetails(['equipmentId' => $model->equipment->id, 'equipmentQuantityId' => $model->id]);
        $warrantyParams = htmlspecialchars($specificDetails['warranty_conditions']);

        $proposedWarrantyParams = '';
        if (!empty($model->quantityList) && !empty($model->quantityList->quantity_list_type) && $model->quantityList->quantity_list_type == 1)
            $proposedWarrantyParams = $warrantyParams;

        $warrantyCondParams = $this->cell1->addTextRun($this->textAlignCentered);
        $warrantyCondParams->addText('4', $this->fontStyleWithBold12);
        $warrantyCondParams = $this->cell2->addTextRun();
        $warrantyCondParams->addText('Condiţii de garanţie şi postgaranţie:', $this->fontStyleWithBold12);
        $warrantyCondParams->addTextBreak();
        $this->setWrapText($warrantyParams, $warrantyCondParams);
        $warrantyCondParams = $this->cell3->addTextRun();
        $warrantyCondParams->addText('Condiţii de garanţie şi postgaranţie:', $this->fontStyleWithBold12);
        $warrantyCondParams->addTextBreak();
        $this->setWrapText($proposedWarrantyParams, $warrantyCondParams);
        $warrantyCondParams = $this->cell4->addTextRun($this->textAlignCentered);
        $warrantyCondParams->addText($this->setProvider(), $this->fontStyleWithoutBold12);
    }

    /**
     * @param EquipmentQuantity $model
     * adauga randul cu alte conditii al tabelului
     * Display other conditions params on proposed column when qty list type is 1 "licitație"
     * Added htmlspecialchars to solve some export problems that appears when the text contains some characters like `<`
     */
    public function addTableOtherCondRow($model)
    {
        $specificDetails = EquipmentQuantity::getSpecificDetails(['equipmentId' => $model->equipment->id, 'equipmentQuantityId' => $model->id]);
        $otherParams = htmlspecialchars($specificDetails['other_technical_conditions']);

        $proposedOtherParams = '';
        if (!empty($model->quantityList) && !empty($model->quantityList->quantity_list_type) && $model->quantityList->quantity_list_type == 1)
            $proposedOtherParams = $otherParams;

        $otherCondParams = $this->cell1->addTextRun($this->textAlignCentered);
        $otherCondParams->addText('5', $this->fontStyleWithBold12);
        $otherCondParams = $this->cell2->addTextRun();
        $otherCondParams->addText('Alte condiţii cu caracter tehnic:', $this->fontStyleWithBold12);
        $otherCondParams->addTextBreak();
        $this->setWrapText($otherParams, $otherCondParams);
        $otherCondParams = $this->cell3->addTextRun();
        $otherCondParams->addText('Alte condiţii cu caracter tehnic:', $this->fontStyleWithBold12);
        $otherCondParams->addTextBreak();
        $this->setWrapText($proposedOtherParams, $otherCondParams);
        $otherCondParams = $this->cell4->addTextRun($this->textAlignCentered);
        $otherCondParams->addText($this->setProvider(), $this->fontStyleWithoutBold12);
    }

    /**
     * @param $equipmentQuantityModel
     * adauga tabela si continutul ei pentru fisa tehnica
     */
    public function addSheetContent($equipmentQuantityModel)
    {
        $table = $this->section->addTable($this->fancyTableStyle);

        // table head text
        $this->addTableCells($table);
        $this->setTableHead();

        // table head nr Crt
        $this->addTableCells($table);
        $this->setTableHeadNr();

        // table technical params row
        $this->addTableCells($table);
        $this->addTableTechnicalParamRow($equipmentQuantityModel);

        // table specs params row
        $this->addTableCells($table);
        $this->addTableSpecsParamRow($equipmentQuantityModel);

        // table conditions row
        $this->addTableCells($table);
        $this->addTableConditionsRow($equipmentQuantityModel);

        // table warranty conditions row
        $this->addTableCells($table);
        $this->addTableWarrantyCondRow($equipmentQuantityModel);

        // table other conditions row
        $this->addTableCells($table);
        $this->addTableOtherCondRow($equipmentQuantityModel);
    }

    /**
     * seteaza footerul fisei tehinice
     */
    public function setSheetFooter()
    {
        $this->section->addTextBreak();
        $this->section->addText('Proiectant ', $this->fontStyleWithoutBold12, $this->textAlignCentered);
        $this->section->addText('Leviatan Design SRL', $this->fontStyleWithoutBold12, $this->textAlignCentered);

        $this->section->addText('Proiectantul răspunde de corectitudinea completării coloanelor 1; in cazul in care contractul de lucrări are ca obiect atat proiectarea, cat si executia uneia sau mai multor lucrari de constructii, responsabilitatea completarii coloanelor 1,2 si 3 revine ofertantului. ', $this->fontStyleWithoutBold12, $this->textAlignJustify);
    }

    /**
     * adauga o pagina noua
     */
    public function addNewPage()
    {
        $this->section->addPageBreak();
    }
}