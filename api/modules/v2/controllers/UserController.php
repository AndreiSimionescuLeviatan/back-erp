<?php

namespace api\modules\v2\controllers;

use api\modules\v1\models\User;
use Yii;
use yii\web\HttpException;

/**
 * V2 of User controller
 */
class UserController extends \api\modules\v1\controllers\UserController
{
    public $modelClass = 'api\modules\v1\models\User';

    /**
     * @return array
     * @throws \yii\base\Exception
     */
    public function actionAuth()
    {
        $params = Yii::$app->request->post();
        if (empty($params['email']) || empty($params['password'])) {
            Yii::$app->response->statusCode = 400;
            return [
                'status' => 400,
                'message' => Yii::t('app', 'Wrong request received. No email or password received!')
            ];
        }

        try {//validate received POST data
            User::validateLoginData($params);
        } catch (HttpException $exc) {
            Yii::$app->response->statusCode = $exc->getCode();
            $this->return['status'] = $exc->getCode();
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }

        //check if we have an active user
        $user = User::findByEmail($params['email']);
        if ($user && $user->validatePassword($params['password'])) {//validate user password
            try {
                $user->generateAuthKey();
                $user->last_auth = date('Y-m-d H:i:s');
            } catch (HttpException $exc) {
                Yii::$app->response->statusCode = $exc->statusCode;
                $this->return['status'] = $exc->statusCode;
                $this->return['message'] = $exc->getMessage();
                return $this->return;
            }

            if (!$user->save()) {
                $this->return['status'] = 500;
                Yii::$app->response->statusCode = $this->return['status'];
                if ($user->hasErrors()) {
                    foreach ($user->errors as $error) {
                        $this->return['message'] = $error[0];
                        return $this->return;
                    }
                }
                $this->return['message'] = Yii::t('app', 'Could not update user data. Please contact an administrator!');
                return $this->return;
            }

            $this->return['status'] = 200;
            $this->return['user'] = $user;
            $this->return['token'] = $user->auth_key;
            $this->return['user_can_set_device_room'] = Yii::$app->authManager->checkAccess($user->id, 'SuperAdmin');
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('app', 'Successfully authenticated');
        } else {
            $this->return['status'] = 401;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('app', 'Wrong authentication data received!');
        }
        return $this->return;
    }

    /**
     * Sends user details to app, details like `car_id` and others that are normally sent when user logs in
     * @return array
     */
    public function actionDetails()
    {
        $user = User::findIdentity(Yii::$app->user->id);
        $this->return['user'] = $user;
        $this->return['user_can_set_device_room'] = Yii::$app->user->can('SuperAdmin');

        $this->return['status'] = 200;
        Yii::$app->response->statusCode = $this->return['status'];
        $this->return['message'] = Yii::t('app', 'Successfully retrieved user details');
        return $this->return;
    }
}