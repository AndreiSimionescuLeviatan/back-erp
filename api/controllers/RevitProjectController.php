<?php

namespace api\controllers;

use api\models\RevitProject;
use api\models\User;
use Yii;
use yii\web\BadRequestHttpException;

/**
 * User controller
 * @deprecated on Revit Andrei renounced to this action since 13/10/2022
 */
class RevitProjectController extends RestController
{
    public $modelClass = 'api\models\RevitProject';

    /**
     * @return object|null
     * @throws \yii\base\InvalidConfigException
     */
    private static function getDb()
    {
        return Yii::$app->get('ecf_build_db');
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

    public function prepareDataProvider()
    {
        $post = Yii::$app->request->post();

        try {
            if (empty($post) || empty($post['user'])) {
                throw new BadRequestHttpException('Did not get any token for user', 400);
            }

            $user = User::getUserByAuthKey($post['user']);
        } catch (BadRequestHttpException $exc) {
            $this->return['status'] = 400;
            return $this->prepareResponse($exc->getMessage(), $exc->getCode());
        }

        $revitProjects = RevitProject::find()->select('id AS project_id, code, name, source, file_path, added, updated')
            ->where('`added_by` = :addedBy', [':addedBy' => $user->id])->orderBy('id DESC')->asArray()->all();

        $this->return['projects'] = $revitProjects;

        $message = Yii::t('app', 'Successfully sent the projects');
        return $this->prepareResponse($message);
    }

    public function actionCreate()
    {
        $post = Yii::$app->request->post();

        try {
            if (empty($post) || empty($post['user'])) {
                throw new BadRequestHttpException('Did not get any token for user', 400);
            }
            $user = User::getUserByAuthKey($post['user']);

            if (empty($post['code']) || empty($post['name'])) {
                throw new BadRequestHttpException('Did not get project name or code', 400);
            }
            if (empty($post['source'])) {
                throw new BadRequestHttpException('Did not get the source', 400);
            }
            if (empty($post['file_path'])) {
                throw new BadRequestHttpException('Did not get the file_path', 400);
            }
        } catch (BadRequestHttpException $exc) {
            return $this->prepareResponse($exc->getMessage(), $exc->getCode());
        }

        $revitProject = RevitProject::find()->select('id')->where('(`code` = :code OR `name` = :name) AND `added_by` = :addedBy', [
            ':code' => $post['code'],
            ':name' => $post['name'],
            ':addedBy' => $user->id,
        ])->asArray()->one();

        if (empty($revitProject)) {
            $revitProject = new RevitProject();
            $revitProject->code = $post['code'];
            $revitProject->name = $post['name'];
            $revitProject->source = $post['source'];
            $revitProject->file_path = $post['file_path'];
            $revitProject->added = date('Y-m-d H:i:s');
            $revitProject->added_by = $user->id;

            if (!$revitProject->save()) {
                $message = Yii::t('app', 'Could not save the project');
                return $this->prepareResponse($message, 400);
            }

            $message = Yii::t('app', 'Successfully created the project');
            $this->return['project_id'] = $revitProject->id;
        } else {
            $message = Yii::t('app', 'The project already exist');
            $this->return['project_id'] = -1;
        }

        return $this->prepareResponse($message);
    }
}
