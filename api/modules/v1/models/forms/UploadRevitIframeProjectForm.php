<?php

namespace api\modules\v1\models\forms;

use api\modules\v1\models\RevitFamilyArticle;
use api\modules\v1\models\RevitProject;
use common\components\HttpStatus;
use Exception;
use Yii;
use yii\helpers\FileHelper;
use yii\base\DynamicModel;
use ZipArchive;

class UploadRevitIframeProjectForm extends DynamicModel
{
    public $monadaFile;
    public $source;
    public $builderFile;
    public $monadaLocalFilePath;
    public $builderLocalFilePath;
    public $monadaFileName;
    public $builderFileName;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['monadaFile', 'builderFile'], 'required', 'when' => function ($model) {
                return $model->source == RevitFamilyArticle::SOURCE_TEMPLATE;
            }],
            [['monadaFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'mnd', 'checkExtensionByMimeType' => false],
            [['builderFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'json', 'checkExtensionByMimeType' => false]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'monadaFile' => Yii::t('revit-api', 'Monada File'),
            'builderFile' => Yii::t('revit-api', 'Builder File')
        ];
    }

    /**
     * @param $userId
     * @param $projectType
     * @param $code
     * @param $model | RevitProject
     * @return true
     * @throws Exception
     */
    public function saveFile($userId, $projectType, $code, $model = null)
    {
        $this->{$projectType . 'FileName'} = $this->buildFileName($userId, $projectType);
        $this->{$projectType . 'LocalFilePath'} = $this->buildLocalFilePath($projectType);
        if (!$this->{$projectType . 'File'}->saveAs($this->{$projectType . "LocalFilePath"})) {
            throw new \Exception(Yii::t('revit-api', 'The file could not be saved to disk. Please contact an administrator') . '!', HttpStatus::INTERNAL_SERVER_ERROR);
        }
        $file = $this->buildFileName($userId, $projectType);
        if ($model !== null) {
            $this->newZipContent($file, $projectType, $userId, $code);
        }

        $this->createZip($file, $projectType, $userId, $code);

        return true;
    }


    /**
     * @param $file
     * @param $projectType
     * @param $userId
     * @param $code
     * @return void
     * @throws Exception
     */
    public function createZip($file, $projectType, $userId, $code)
    {
        $fileName = $this->buildZipFileName($userId, $code);

        $zip = new ZipArchive();
        if ($zip->open($fileName, ZipArchive::CREATE) !== TRUE) {
            throw new \Exception(Yii::t('revit-api', 'Cannot create a zip file'), HttpStatus::INTERNAL_SERVER_ERROR);
        } else {
            $zip->addFile($this->{$projectType . "LocalFilePath"}, $file);
            $zip->close();
        }
    }

    /**
     * @param $file
     * @param $projectType
     * @param $userId
     * @param $code
     * @param $model | RevitProject
     * @return true|void
     * @throws Exception
     */
    public function newZipContent($file, $projectType, $userId, $code)
    {
        $zip = new ZipArchive();
        if ($zip->open($this->buildZipFileName($userId, $code)) !== TRUE) {
            $this->createZip($file, $projectType, $userId, $code);
            return true;
        }
        if ($zip->numFiles > 1) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $zip->deleteIndex($i);
            }
        } else {
            if ($projectType == 'monada') {
                $zip->addFile($this->{$projectType . "LocalFilePath"}, $file);
            } else {
                $zip->addFile($this->{$projectType . "LocalFilePath"}, $file);
            }
        }
        $zip->close();
    }

    /**
     * @param $userId
     * @param $projectType
     * @return string
     */
    public function buildFileName($userId, $projectType)
    {
        return $userId . '_' . strtotime(date("Y-m-d H:i:s")) . '_' . $this->{$projectType . "File"}->name;
    }

    /**
     * @param $userId
     * @param $code
     * @return string
     * @throws Exception
     */
    public function buildZipFileName($userId, $code)
    {
        return $this->buildZipLocalFilePath() . DIRECTORY_SEPARATOR . $userId . "_" . $code . ".zip";
    }

    /**
     * @param $projectType
     * @return string
     * @throws \Exception
     */
    public function buildLocalFilePath($projectType)
    {
        $uploadDir = RevitProject::getUploadDirectoryPath($projectType);
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

        return $uploadDir . DIRECTORY_SEPARATOR . $this->{$projectType . 'FileName'};
    }

    /**
     * @return false|string
     * @throws Exception
     */
    public function buildZipLocalFilePath()
    {
        $uploadDirZip = RevitProject::getUploadZipDirectoryPath();
        if (!is_dir($uploadDirZip)) {
            try {
                FileHelper::createDirectory($uploadDirZip);
            } catch (\Exception $exc) {
                $msg = Yii::t('revit-api', 'The directory for uploaded files could not be created.') . "\n";
                $msg .= Yii::t('revit-api', 'Error received') . ": {$exc->getMessage()} \n";
                $msg .= Yii::t('revit-api', 'Please contact an administrator!');

                throw new \Exception($msg, HttpStatus::INTERNAL_SERVER_ERROR);
            }
        }
        return $uploadDirZip;
    }
}