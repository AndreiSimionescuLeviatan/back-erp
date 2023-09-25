<?php

namespace api\modules\v1\controllers;

use api\models\Speciality;
use api\modules\v1\models\forms\UploadRevitIframeFamilyForm;
use api\modules\v1\models\RevitFamily;
use api\modules\v1\models\RevitFamilyProject;
use common\components\HttpStatus;
use Yii;
use yii\web\UploadedFile;

/**
 * Revit Family controller
 */
class RevitFamilyController extends RestV1Controller
{
    public $modelClass = 'api\modules\v1\models\RevitFamily';
    public $specialityId;

    public function actions()
    {
        $actions = parent::actions();
        return $actions;
    }

    /**
     * @return array|void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Exception
     */
    public function actionImport()
    {
        self::$threadName = 'RevitFamilyController_actionImport';
        self::debug('Importing families from REVIT....');

        if (!Yii::$app->request->isPost) {
            $msg = Yii::t('revit-api', 'Bad method used for request');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::METHOD_NOT_ALLOWED;
            return [
                'message' => $msg
            ];
        }

        $post = Yii::$app->request->post();
        if (empty($post)) {
            $msg = Yii::t('revit-api', 'No data received');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
            return [
                'message' => $msg
            ];
        }
        $uploadModel = new UploadRevitIframeFamilyForm();
        if (!$uploadModel->load($post, '')) {
            $msg = Yii::t('revit-api', 'Could not load the data');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::INTERNAL_SERVER_ERROR;
            return [
                'message' => $msg
            ];
        }

        $uploadModel->excelFile = UploadedFile::getInstanceByName('excelFile');

        if (!$uploadModel->validate() && $uploadModel->hasErrors()) {
            foreach ($uploadModel->errors as $error) {
                $msg = Yii::t('revit-api', $error[0]);
                self::error($msg);
                Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
                return [
                    'message' => $msg
                ];
            }
        }

        try {
            $uploadModel->saveFile();
        } catch (\Exception $exc) {
            Yii::$app->response->statusCode = $exc->getCode();
            self::error($exc->getMessage());
            return [
                'message' => $exc->getMessage()
            ];
        }

        try {
            $uploadModel->setFileHash();
        } catch (\Exception $exc) {
            Yii::$app->response->statusCode = $exc->getCode();
            self::error($exc->getMessage());
            return [
                'message' => $exc->getMessage()
            ];
        }

        $revitFamilyImport = $uploadModel->fileAlreadyImported($post);

        if ($revitFamilyImport) {
            $msg = Yii::t('revit-api', 'This file was also imported by {added_by}, on {added}!', [
                'added_by' => $revitFamilyImport->getUserFullName(),
                'added' => $revitFamilyImport->added,
            ]);
            $statusCode = HttpStatus::OK;

            $return = [];

            if (!empty($post['speciality'])) {
                $speciality = Speciality::findOneByAttributes(['code' => trim($post['speciality'])]);
                if ($speciality === null) {
                    $msg = Yii::t('revit-api', 'Speciality not found in our database!');
                    $statusCode = HttpStatus::NOT_FOUND;
                } else {
                    $this->specialityId = $speciality->id;
                    $return['url'] = $revitFamilyImport->getApplicationURL($this->specialityId);
                }
            } else {
                $firstImportedSpecialityByProjectId = RevitFamilyProject::findOneByAttributes([
                    'project_id' => $revitFamilyImport->project_id
                ]);
                if (!empty($firstImportedSpecialityByProjectId)) {
                    $getSpecialityIdByFamilyId = RevitFamily::findOneByAttributes([
                        'id' => $firstImportedSpecialityByProjectId->family_id
                    ]);
                    if (!empty($getSpecialityIdByFamilyId)) {
                        $return['url'] = $revitFamilyImport->getApplicationURL($getSpecialityIdByFamilyId->speciality_id);
                    }
                }
            }

            $return['message'] = $msg;
            $return['status'] = $statusCode;
            self::info($msg);

            return $return;
        }

        try {
            $revitFamilyImport = $uploadModel->createRevitFamilyImport($post);
        } catch (\Exception $exc) {
            Yii::$app->response->statusCode = $exc->getCode();
            self::error($exc->getMessage());
            return [
                'message' => $exc->getMessage()
            ];
        }
        $uploadModel->revitFamilyImportAttributes['id'] = $revitFamilyImport->id;
        try {
            $uploadModel->saveRevitFamiliesFromCsv($post);
            $statusCode = HttpStatus::OK;
        } catch (\Exception $exc) {
            self::error($exc->getMessage());
            return [
                'message' => $exc->getMessage()
            ];
        }

        self::debug('Imported successfully.');
        try {
            if (!empty($uploadModel->existSpecialityForFamilyType)) {
                return [
                    'url' => $revitFamilyImport->getApplicationURL($uploadModel->specialityListIDs[0]),
                    'message' => Yii::t('revit-api', "Success generate URL application"),
                    'status' => $statusCode,
                    'warning' => $uploadModel->existSpecialityForFamilyType
                ];
            }
            return [
                'url' => $revitFamilyImport->getApplicationURL($uploadModel->specialityListIDs[0]),
                'message' => Yii::t('revit-api', "Success generate URL application"),
                'status' => $statusCode,
            ];

        } catch (\Exception $exc) {
            Yii::$app->response->statusCode = HttpStatus::INTERNAL_SERVER_ERROR;
            return [
                'message' => Yii::t('revit-api', $exc->getMessage())
            ];
        }
    }
}
