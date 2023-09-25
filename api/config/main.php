<?php
require_once __DIR__ . '/../../backend/config/share-point-params.php';
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'api\controllers',
    'modules' => [
        'v1' => [
            'class' => 'api\modules\v1\Module',
        ],
        'v2' => [
            'class' => 'api\modules\v2\Module',
        ],
    ],
    'components' => [
        'request' => [
            'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
            'parsers' => [

                // Info: The above configuration is optional.
                // Without the above configuration, the API would only recognize
                // application/x-www-form-urlencoded and multipart/form-data input formats.
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'mutex' => [
            'class' => 'yii\mutex\FileMutex',
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8'
        ],
        'user' => [
            'identityClass' => 'api\models\User',
            'enableSession' => false,
//            'enableAutoLogin' => true,
//            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            // uncomment if you want to cache RBAC items hierarchy
            // 'cache' => 'cache',
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
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v1/user', 'v2/user'],
                    'except' => ['create', 'index', 'view', 'update', 'delete'],
                    'extraPatterns' => [
                        'OPTIONS <action:\w+|action:>' => 'options',
                        'POST auth' => 'auth',
                        'GET details' => 'details',
                        'POST signature' => 'signature',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v1/device', 'v2/device'],
                    'extraPatterns' => [
                        'OPTIONS <action:\w+|action:>' => 'options',
                        'POST auth' => 'auth',
                        'POST register' => 'register',
                        'POST keep-alive' => 'keep-alive',
                        'POST download' => 'download',
                        'POST last-version' => 'last-version',
                        'POST timestamp-device' => 'timestamp-device'
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'car',
                    'extraPatterns' => [
                        'GET available-cars' => 'available-cars',
                        'GET details' => 'details',
                        'GET details-new' => 'details-new',
                        'GET documents' => 'documents',
                        'POST create-car' => 'create-car',
                        'POST car-zone-history' => 'car-zone-history',
                        'POST preview-pv' => 'preview-pv',
                        'POST update-car-status' => 'update-car-status',
                        'POST upload-personal-digital-signature' => 'upload-personal-digital-signature',
                        'POST upload-photo-car-zone' => 'upload-photo-car-zone',
                        'POST unlock' => 'unlock',
                        'POST generate-pdf' => 'generate-pdf',
                        'GET journeys' => 'journeys',
                        'GET journeys-new' => 'journeys-new',
                        'POST validate-journeys' => 'validate-journeys',
                        'GET accessories' => 'accessories',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/car',
                    'except' => ['create', 'index', 'view', 'update', 'delete'],
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'GET available-cars' => 'available-cars',
                        'GET details' => 'details',
                        'GET documents' => 'documents',
                        'GET accessories' => 'accessories',
                        'POST preview-pv' => 'preview-pv',
                        'POST update-car-status' => 'update-car-status',
                        'POST unlock' => 'unlock',
                        'POST upload-document' => 'upload-document',
                        'GET view-document' => 'view-document',
                        'POST set-km' => 'set-km',
                        'GET get-document' => 'get-document',
                        'GET download-car-pv' => 'download-car-pv',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'journey',
                    'except' => ['delete', 'create', 'update', 'view'],
                    'patterns' => [
                        'GET' => 'index'
                    ],
                    'extraPatterns' => [
                        'POST validate' => 'validate'
                    ],

                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/journey',
                    'except' => ['create', 'update', 'view', 'delete'],//Any action found in this array will NOT have its URL rules created.
                    'patterns' => [
                        'OPTIONS' => 'options',
                        'GET' => 'index',
                    ],
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'POST validate' => 'validate',
                        'GET details' => 'details',
                        'POST delete-journey' => 'delete-journey',
                        'POST activate' => 'activate',
                        'GET get-locations' => 'get-locations',
                        'POST merge-journeys' => 'merge-journeys',
                        'POST update-hotspot-name' => 'update-hotspot-name',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/accessory',
                    'except' => ['create', 'view', 'update', 'delete'],
                    'patterns' => [
                        'OPTIONS' => 'options',
                        'GET' => 'index'
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v1/evaluation', 'v2/evaluation'],
                    'except' => ['create', 'view', 'update', 'delete'],
                    'patterns' => [
                        'OPTIONS' => 'options'
                    ],
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'GET count' => 'count',
                        'GET employees' => 'employees',
                        'POST find-all' => 'find-all',
                        'POST find' => 'find',
                        'POST save' => 'save',
                        'GET grades-by-months' => 'grades-by-months',
                        'GET grades-by-categories' => 'grades-by-categories',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/revit-project',
                    'except' => ['delete', 'update', 'view'],
                    'patterns' => [
                        'POST' => 'index',
                        'POST create' => 'create',
                        'POST update-project' => 'update-project',
                        'GET download-project-files' => 'download-project-files'
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/invoice',
                    'except' => ['create', 'view', 'update', 'delete'],
                    'patterns' => [
                        'OPTIONS' => 'options'
                    ],
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'POST upload-images' => 'upload-images',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/revit-family',
                    'except' => ['delete', 'update', 'view'],
                    'patterns' => [
                        'OPTIONS' => 'options'
                    ],
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'POST import' => 'import',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/company',
                    'except' => ['create', 'delete', 'update', 'view'],
                    'patterns' => [
                        'OPTIONS' => 'options'
                    ],
                    'extraPatterns' => [
                        'GET clients' => 'clients',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v1/meeting-room', 'v2/meeting-room'],
                    'except' => ['create', 'delete', 'update'],
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'GET users-list' => 'users-list',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v1/room-reservation', 'v2/room-reservation'],
                    'except' => ['index', 'create', 'view', 'delete', 'update'],
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'GET meetings-list' => 'meetings-list',
                        'POST create-meeting' => 'create-meeting',
                        'POST update-meeting' => 'update-meeting',
                        'POST update-single-meeting' => 'update-single-meeting',
                        'POST update-multiple-meeting' => 'update-multiple-meeting',
                        'POST delete-meeting' => 'delete-meeting',
                        'GET details' => 'details',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/send-notification',
                    'except' => ['delete', 'update', 'view'],
                    'patterns' => [
                        'OPTIONS' => 'options'
                    ],
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'POST email' => 'email',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v1/shift', 'v2/shift'],
                    'except' => ['create', 'index', 'update', 'delete'],
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'GET {id}' => 'view',
                        'POST monthly-schedule-details' => 'monthly-schedule-details',
                        'POST shift-details' => 'shift-details',
                        'POST break-details' => 'break-details',
                        'POST shifts-history' => 'shifts-history',
                        'POST start-shift' => 'start-shift',
                        'POST start-break' => 'start-break',
                        'POST stop-shift' => 'stop-shift',
                        'POST stop-break' => 'stop-break',
                        'POST manual-add-break' => 'manual-add-break',
                        'POST manual-update-shift' => 'manual-update-shift',
                        'POST manual-update-break' => 'manual-update-break',
                        'POST delete-shift' => 'delete-shift',
                        'POST delete-break' => 'delete-break',
                        'GET history' => 'history',
                        'POST save-shift' => 'save-shift',
                        'POST validate-shift' => 'validate-shift',
                        'POST ongoing-shift' => 'ongoing-shift',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/general-hr-components',
                    'except' => ['delete', 'update', 'view'],
                    'patterns' => [
                        'OPTIONS' => 'options'
                    ],
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'POST save-app-feedback' => 'save-app-feedback'
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v2/vacation-type',
                    'except' => ['create', 'delete', 'update'],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v1/device-token', 'v2/device-token'],
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'POST save-firebase-token' => 'save-firebase-token'
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',

                    'controller' => 'v2/chat-gpt',
                    'except' => ['delete', 'update', 'view', 'create'],
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'GET feedback' => 'feedback'
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v2/be-maria',
                    'except' => ['index', 'delete', 'update', 'view', 'create'],
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'POST ask' => 'ask',
                        'POST feedback' => 'feedback',
                        'POST speech-2-text' => 'speech-2-text',
                        'POST text-2-speech' => 'text-2-speech'
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v2/employee',
                    'except' => ['index', 'delete', 'update', 'view', 'create'],
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'GET assigned-companies' => 'assigned-companies',
                        'GET employees-take-over-list' => 'employees-take-over-list',
                        'GET time-off-requests' => 'time-off-requests',
                        'GET monthly-schedule-details' => 'monthly-schedule-details',
                        'GET shifts' => 'shifts',
                        'GET openshift' => 'openshift',
                        'GET permissions' => 'permissions',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v2/time-off',
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'GET approval-requests' => 'approval-requests',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v2/approval-history',
                    'except' => ['index', 'delete', 'update', 'create'],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v2/family',
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'GET placements' => 'placements',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v2/speciality',
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                        'GET speciality' => 'speciality',
                    ]
                ],
            ],
        ],
    ],
    'params' => $params,
];