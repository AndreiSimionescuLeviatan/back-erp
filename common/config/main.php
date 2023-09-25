<?php
require_once __DIR__ . '/../../common/config/mysql-params.php';
$files = glob(__DIR__ . '/../../backend/modules/*/config/db/mysql-params-module-*.php');
foreach ($files as $file) {
    include_once $file;
}

$initialDb = require(__DIR__ . '/../../common/config/initial_db.php');
$ecfAdmDb = require(__DIR__ . '/../../common/config/ecf_adm_db.php');
$ecfCrmDb = require(__DIR__ . '/../../common/config/ecf_crm_db.php');
$ecfPmpDb = require(__DIR__ . '/../../common/config/ecf_pmp_db.php');
$ecfLocationDb = require(__DIR__ . '/../../common/config/ecf_location_db.php');
$ecfNotificationDb = require(__DIR__ . '/../../common/config/ecf_notification_db.php');
$ecfEntityDb = require(__DIR__ . '/../../common/config/ecf_entity_db.php');

function includeFileConfig(&$configs, string $pattern)
{
    $files = glob($pattern);
    foreach ($files as $file) {
        $configs = array_merge($configs, include $file);
    }
}

$translations = [
    'app-notification*' => [
        'class' => 'yii\i18n\PhpMessageSource',
        'basePath' => '@api/messages',
    ],
    'app*' => [
        'class' => 'yii\i18n\PhpMessageSource',
        'basePath' => '@common/messages',
    ],
    'adm*' => [
        'class' => 'yii\i18n\PhpMessageSource',
        'basePath' => '@backend/modules/adm/messages',
    ],
    'crm*' => [
        'class' => 'yii\i18n\PhpMessageSource',
        'basePath' => '@backend/modules/crm/messages',
    ],
    'location*' => [
        'class' => 'yii\i18n\PhpMessageSource',
        'basePath' => '@backend/modules/location/messages',
    ],
    'notification*' => [
        'class' => 'yii\i18n\PhpMessageSource',
        'basePath' => '@backend/modules/notification/messages',
    ],
    'pmp*' => [
        'class' => 'yii\i18n\PhpMessageSource',
        'basePath' => '@backend/modules/pmp/messages',
    ],
    'api-hr*' => [
        'class' => 'yii\i18n\PhpMessageSource',
        'basePath' => '@api/messages',
    ],
    'api-auto*' => [
        'class' => 'yii\i18n\PhpMessageSource',
        'basePath' => '@api/messages',
    ],
    'cmd-hr*' => [
        'class' => 'yii\i18n\PhpMessageSource',
        'basePath' => '@console/messages',
    ],
    'cmd-auto*' => [
        'class' => 'yii\i18n\PhpMessageSource',
        'basePath' => '@console/messages',
    ],
    'api-logistic*' => [
        'class' => 'yii\i18n\PhpMessageSource',
        'basePath' => '@api/messages',
    ],
    'entity*' => [
        'class' => 'yii\i18n\PhpMessageSource',
        'basePath' => '@backend/modules/entity/messages',
    ],
    'revit-api*' => [
        'class' => 'yii\i18n\PhpMessageSource',
        'basePath' => '@api/messages',
    ],
    'bemaria-api*' => [
        'class' => 'yii\i18n\PhpMessageSource',
        'basePath' => '@api/messages',
    ],
    'cmd-finance*' => [
        'class' => 'yii\i18n\PhpMessageSource',
        'basePath' => '@console/messages',
    ],
];

includeFileConfig($translations, __DIR__ . '/../../backend/modules/*/config/translations-module-*.php');

$components = [
    'db' => $initialDb,
    'ecf_adm_db' => $ecfAdmDb,
    'ecf_crm_db' => $ecfCrmDb,
    'ecf_pmp_db' => $ecfPmpDb,
    'ecf_location_db' => $ecfLocationDb,
    'ecf_notification_db' => $ecfNotificationDb,
    'ecf_entity_db' => $ecfEntityDb,
    'cache' => [
        'class' => 'yii\caching\FileCache',
    ],
    'i18n' => [
        'translations' => $translations,
    ],
];

$controllerMap = [
    // Migrations for the specific extension
    'migrate-api' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => '@api/migrations',
        'migrationTable' => 'migration_api',
    ],
    'migrate-rbac' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => '@backend/rbac/migrations',
        'migrationTable' => 'migration_rbac',
    ],
    'migrate-adm-rbac' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => '@backend/modules/adm/migrations/rbac',
        'migrationTable' => 'migration_adm_rbac',
    ],
    'migrate-crm-rbac' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => '@backend/modules/crm/migrations/rbac',
        'migrationTable' => 'migration_crm_rbac',
    ],
    'migrate-location-rbac' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => '@backend/modules/location/migrations/rbac',
        'migrationTable' => 'migration_location_rbac',
    ],
    'migrate-notification-rbac' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => '@backend/modules/notification/migrations/rbac',
        'migrationTable' => 'migration_notification_rbac',
    ],
    'migrate-pmp-rbac' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => '@backend/modules/pmp/migrations/rbac',
        'migrationTable' => 'migration_pmp_rbac',
    ],
    'migrate-adm' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => '@backend/modules/adm/migrations',
        'migrationTable' => 'migration_adm',
    ],
    'migrate-crm' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => '@backend/modules/crm/migrations',
        'migrationTable' => 'migration_crm',
    ],
    'migrate-location' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => '@backend/modules/location/migrations',
        'migrationTable' => 'migration_location',
    ],
    'migrate-notification' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => '@backend/modules/notification/migrations',
        'migrationTable' => 'migration_notification',
    ],
    'migrate-pmp' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => '@backend/modules/pmp/migrations',
        'migrationTable' => 'migration_pmp',
    ],
    'migrate-entity' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => '@backend/modules/entity/migrations',
        'migrationTable' => 'migration_entity',
    ],
    'migrate-entity-rbac' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => '@backend/modules/entity/migrations/rbac',
        'migrationTable' => 'migration_entity_rbac',
    ],
];

includeFileConfig($components, __DIR__ . '/../../backend/modules/*/config/db/alias-db-module-*.php');
includeFileConfig($controllerMap, __DIR__ . '/../../backend/modules/*/config/db/migrate-module-*.php');

return [
    'name' => 'Leviatan Design ERP',
    'language' => 'ro-RO',
    'timeZone' => 'Europe/Bucharest',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => $components,
    'modules' => [],
    'controllerMap' => $controllerMap,
];
