<?php

namespace api\controllers;

use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\Response;
use Yii;

class RestController extends ActiveController
{
    public $return = [
        'status' => 200,
        'message' => ''
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
//        $behaviors['authenticator'] = [
//            'class' => CompositeAuth::className(),
//            'authMethods' => [
//                HttpBearerAuth::className(),
//                HttpBasicAuth::className()
//            ],
//
//        ];
        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        $behaviors['contentNegotiator'] = [
            'class' => 'yii\filters\ContentNegotiator',
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ]
        ];

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
        ];
        //$auth['authMethods'] = [HttpBasicAuth::class];
        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        // $behaviors['authenticator']['except'] = ['options'];
        $behaviors['authenticator']['except'] = [
            'options',
            'auth',
            'register'
        ];

        return $behaviors;
    }

    public function prepareResponse($message, $code = 200)
    {
        $code = max(200, $code);
        $code = min(599, $code);

        Yii::$app->response->statusCode = $code;
        Yii::$app->response->content = $message;

        $this->return['status'] = $code;
        $this->return['message'] = $message;

        return $this->return;
    }
}