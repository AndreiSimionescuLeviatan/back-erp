<?php
require_once __DIR__ . '/../../backend/config/share-point-params.php';
require_once __DIR__ . '/../../common/config/nexus-params.php';
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
        ],
    ],
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager'
        ],
        'log' => [
            /**
             * sets traceLevel to be 3 if YII_DEBUG is on and 0 if YII_DEBUG is off.
             * This means, if YII_DEBUG is on, each log message will be appended with at most 3
             * levels of the call stack at which the log message is recorded;
             * and if YII_DEBUG is off, no call stack information will be included.
             */
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['autoDocumentExpirationNotification'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/auto-document-expiration-notifications.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['expiredCarDocumentsNotification'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/expired-car-documents-notifications.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['buildQtyChangesNotifications'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/build-qty-changes-notifications.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['nexusCarsListImport'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/nexus-cars-list-import.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['nexusLocationsListImport'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/nexus-locations-list-import.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['nexusJourneysListImport'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/nexus-journeys-list-import.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['autoNotificationDifferenceKm'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/notification-difference-km.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['buildArticleEquipmentCreate'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/build-article-equipment-create.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['hrGenerateEvaluationsCto'],
                    'logVars' => [],
                    'logFile' => '@app/runtime/logs/hr-generate-evaluations-cto.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['roadMapGenerate'],
                    'logVars' => [],
                    'logFile' => '@app/runtime/logs/road-map-generate.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['journeysValidationNotification'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/journeys-validation-notification.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['sslNotification'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/ssl-notification.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['saveVisits'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/save-visits.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['autoCarAccessoryExpirationNotifications'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/accessory-exp.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['autoCarAccessoryExpirationNotifications'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/accessory-exp.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['autoRevisionNotification'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/revision-notification.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['autoValidateJourneys'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/auto-val.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['famValidate'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/fam-validate.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['financeAddDataProjExpenseController'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/finance.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'categories' => ['FinanceAddDataAccountSupplierController'],
                    'logVars' => [], //Defaults to ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER']
                    'logFile' => '@app/runtime/logs/finance.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
            ],
        ],
    ],
    'params' => $params,
];
