<?php

const PSW_CHANGED_NO = 0;
const FIRST_TIME_LOGIN_YES = 1;

require_once __DIR__ . '/share-point-params.php';
require_once __DIR__ . '/../../common/config/nexus-params.php';
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

$modules = [
    'adm' => 'backend\modules\adm\AdmModule',
    'crm' => 'backend\modules\crm\CrmModule',
    'location' => 'backend\modules\location\LocationModule',
    'logistic' => 'backend\modules\logistic\LogisticModule',
    'notification' => 'backend\modules\notification\NotificationModule',
    'pmp' => 'backend\modules\pmp\PmpModule',
    'entity' => 'backend\modules\entity\EntityModule',
    'admin' => ['class' => 'mdm\admin\Module',],
    'gridview' => ['class' => 'kartik\grid\Module'],
];

function includeFilesConfig(&$configs, string $pattern)
{
    $files = glob($pattern);
    foreach ($files as $file) {
        $configs = array_merge($configs, include $file);
    }
}

includeFilesConfig($params, __DIR__ . '/../modules/*/config/params-module-*.php');
includeFilesConfig($modules, __DIR__ . '/../modules/*/config/alias-module-*.php');

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => $modules,
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
//            'db' => 'initial_db'
            // uncomment if you want to cache RBAC items hierarchy
            // 'cache' => 'cache',
        ],
        'user' => [
            'identityClass' => 'backend\modules\adm\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            'site/index',
            'site/logout',
            'site/request-password-reset',
            'site/reset-password',
            'site/captcha',
//            'admin/*',
//            'design/*',
//            'some-controller/some-action',
//            // The actions listed here will be allowed to everyone including guests.
//            // So, 'admin/*' should not appear here in the production, of course.
//            // But in the earlier stages of your development, you may probably want to
//            // add a lot of actions here until you finally completed setting up rbac,
//            // otherwise you may not even take a first step.
        ]
    ],
    'as beforeRequest' => [
        'class' => 'yii\filters\AccessControl',
        'rules' => [
            [
                'actions' => ['request-password-reset', 'reset-password', 'login', 'captcha', 'error'],
                'allow' => true,
            ],
            [
                'allow' => true,
                'roles' => ['@'],
            ],
        ]
    ],
    'on beforeAction' => function ($event) {
        if (
            !Yii::$app->user->isGuest
            && Yii::$app->user->identity->psw_changed == PSW_CHANGED_NO
            && Yii::$app->requestedRoute != 'adm/user/update-password'
        ) {
            return Yii::$app->response->redirect(['adm/user/update-password', 'id' => Yii::$app->user->id]);
        }
        if (
            !Yii::$app->user->isGuest
            && Yii::$app->user->identity->psw_changed == PSW_CHANGED_NO
            && Yii::$app->requestedRoute === 'adm/user/update-password'
        ) {
            if (Yii::$app->user->identity->first_time_login == FIRST_TIME_LOGIN_YES) {
                $msg = 'Hello, this is your first login, please change the default password that was set by application.';
            } else {
                $msg = 'Hello, for security reasons, please change the password that you use to authenticate in ERP application.';
            }
            Yii::$app->session->setFlash('info delete_after', Yii::t('adm', $msg));
        }

        if (Yii::$app->user->isGuest && Yii::$app->controller->action->id === 'login'
        ) {
            Yii::$app->session->getFlash('info delete_after', null, true);
        }

        return true;
    },
    'params' => $params,
];
