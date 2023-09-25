<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\forms\UploadRevitIframeProjectForm;
use api\modules\v1\models\RevitFamilyArticle;
use api\modules\v1\models\RevitProject;
use common\components\HttpStatus;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * User controller
 */
class RevitProjectController extends RestV1Controller
{
    public $modelClass = 'api\modules\v1\models\RevitProject';

    /**
     * @return object|null
     * @throws \yii\base\InvalidConfigException
     */
    private static function getDb()
    {
        return Yii::$app->get('ecf_revit_db');
    }

    /**
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();

        // disable the "delete", "create", "update" and "view" actions
        unset($actions['delete'], $actions['update'], $actions['create'], $actions['view']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function verbs()
    {
        return [
            'index' => ['POST'],
        ];
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     */
    public function prepareDataProvider()
    {
        $post = Yii::$app->request->post();
        if (!empty($post['source'])) {
            $source = $post['source'];
            $revitProjects = RevitProject::find()
                ->select('id AS project_id, code, name, source, file_path, added, updated')
                ->where([
                    "deleted" => 0,
                    "added_by" => Yii::$app->user->id,
                    "source" => $source
                ])
                ->orderBy([
                    'COALESCE(updated, added)' => SORT_DESC,
                ])
                ->asArray()
                ->all();
        } else {
            $revitProjects = RevitProject::find()->select('id AS project_id, code, name, source, file_path, added, updated')
                ->where(
                    '`deleted` = 0 AND `added_by` = :addedBy',
                    [':addedBy' => Yii::$app->user->id]
                )
                ->orderBy([
                    'COALESCE(updated, added)' => SORT_DESC,
                ])
                ->asArray()
                ->all();
        }

        $this->return['projects'] = $revitProjects;

        $message = Yii::t('revit-api', 'Successfully sent the projects');
        return $this->prepareResponse($message);
    }

    /**
     * @return array
     */
    public function actionCreate()
    {
        $post = Yii::$app->request->post();
        try {
            RevitProject::validateInput($post);
        } catch (BadRequestHttpException $exc) {
            return $this->prepareResponse($exc->getMessage(), $exc->getCode());
        }

        $attributes = [
            'code' => $post['code'],
            'name' => $post['name'],
            'added_by' => Yii::$app->user->id,
        ];

        $revitProject = RevitProject::findOneByAttributes($attributes);

        if ($revitProject !== null) {
            $message = Yii::t('revit-api', 'The project already exist');
            $this->return['project_id'] = -1;
            return $this->prepareResponse($message, HttpStatus::BAD_REQUEST);
        }
        $uploadModel = new UploadRevitIframeProjectForm();
        $uploadModel->source = $post['source'];
        if (!$uploadModel->load($post, '')) {
            $msg = Yii::t('revit-api', 'Could not load the data');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::INTERNAL_SERVER_ERROR;
            return [
                'message' => $msg
            ];
        }
        if (
             $post['source'] == RevitFamilyArticle::SOURCE_TEMPLATE 
             || $post['source'] == RevitFamilyArticle::SOURCE_MONADA_JS
        ) {
            $uploadModel->monadaFile = UploadedFile::getInstanceByName('monada_project');
            $uploadModel->builderFile = UploadedFile::getInstanceByName('builder_project');
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
                $uploadModel->saveFile(Yii::$app->user->id, 'monada', $post['code']);
                $uploadModel->saveFile(Yii::$app->user->id, 'builder', $post['code']);
            } catch (\Exception $exc) {
                Yii::$app->response->statusCode = $exc->getCode();
                self::error($exc->getMessage());
                return [
                    'message' => $exc->getMessage()
                ];
            }
            $attributes['monada_file_path'] = $uploadModel->monadaFileName;
            $attributes['builder_file_path'] = $uploadModel->builderFileName;
        }

        $attributes['source'] = $post['source'];
        $attributes['file_path'] = $post['file_path'];

        try {
            $revitProject = RevitProject::createByAttributes($attributes);
        } catch (\Exception $exc) {
            return $this->prepareResponse($exc->getMessage(), $exc->getCode());
        }

        $message = Yii::t('revit-api', 'Successfully created the project');
        $this->return['project_id'] = $revitProject->id;

        return $this->prepareResponse($message);
    }

    /**
     * @return array|\yii\console\Response|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDownloadProjectFiles()
    {
        $get = Yii::$app->request->get();
        try {
            RevitProject::validateDownloadInput($get);
        } catch (BadRequestHttpException $exc) {
            return $this->prepareResponse($exc->getMessage(), $exc->getCode());
        }

        $model = new RevitProject();
        $projectZip = Yii::getAlias('@api/' . Yii::$app->params['zipRevitProjectFileUploadDir'] . DIRECTORY_SEPARATOR . $model->getZipFileName(Yii::$app->user->id, $get['project_id']));
        if (!file_exists($projectZip)) {
            throw new NotFoundHttpException("The project archive not found");
        }

        Yii::$app->response->headers->set('hash', strtoupper(hash_file('sha256', $projectZip)));
        return Yii::$app->response->sendFile($projectZip);
    }


    /**
     * @return array|mixed
     * @throws BadRequestHttpException
     */
    public function actionUpdateProject()
    {
        $post = Yii::$app->request->post();
        try {
            if (empty($post['project_id'])) {
                throw new BadRequestHttpException(Yii::t('revit-api', 'Did not get the project_id'), HttpStatus::BAD_REQUEST);
            }
        } catch (BadRequestHttpException $exc) {
            return $this->prepareResponse($exc->getMessage(), $exc->getCode());
        }

        $revitProject = RevitProject::findOneByAttributes([
            'id' => $post['project_id']
        ]);

        if ($revitProject === null) {
            throw new BadRequestHttpException(Yii::t('revit-api', 'Did not get the project'), HttpStatus::BAD_REQUEST);
        }

        $uploadModel = new UploadRevitIframeProjectForm();
        if (!$uploadModel->load($post, '')) {
            $msg = Yii::t('revit-api', 'Could not load the data');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::INTERNAL_SERVER_ERROR;
            return [
                'message' => $msg
            ];
        }

        $uploadModel->monadaFile = UploadedFile::getInstanceByName('monada_project');
        $uploadModel->builderFile = UploadedFile::getInstanceByName('builder_project');

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
            $uploadModel->saveFile(Yii::$app->user->id, 'monada', $revitProject->code, $revitProject);
            $uploadModel->saveFile(Yii::$app->user->id, 'builder', $revitProject->code, $revitProject);

            $revitProject->monada_file_path = $uploadModel->monadaFileName;
            $revitProject->builder_file_path = $uploadModel->monadaFileName;
            $revitProject->updated = date('Y-m-d H:i:s');
            $revitProject->updated_by = Yii::$app->user->id;
            if (!$revitProject->save()) {
                if ($revitProject->hasErrors()) {
                    foreach ($revitProject->errors as $error) {
                        $msg = Yii::t('revit-api', $error[0]);
                        self::error($msg);
                        Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
                        return [
                            'message' => $msg
                        ];
                    }
                }
            }
        } catch (\Exception $exc) {
            Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
            self::error($exc->getMessage());
            return [
                'message' => $exc->getMessage()
            ];
        }

        $message = Yii::t('revit-api', 'Successfully updated the project');
        $this->return['project_id'] = $revitProject->id;

        return $this->prepareResponse($message);
    }
}
