<?php

namespace api\controllers;

use api\models\Car;
use api\models\Device;
use api\models\User;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

/**
 * User controller
 */
class UserController extends RestController
{
    public $modelClass = 'api\models\User';

    /**
     * @return array
     */
    public function actionRegister()
    {
        $post = Yii::$app->request->post();

        try {
            Device::auth($post);
        } catch (\Exception $exc) {
            return $this->prepareResponse($exc->getMessage(), $exc->getCode());
        }

        try {
            User::register($post);
        } catch (\Exception $exc) {
            return $this->prepareResponse($exc->getMessage(), $exc->getCode());
        }

        $message = Yii::t('app', 'Successfully registered the user');
        return $this->prepareResponse($message);
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAuth()
    {
        $params = Yii::$app->getRequest()->getBodyParams();
        $this->return['user'] = null;

        try {
            User::validateLoginData($params);
        } catch (BadRequestHttpException $exc) {
            Yii::$app->response->statusCode = $exc->getCode();
            $this->return['status'] = $exc->getCode();
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }

        $user = User::findByEmail($params['email']);
        if ($user === null) {
            $this->return['status'] = 404;
            $this->return['message'] = Yii::t('app', 'User not found!');
            return $this->return;
        }
        if ($user->validatePassword($params['password'])) {
            $carModel = Car::find()->where(['user_id' => $user->id])->one();
            $carId = !empty($carModel) ? $carModel->id : null;
            Yii::$app->response->statusCode = 200;
            $this->return['status'] = 200;
            $this->return['message'] = Yii::t('app', 'Successfully authenticated');
            $this->return['user'] = $user;
            $this->return['token'] = $user->auth_key;
            $this->return['car_id'] = $carId;
            return $this->return;
        } else {
            Yii::$app->response->statusCode = 401;
            $this->return['status'] = 401;
            $this->return['message'] = Yii::t('app', 'Wrong authentication data received!');
            return $this->return;
        }
    }

    /**
     * Sends user details to app, details like `car_id` and others that are normally sent when user logs in
     * @return array
     */
    public function actionDetails()
    {
        $token = Yii::$app->request->get('token');
        if (empty($token)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing token');
            return $this->return;
        }
        $userId = Yii::$app->request->get('id');
        if (empty($userId)) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('app', 'Incomplete received data, missing user id');
            return $this->return;
        }

        $user = User::findUserByIdToken($userId, $token);

        Yii::$app->response->statusCode = 200;
        $this->return['message'] = Yii::t('app', 'Successfully authenticated');
        $this->return['car_id'] = !empty($user->usedCar) ? $user->usedCar->id : null;
        return $this->return;
    }
}