<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Employee;
use api\modules\v1\models\User;
use backend\modules\adm\models\UserSignature;
use common\components\HttpStatus;
use Yii;
use yii\helpers\FileHelper;
use yii\web\HttpException;

/**
 * V1 of User controller
 */
class UserController extends RestV1Controller
{
    public $modelClass = 'api\modules\v1\models\User';

    public function actions()
    {
        $actions = parent::actions();
        return $actions;
    }

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
        $userCarId = null;
        $usedCarDetails = null;

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

            $this->return['user'] = $user;
            $this->return['token'] = $user->auth_key;

            try {
                $this->return['auto'] = $user->getAutoDetails();
            } catch (HttpException $exc) {
                Yii::$app->response->statusCode = $exc->statusCode;
                $this->return['status'] = $exc->statusCode;
                $this->return['message'] = $exc->getMessage();
                return $this->return;
            }

            $this->return['status'] = 200;
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
        if (!$user) {
            Yii::$app->response->statusCode = 401;
            return [
                'status' => 401,
                'message' => Yii::t('app', 'User not found!')
            ];
        }
        if (!$user->employee) {
            Yii::$app->response->statusCode = 401;
            return [
                'status' => 401,
                'message' => Yii::t('app', "You don't have a valid employee account. Please ask to an administrator to create one for you!")
            ];
        }
        $this->return['user'] = $user;
        $userSignature = UserSignature::getSignature($user->id);
        if ($userSignature) {
            $this->return['user_signature'] = $userSignature;
        }

        try {
            $this->return['hr'] = Employee::getHrDetails($user->id);
        } catch (HttpException $exc) {
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['status'] = $exc->statusCode;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }

        try {
            $this->return['auto'] = $user->getAutoDetails();
        } catch (HttpException $exc) {
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['status'] = $exc->statusCode;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }

        $this->return['status'] = 200;
        Yii::$app->response->statusCode = $this->return['status'];
        $this->return['message'] = Yii::t('app', 'Successfully retrieved user details');
        return $this->return;
    }

    public function actionSignature()
    {
        $post = Yii::$app->request->post();

        if (empty($post['user_id'])) {
            Yii::$app->response->statusCode = 400;
            return [
                'status' => 400,
                'message' => Yii::t('app', 'Wrong request received. No user_id received!')
            ];
        }

        if (empty($post['signature'])) {
            Yii::$app->response->statusCode = 400;
            return [
                'status' => 400,
                'message' => Yii::t('app', 'Wrong request received. No signature received!')
            ];
        }
        $signature = $post['signature'];

        $signatureDir = Yii::getAlias('@backend/web/images/signatures');
        try {
            if (!is_dir($signatureDir)) {
                FileHelper::createDirectory($signatureDir);
            }
            if (preg_match('/^data:image\/(\w+);base64,/', $signature, $type)) {
                $image = substr($signature, strpos($signature, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif
                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    $this->return['status'] = HttpStatus::BAD_REQUEST;
                    Yii::$app->response->statusCode = $this->return['status'];
                    $this->return['message'] = Yii::t('api-auto', 'Invalid signature format!');
                    return $this->return;
                }
                $signatureName = Yii::$app->user->id . "_" . uniqid() . ".{$type}";
                $signaturePath = $signatureDir . '/' . $signatureName;
                $image = str_replace(' ', '+', $image);
                $image = base64_decode($image);
                $existingUserSignature = UserSignature::find()
                    ->where(['deleted' => 0, 'user_id' => Yii::$app->user->id])
                    ->one();
                if (empty($existingUserSignature)) {
                    $userSignature = new UserSignature();
                    $userSignature->signature = $signatureName;
                    $userSignature->user_id = Yii::$app->user->id;
                    $userSignature->added = date('Y-m-d H:i:s');
                    $userSignature->added_by = Yii::$app->user->id;
                    if (!$userSignature->save()) {
                        if ($userSignature->hasErrors()) {
                            foreach ($userSignature->errors as $error) {
                                throw new HttpException(409, $error[0]);
                            }
                        }
                        throw new HttpException(409, Yii::t('api-auto', 'Could not save signature. Please contact an administrator!'));
                    }
                } else {
                    $existingUserSignature->signature = $signatureName;
                    $existingUserSignature->updated = date('Y-m-d H:i:s');
                    $existingUserSignature->updated_by = Yii::$app->user->id;
                    if (!$userSignature->save()) {
                        if ($userSignature->hasErrors()) {
                            foreach ($userSignature->errors as $error) {
                                throw new HttpException(409, $error[0]);
                            }
                        }
                        throw new HttpException(409, Yii::t('api-auto', 'Could not save signature. Please contact an administrator!'));
                    }
                }
                if ($image === false) {
                    $this->return['status'] = HttpStatus::BAD_REQUEST;
                    Yii::$app->response->statusCode = $this->return['status'];
                    $this->return['message'] = Yii::t('api-auto', 'Could not decode the signature. Please contact an administrator!');
                    return $this->return;
                }
            } else {
                $this->return['status'] = HttpStatus::BAD_REQUEST;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-auto', 'Did not match data URI with image data. Please contact an administrator!');
                return $this->return;
            }
            if (!file_put_contents($signaturePath, $image)) {
                $this->return['status'] = HttpStatus::INTERNAL_SERVER_ERROR;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-auto', 'The signature could not be saved. Please contact an administrator!');
                return $this->return;
            }
        } catch (\Exception $exc) {
            $msg = "Error received while saving signature: {$exc->getMessage()} \n";
            $msg .= "Please contact an administrator!";
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = $msg;
            return $this->return;
        }

        $this->return['status'] = 200;
        Yii::$app->response->statusCode = $this->return['status'];
        $this->return['message'] = $signature;
        return $this->return;
    }

}