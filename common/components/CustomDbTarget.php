<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace common\components;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\db\Exception;
use yii\di\Instance;
use yii\helpers\VarDumper;
use yii\log\LogRuntimeException;
use yii\log\Target;

/**
 * DbTarget stores log messages in a database table.
 *
 * The database connection is specified by [[db]]. Database schema could be initialized by applying migration:
 *
 * ```
 * yii migrate --migrationPath=@yii/log/migrations/
 * ```
 *
 * If you don't want to use migration and need SQL instead, files for all databases are in migrations directory.
 *
 * You may change the name of the table used to store the data by setting [[logTable]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CustomDbTarget extends Target
{
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * After the DbTarget object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db = 'db';
    /**
     * @var string name of the DB table to store cache content. Defaults to "log".
     */
    public $logTable = '{{%log}}';


    /**
     * Initializes the DbTarget component.
     * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
     * @throws InvalidConfigException if [[db]] is invalid.
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }

    /**
     * Stores log messages to DB.
     * @throws Exception
     * @throws LogRuntimeException|InvalidConfigException
     */
    public function export()
    {
        if ($this->db->getTransaction()) {
            // create new database connection, if there is an open transaction
            // to ensure insert statement is not affected by a rollback
            $this->db = clone $this->db;
        }

        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        $userID = $user ? $user->getId(false) : null;

        $tableName = $this->db->quoteTableName($this->logTable);
        $sql = "INSERT INTO $tableName ([[type]], [[module_name]], [[entity_name]], [[event_operation]], [[event_name]], [[entity_data]], [[level]], [[category]], [[log_time]], [[prefix]], [[raw_sql]], [[added_by]])
                VALUES (:type, :module_name, :entity_name, :event_operation, :event_name, :entity_data, :level, :category, :log_time, :prefix, :raw_sql, :added_by)";
        $command = $this->db->createCommand($sql);

        foreach ($this->messages as $key => $message) {
            list($rawSql, $level, $category, $timestamp) = $message;
            if (!is_string($rawSql)) {
                // exceptions may not be serializable if in the call stack somewhere is a Closure
                if ($rawSql instanceof \Exception || $rawSql instanceof \Throwable) {
                    $rawSql = (string)$rawSql;
                } else {
                    $rawSql = VarDumper::export($rawSql);
                }
            }
            if (strpos($rawSql, "INSERT INTO `log`") !== false) {
                continue;
            }
            $moduleName = isset($message[4][$key]['file']) ?
                self::extractModuleName($message[4][$key]['file']) :
                self::getDbAndTableName($rawSql)['databaseName'];;
            if ($command->bindValues([
                    ':type' => self::getQueryType($rawSql, true),
                    ':module_name' => $moduleName,
                    ':entity_name' => isset($message[4]) ? self::getCalledFile($message[4]) : null,
                    ':event_operation' => self::getQueryType($rawSql, false),
                    ':event_name' => isset($message[4]) ? self::getCalledMethod($message[4]) : null,
                    ':entity_data' => self::extractQueryParams($rawSql),
                    ':level' => $level,
                    ':category' => $category,
                    ':log_time' => $timestamp,
                    ':prefix' => $this->getMessagePrefix($message),
                    ':raw_sql' => $rawSql,
                    ':added_by' => $userID,
                ])->execute() > 0) {
                continue;
            }
            throw new LogRuntimeException('Unable to export log through database!');
        }
    }

    /**
     * Check if an SQL query string is a SELECT, UPDATE, INSERT, or DELETE query
     * @param string $query
     * @param bool $asInt
     * @return string|null
     */
    private function getQueryType(string $query, bool $asInt)
    {
        $query = trim($query);
        $queryType = null;

        // Regular expressions to match query types
        $selectPattern = '/^SELECT\b/i';
        $updatePattern = '/^UPDATE\b/i';
        $insertPattern = '/^INSERT\b/i';
        $deletePattern = '/^DELETE\b/i';

        if (preg_match($selectPattern, $query)) {
            $queryType = $asInt ? 2 : 'SELECT';
        } elseif (preg_match($updatePattern, $query)) {
            $queryType = $asInt ? 1 : 'UPDATE';
        } elseif (preg_match($insertPattern, $query)) {
            $queryType = $asInt ? 1 : 'INSERT';
        } elseif (preg_match($deletePattern, $query)) {
            $queryType = $asInt ? 1 : 'DELETE';
        }

        return $queryType;
    }

    /**
     * Extract the database and table names
     * @param string $query
     * @return array
     */
    private function getDbAndTableName(string $query)
    {
        $query = trim($query);
        $databaseName = null;
        $tableName = null;

        // Regular expressions to match query db and table
        $tablePattern = "/(?<=INSERT INTO|FROM|JOIN|UPDATE)\s*[`']([a-zA-Z0-9_]+)[`']\.`([a-zA-Z0-9_]+)`/";

        preg_match($tablePattern, $query, $_matches);
        if (isset($_matches[0])) {
            $matches = explode('.', trim($_matches[0]));
            $databaseName = str_replace('ecf_', '', trim($matches[0], "'`"));
            $tableName = trim($matches[1], "'`");
        }

        return [
            'databaseName' => $databaseName,
            'tableName' => $tableName
        ];
    }

    /**
     * Extract the request trace details
     * @param array $traceDetails
     * @return mixed|null
     */
    private function getCalledMethod(array $traceDetails)
    {
        $_traceDetails = null;
        foreach ($traceDetails as $traces) {
            foreach ($traces as $key => $trace) {
                if ($key !== 'function')
                    continue;

                if (empty($_traceDetails))
                    $_traceDetails = $trace;
                else
                    $_traceDetails .= "\n $trace";
            }
        }
        return $_traceDetails;
    }

    /**
     * Extracts the module name from the file path
     * @param $filePath
     * @return mixed|string
     */
    private function extractModuleName($filePath)
    {
        $pathParts = array_slice(explode('/', $filePath), -3, 1);
        return $pathParts[0];
    }

    /**
     * Extract the request trace details
     * @param array $traceDetails
     * @return mixed|null
     */
    private function getCalledFile(array $traceDetails)
    {
        $_traceDetails = null;
        foreach ($traceDetails as $traces) {
            foreach ($traces as $key => $trace) {
                if ($key !== 'file')
                    continue;
                $classNamePath = explode('/', $trace);
                if (empty($_traceDetails)) {
                    $_traceDetails = end($classNamePath);
                } else {
                    $_traceDetails .= "\n" . end($classNamePath);
                }
            }
        }
        return $_traceDetails;
    }

    /**
     * @param $sqlQuery
     * @return array|false|string
     */
    private function extractQueryParams($sqlQuery)
    {
        $queryParams = array();

        // SELECT query
        if (preg_match('/SELECT.*?\bWHERE\b(.*)/i', $sqlQuery, $matches)) {
            $queryParams = self::extractKeyValuePairs($matches[1]);
        } // UPDATE query
        elseif (preg_match('/UPDATE.*?\bSET\b(.*?)\bWHERE\b/i', $sqlQuery, $matches)) {
            $queryParams = self::extractKeyValuePairs($matches[1]);
        } // INSERT query{
        elseif (preg_match('/INSERT INTO.*?\((.*)\).*?\bVALUES\b\s*\((.*)\)/i', $sqlQuery, $matches)) {
            $queryParams = self::combineKeyValuesInsertSqlData($matches[1], $matches[2]);
        }
        return $queryParams;
    }

    /**
     * @param $paramString
     * @return false|string
     */
    private function extractKeyValuePairs($paramString)
    {
        $params = array();
        preg_match_all('/(`[^`]+`)\s*=\s*([^,]+)/', $paramString, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $paramName = trim($match[1], '`');
            $paramValue = str_replace(["'", ' '], '', $match[2]);
            $params[$paramName] = $paramValue;
        }
        return json_encode($params);
    }

    /**
     * @param $keys
     * @param $values
     * @return false|string
     */
    private function combineKeyValuesInsertSqlData($keys, $values)
    {
        $_keys = explode(',', str_replace(['`', ' '], '', $keys));
        $_values = explode(',', str_replace(["'", ' '], '', $values));
        return json_encode(array_combine($_keys, $_values));
    }
}
