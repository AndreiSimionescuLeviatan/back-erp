<?php

namespace api\modules\v1\models\forms;

use api\models\Speciality;
use api\modules\v1\models\RevitFamilyImport;
use api\modules\v1\models\RevitFamily;
use api\modules\v1\models\RevitFamilyName;
use api\modules\v1\models\RevitFamilyCategory;
use api\modules\v1\models\RevitFamilyProject;
use api\modules\v1\models\RevitProject;
use backend\modules\revit\models\RevitFamilyArticle;
use common\components\HttpStatus;
use Yii;
use yii\helpers\FileHelper;
use yii\base\DynamicModel;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * This is the model class for importing revit families using the Revit iframe.
 * It is quite similar with UploadRevitFamilyForm but accepts also csv files, validates speciality and revit project to exist
 */
class UploadRevitIframeFamilyForm extends DynamicModel
{
    public $excelFile;
    public $localFilePath = null;
    public $fileHash = null;
    public $revitFamilyImportAttributes = [];
    public $rawSheetData = [];
    public $sheetData = [];
    public $columnsValidNames = [
        'name', 'type', 'level', 'height', 'area', 'volume', 'length', 'count', 'width', 'revit_category', 'speciality', 'rfa_file_path'
    ];
    public $columnsRequired = [
        'name', 'type', 'width', 'revit_category'
    ];
    public $columnsNames = [];
    public $specialityListIDs = [];
    public $project_id;
    public $source;
    /**
     * @var bool
     */
    public $existSpecialityForFamilyType = null;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['excelFile', 'source'], 'required'],
            [['project_id', 'source'], 'integer'],
            [['excelFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xls,xlsx,csv', 'checkExtensionByMimeType' => false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'excelFile' => Yii::t('build', 'Excel File')
        ];
    }

    public function saveFile()
    {
        $uploadDir = RevitFamilyImport::getUploadDirectoryPath();
        if (!is_dir($uploadDir)) {
            try {
                FileHelper::createDirectory($uploadDir);
            } catch (\Exception $exc) {
                $msg = Yii::t('revit-api', 'The directory for uploaded files could not be created.') . "\n";
                $msg .= Yii::t('revit-api', 'Error received') . ": {$exc->getMessage()} \n";
                $msg .= Yii::t('revit-api', 'Please contact an administrator!');

                throw new \Exception($msg, HttpStatus::INTERNAL_SERVER_ERROR);
            }
        }

        $this->localFilePath = $uploadDir . DIRECTORY_SEPARATOR . $this->excelFile->name;

        if (!$this->excelFile->saveAs($this->localFilePath)) {
            throw new \Exception(Yii::t('revit-api', 'The file could not be saved to disk. Please contact an administrator!'), HttpStatus::INTERNAL_SERVER_ERROR);
        }

        return true;
    }

    public function setFileHash()
    {
        if (empty($this->localFilePath)) {
            throw new \Exception(Yii::t('revit-api', 'No file was uploaded'), HttpStatus::INTERNAL_SERVER_ERROR);
        }
        if (!is_file($this->localFilePath)) {
            throw new \Exception(Yii::t('revit-api', 'File was not found'), HttpStatus::INTERNAL_SERVER_ERROR);
        }

        $this->fileHash = hash_file('sha256', $this->localFilePath);
    }

    public function setRevitFamilyImportAttributes($scope, $params = [])
    {
        $this->revitFamilyImportAttributes = [];
        if ($scope == 'check_if_exist') {
            $this->revitFamilyImportAttributes['file_hash'] = $this->fileHash;
            $this->revitFamilyImportAttributes['project_id'] = $this->project_id;

            return;
        }

        if ($scope == 'create') {
            $this->revitFamilyImportAttributes['file_hash'] = $this->fileHash;
            $this->revitFamilyImportAttributes['source'] = $this->source;
            $this->revitFamilyImportAttributes['project_id'] = $this->project_id;
            $this->revitFamilyImportAttributes['file_name'] = $this->excelFile->name;
            $this->revitFamilyImportAttributes['added'] = date('Y-m-d H:i:s');
            $this->revitFamilyImportAttributes['added_by'] = Yii::$app->user->id;
        }
    }

    public function fileAlreadyImported($params = [])
    {
        $this->setRevitFamilyImportAttributes('check_if_exist', $params);
        $model = RevitFamilyImport::findOneByAttributes($this->revitFamilyImportAttributes);
        if ($model !== null) {
            return $model;
        }
        return false;
    }

    /**
     * @throws \Exception
     */
    public function createRevitFamilyImport($params = [])
    {
        $this->setRevitFamilyImportAttributes('create', $params);
        return RevitFamilyImport::createByAttributes($this->revitFamilyImportAttributes);
    }

    public function setRawSheetData()
    {
        $inputFileType = IOFactory::identify($this->localFilePath);
        $objReader = IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true);//Get the area of data inserted
        $objReader->setReadEmptyCells(false);
        $spreadsheet = $objReader->load($this->localFilePath);
        $this->rawSheetData = $spreadsheet->getActiveSheet()->toArray();
    }

    public function setSheetData($additionalParams = [])
    {
        $this->setRawSheetData();
        $this->sheetData = [];
        for ($row = 1; $row < count($this->rawSheetData); $row++) {
            $this->setSheetDataForRow($row, $additionalParams);
        }
    }

    public function setSheetDataForRow($row, $additionalParams = [])
    {
        $this->sheetData[] = $this->getRowColumnsValues($row, $additionalParams);
    }

    public function getRowColumnsValues($row, $additionalParams = [])
    {
        $values = [];
        for ($col = 0; $col <= 20; $col++) {
            $columnName = $this->getColumnName($col);

            if ($columnName === null) {
                continue;
            }

            $value = $this->getColumnValue($row, $col);
            if (
                empty($value)
                && in_array($columnName, $this->columnsRequired)
            ) {
                continue;
            }
            $values[$columnName] = $value;
        }

        foreach ($additionalParams as $key => $value) {
            $values[$key] = $value;
        }

        return $values;
    }

    public function getColumnName($col)
    {
        if (
            !isset($this->rawSheetData[0])
            || !isset($this->rawSheetData[0][$col])
        ) {
            return null;
        }

        $columnName = strtolower(trim($this->rawSheetData[0][$col]));

        if (!in_array($columnName, $this->columnsValidNames)) {
            return null;
        }

        $this->columnsNames[$columnName] = $columnName;
        return $columnName;
    }

    public function getColumnValue($row, $col)
    {
        if (
            !isset($this->rawSheetData[$row])
            || !isset($this->rawSheetData[$row][$col])
        ) {
            return null;
        }

        return $this->rawSheetData[$row][$col];
    }

    public function saveRevitFamiliesFromCsv($params)
    {
        $additionalParams = [];
        if (!empty($params['project_id'])) {
            $project = RevitProject::findOneByAttributes(['id' => $params['project_id']]);
            if ($project !== null) {
                $additionalParams['project_id'] = $project->id;
            }
        }
        if (
            !empty($params['source'])
            && RevitFamily::sourceExist($params['source'])
        ) {
            $additionalParams['source'] = $params['source'];
        }

        $this->setSheetData($additionalParams);
        if (empty($this->sheetData)) {
            throw new \Exception(Yii::t('revit-api', 'The file does not contain any family!'), HttpStatus::BAD_REQUEST);
        }
        $_specialityCodes = [];
        $return = [];
        foreach ($this->sheetData as $data) {
            if (!in_array(trim($data['speciality']), $_specialityCodes)) {
                $_specialityCodes[] = trim($data['speciality']);
                $speciality = Speciality::findOneByAttributes(['code' => trim($data['speciality'])]);
                if ($speciality === null) {
                    $return['message'] = Yii::t('revit-api', 'Speciality not found in our database!');
                    $return['status'] = HttpStatus::NOT_FOUND;
                    return $return;
                }
                if (!in_array($speciality->id, $this->specialityListIDs)) {
                    $this->specialityListIDs[] = $speciality->id;
                }
            }
            try {
                $this->saveRevitFamily($data, $speciality->id, $additionalParams);
            } catch (\Exception $exc) {
                throw new \Exception($exc->getMessage(), $exc->getCode());
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function saveRevitFamily($data, $specialityID, $additionalParams = [])
    {
        $revitFamilyNameAttributes = [
            'name' => $data['name']
        ];
        try {
            $revitFamilyName = RevitFamilyName::getByAttributes($revitFamilyNameAttributes, $revitFamilyNameAttributes);
        } catch (\Exception $exc) {
            throw new \Exception($exc->getMessage(), $exc->getCode());
        }
        if ($revitFamilyName === null) {
            throw new \Exception(Yii::t('revit-api', 'Could not get the family name!'), HttpStatus::BAD_REQUEST);
        }

        // attributes that we were looking for be used to functions getByAttributes || createByAttributes
        $revitFamilyAttributes = [
            'type' => (string)$data['type'],
            'family_name_id' => $revitFamilyName->id,
            'speciality_id' => $specialityID
        ];

        $revitFamilyAttributesCreateAttributes = [
            'source' => RevitFamilyArticle::ASSOCIATE_SOURCE_REVIT
        ];
        if (empty($data['speciality'])) {
            throw new \Exception(Yii::t('revit-api', 'Missing required speciality column!'), HttpStatus::BAD_REQUEST);
        }

        $speciality = Speciality::findOneByAttributes(['code' => trim($data['speciality'])]);

        if ($speciality === null) {
            throw new \Exception(Yii::t('revit-api', 'Speciality not found in our database!'), HttpStatus::NOT_FOUND);
        }

        // find if existing type which must be unique by speciality
        $existRevitFamilyType = RevitFamily::findOneByAttributes([
            'type' => $data['type'],
            'speciality_id' => $specialityID
        ]);

        if (!empty($existRevitFamilyType) && (int)$existRevitFamilyType->speciality_id !== (int)$speciality->id) {
            $this->existSpecialityForFamilyType = Yii::t('revit-api', "One or mode revit families belongs to another specialty");
        } else {
            $revitFamilyAttributesCreateAttributes['speciality_id'] = $speciality->id;
            if (!empty($this->revitFamilyImportAttributes['project_id'])) {
                $revitFamilyAttributesCreateAttributes['project_id'] = $this->revitFamilyImportAttributes['project_id'];
            }
            if (!empty($this->revitFamilyImportAttributes['id'])) {
                $revitFamilyAttributesCreateAttributes['family_import_id'] = $this->revitFamilyImportAttributes['id'];
            }
            if (!empty($data['width'])) {
                $revitFamilyAttributesCreateAttributes['width'] = (string)$data['width'];
            }
            if (!empty($data['rfa_file_path'])) {
                $revitFamilyAttributesCreateAttributes['rfa_file_path'] = $data['rfa_file_path'];
            }

            if (!empty($data['revit_category'])) {
                $revitFamilyCategoryAttributes = [
                    'name' => $data['revit_category']
                ];
                try {
                    $revitFamilyCategory = RevitFamilyCategory::getByAttributes($revitFamilyCategoryAttributes, $revitFamilyCategoryAttributes);
                } catch (\Exception $exc) {
                    throw new \Exception($exc->getMessage(), $exc->getCode());
                }
                if ($revitFamilyCategory === null) {
                    throw new \Exception(Yii::t('revit-api', 'Could not get the family category!'), HttpStatus::INTERNAL_SERVER_ERROR);
                }

                $revitFamilyAttributesCreateAttributes['family_category_id'] = $revitFamilyCategory->id;
            }

            RevitFamily::getByAttributes($revitFamilyAttributes, $revitFamilyAttributes + $revitFamilyAttributesCreateAttributes);

            $existRevitFamily = RevitFamily::getByAttributes($revitFamilyAttributes);

            if ($existRevitFamily === null) {
                throw new \Exception(Yii::t('revit-api', 'Could not get the family revit!'), HttpStatus::INTERNAL_SERVER_ERROR);
            }

            $revitFamilyProjectAttributes = [
                'family_id' => $existRevitFamily['id'],
                'project_id' => $data['project_id'],
                'source' => $additionalParams['source']
            ];

            try {
                RevitFamilyProject::getByAttributes($revitFamilyProjectAttributes, $revitFamilyProjectAttributes, true);
            } catch (\Exception $exc) {
                throw new \Exception($exc->getMessage(), $exc->getCode());
            }
        }
    }
}
