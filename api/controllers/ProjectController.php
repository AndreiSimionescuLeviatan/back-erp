<?php

namespace api\controllers;

use api\models\Device;
use backend\modules\adm\models\User;
use backend\modules\design\models\Project;
use Yii;

/**
 * Project controller
 */
class ProjectController extends RestController
{
    public $modelClass = 'api\models\Project';

    /**
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();

        // disable the "delete", "create", "update" and "view" actions
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['view']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function verbs()
    {
        return [
            'index' => ['GET']
        ];
    }

    public function prepareDataProvider()
    {
        $get = Yii::$app->request->get();

        $token = $get['token'];
        if (empty($token)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing token');
            return $this->return;
        }
        $uuid = Yii::$app->request->get('uuid');
        if (empty($uuid)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing uuid');
            return $this->return;
        }

        $user = User::find()->where('auth_key = :auth_key', [':auth_key' => $token])->one();
        if (empty($user)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }

        $device = Device::find()->where('uuid = :uuid', [':uuid' => $uuid])->one();
        if (empty($device)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Bad request');
            return $this->return;
        }

        $projects = Project::find()->select('id, name')->indexBy('id')->where('`deleted` = 0')->asArray()->all();

        $this->return['projects'] = $projects;

        $message = Yii::t('app', 'Successfully sent the projects');
        return $this->prepareResponse($message);
    }
}
