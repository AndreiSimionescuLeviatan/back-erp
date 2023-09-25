<?php

namespace api\modules\v1\controllers;

use Yii;
use api\controllers\RestController;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\web\Response;
use yii\helpers\FileHelper;

class RestV1Controller extends RestController
{
    public static $threadName = 'default';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                HttpBasicAuth::className()
            ],
        ];
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
        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = [
            'options',
            'auth',
            'register',
            'keep-alive',
            'timestamp-device',
            'last-version',
            'download'
        ];

        return $behaviors;
    }

    /**
     * @var int[]
     */
    public static $logLevels = array(
        'ERROR' => 0,
        'WARNING' => 1,
        'INFO' => 2,
        'DEBUG' => 3
    );

    /**
     * @var string[]
     */
    public static $logLevelsKeys = array(
        0 => 'ERROR',
        1 => 'WARNING',
        2 => 'INFO',
        3 => 'DEBUG'
    );

    /**
     * @param $message
     * @return void
     */
    public static function debug($message = '')
    {
        $type = 'DEBUG';

        $currentLogLevel = 'DEBUG';
        if (defined('LOGGING_LEVEL')) {
            $currentLogLevel = LOGGING_LEVEL;
        }

        if (self::$logLevels[$type] > self::$logLevels[$currentLogLevel]) {
            return;
        }

        self::log2file($message, 'DEBUG');
    }

    /**
     * @param $message
     * @return void
     */
    public static function info($message = '')
    {
        $type = 'INFO';

        $currentLogLevel = 'DEBUG';
        if (defined('LOGGING_LEVEL')) {
            $currentLogLevel = LOGGING_LEVEL;
        }

        if (self::$logLevels[$type] > self::$logLevels[$currentLogLevel]) {
            return;
        }

        self::log2file($message, 'INFO');
    }

    /**
     * @param $message
     * @return void
     */
    public static function warning($message = '')
    {
        $type = 'WARNING';

        $currentLogLevel = 'DEBUG';
        if (defined('LOGGING_LEVEL')) {
            $currentLogLevel = LOGGING_LEVEL;
        }

        if (self::$logLevels[$type] > self::$logLevels[$currentLogLevel]) {
            return;
        }

        self::log2file($message, 'WARNING');
    }

    /**
     * @param $message
     * @return void
     */
    public static function error($message = '')
    {
        $type = 'ERROR';

        $currentLogLevel = 'DEBUG';
        if (defined('LOGGING_LEVEL')) {
            $currentLogLevel = LOGGING_LEVEL;
        }

        if (self::$logLevels[$type] > self::$logLevels[$currentLogLevel]) {
            return;
        }

        self::log2file($message, 'ERROR');
    }

    /**
     * @param $message
     * @param $type
     * @param $timestamp
     * @return string
     */
    public static function composeMessage($message = '', $type = 'DEBUG', $timestamp = '')
    {
        if ($timestamp == '') {
            $timestamp = microtime();
        }

        $output = "";
        list($mili, $time) = explode(' ', $timestamp, 2);

        $output .= date('Y-m-d H:i:s', $time);
        $output .= ',' . substr($mili, 2, 3);
        $output .= ' - ' . self::$threadName . " - {$type} - {$message}";
        $output .= "\n";

        return $output;
    }

    /**
     * @param $message
     * @param $type
     * @param $timestamp
     * @return void
     */
    public static function log($message = '', $type = 'DEBUG', $timestamp = '')
    {
        if (!isset(self::$logLevels[$type])) {
            $type = 'DEBUG';
        }

        $currentLogLevel = 'DEBUG';
        if (defined('LOGGING_LEVEL')) {
            $currentLogLevel = LOGGING_LEVEL;
        }

        if (self::$logLevels[$type] > self::$logLevels[$currentLogLevel]) {
            return;
        }

        echo self::composeMessage($message, $type, $timestamp);
    }

    /**
     * @param $message
     * @param $type
     * @param $timestamp
     * @return true|void
     * @throws \yii\base\Exception
     */
    public static function log2file($message = '', $type = 'DEBUG', $timestamp = '')
    {
        if (!isset(self::$logLevels[$type])) {
            $type = 'DEBUG';
        }

        $currentLogLevel = 'DEBUG';
        if (defined('LOGS_LEVEL')) {
            $currentLogLevel = LOGS_LEVEL;
        }

        if (self::$logLevels[$type] > self::$logLevels[$currentLogLevel]) {
            return;
        }

        $output = self::composeMessage($message, $type, $timestamp);

        $logsDir = Yii::getAlias('@api/' . Yii::$app->params['apiLogsDir']) . '/' . date('Y_m_d');
        if (!is_dir($logsDir)) {
            FileHelper::createDirectory($logsDir);
        }

        file_put_contents("{$logsDir}/" . self::$threadName . '.log', $output, FILE_APPEND);

        return true;
    }
}