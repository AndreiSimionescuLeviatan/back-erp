<?php

namespace backend\modules\crm\models;

use Yii;

/**
 * This is the model class for all modules models
 *
 */
class CrmActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_crm_db');
    }

    /**
     * @param $sql
     * @return array|\yii\db\DataReader
     * @throws \yii\db\Exception
     */
    public static function queryAll($sql)
    {
        $conn = self::getDb();
        return $conn->createCommand($sql)->queryAll();
    }

    public static function execute($sql)
    {
        $conn = self::getDb();
        return $conn->createCommand($sql)->execute();
    }

    public static function findAllByAttributes($attributes, $options = [])
    {
        $className = get_called_class();
        $model = new $className();
        $where = '';
        $bind = [];
        foreach ($attributes as $attribute => $value) {
            if (!$model->hasAttribute($attribute)) {
                continue;
            }
            if (!empty($where)) {
                $where .= ' AND ';
            }
            $where .= "{$attribute} = :{$attribute}";
            $bind[":{$attribute}"] = $value;
        }

        $response = [];
        if (!empty($attributes) && empty($where)) {
            return $response;
        }

        $command = self::find()->where($where, $bind);
        if (!empty($options['order'])) {
            $command->orderBy($options['order']);
        }
        if (!empty($options['limit'])) {
            $command->limit($options['limit']);
        }
        if (!empty($options['as_array'])) {
            $command->asArray();
        }

        return $command->all();
    }

    public static function findOneByAttributes($attributes, $options = [])
    {
        $className = get_called_class();
        $model = new $className();
        $where = '';
        $bind = [];
        foreach ($attributes as $attribute => $value) {
            if (!$model->hasAttribute($attribute)) {
                continue;
            }
            if (!empty($where)) {
                $where .= ' AND ';
            }
            $where .= "{$attribute} = :{$attribute}";
            $bind[":{$attribute}"] = $value;
        }

        $response = null;
        if (!empty($attributes) && empty($where)) {
            return $response;
        }

        $command = self::find()->where($where, $bind);
        if (!empty($options['order'])) {
            $command->orderBy($options['order']);
        }
        if (!empty($options['as_array'])) {
            $command->asArray();
        }

        return $command->one();
    }

    public static function getByAttributes($attributes, $createAttributes = [])
    {
        $model = self::findOneByAttributes($attributes);
        if ($model !== null) {
            return $model;
        }

        if (!empty($createAttributes)) {
            try {
                return self::createByAttributes($createAttributes);
            } catch (Exception $exc) {
                throw new Exception($exc->getMessage(), $exc->getCode());
            }
        }
        return null;
    }

    public static function createByAttributes($attributes)
    {
        $className = get_called_class();
        $model = new $className();
        foreach ($attributes as $attribute => $value) {
            if (!$model->hasAttribute($attribute)) {
                continue;
            }
            $model->$attribute = $value;
        }
        if (empty($model->added_by)) {
            $model->added_by = Yii::$app->params['superAdmin'];
        }
        if (empty($model->added)) {
            $model->added = date('Y-m-d H:i:s');
        }
        if (!$model->save()) {
            if ($model->hasErrors()) {
                foreach ($model->errors as $error) {
                    throw new \Exception(Yii::t('finance', $error[0]));
                }
            }
        }
        return $model;
    }

    public function updateByAttributes($attributes)
    {
        foreach ($attributes as $attribute => $value) {
            if (!$this->hasAttribute($attribute)) {
                continue;
            }
            $this->$attribute = $value;
        }
        if (empty($model->updated_by)) {
            $this->updated_by = Yii::$app->params['superAdmin'];
        }
        if (empty($model->updated)) {
            $this->updated = date('Y-m-d H:i:s');
        }

        if ($this->save()) {
            return true;
        }

        return false;
    }

    public static function countByAttributes($attributes)
    {
        $className = get_called_class();
        $model = new $className();
        $where = '';
        $bind = [];
        foreach ($attributes as $attribute => $value) {
            if (!$model->hasAttribute($attribute)) {
                continue;
            }
            if (!empty($where)) {
                $where .= ' AND ';
            }
            $where .= "{$attribute} = :{$attribute}";
            $bind[":{$attribute}"] = $value;
        }

        if (!empty($attributes) && empty($where)) {
            return 0;
        }

        return self::find()->where($where, $bind)->count();
    }

    public static function sendPOST($url, $postData = '', $port = 80)
    {
        if (!is_callable('curl_init')) {
            throw new Exception('Curl is not installed. Please install it before running the controller.');
        }
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYHOST => 0, // don't verify SSL
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => 1, // return the transfer as a string instead of outputting it out directly
            CURLOPT_HEADER => 0, // don't return headers
            CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
            CURLOPT_TIMEOUT => 120, // timeout on response
            CURLOPT_FOLLOWLOCATION => 1, // follow redirects
            CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
            CURLOPT_AUTOREFERER => 1, // set referer on redirect
            CURLOPT_ENCODING => '', // handle all encodings
            CURLOPT_VERBOSE => 0,
            CURLOPT_POST => 1, // send POST
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_PORT => $port
        ));
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);

        if ($err != 0) {
            $message = 'No error message returned back.';
            if (!empty($errmsg)) {
                $message = $errmsg;
            }
            throw new Exception($message);
        }

        if (empty($content)) {
            throw new Exception('No response from the server.');
        }

        $content = json_decode($content, true);
        if ($content === null) {
            throw new Exception('Error decoding the response from the server. Content: ' . $content);
        }

        return $content;
    }

    /**
     * @return void
     * @param $conditions[] = ['condition' 'column', 'value']
     * @param $relatedTables[] = [
     *            'name' => 'table_name',
     *            'column' => 'project_id',
     *            'on_column' => 'self.id',
     *        ]
     */
    public static function setAllNames($conditions = [['deleted' => 0]], $relatedTables = [])
    {
        $className = get_called_class();
        $className::$names = $className::getList($conditions, ['key' => 'id', 'value' => 'name'], $relatedTables);
    }

    /**
     * @return void
     * @param $conditions[] = ['condition' 'column', 'value']
     * @param $relatedTables[] = [
     *            'name' => 'table_name',
     *            'column' => 'project_id',
     *            'on_column' => 'self.id',
     *        ]
     */
    public static function setAllCodes($conditions = [['deleted' => 0]], $relatedTables = [])
    {
        $className = get_called_class();
        $className::$codes = $className::getList($conditions, ['key' => 'id', 'value' => 'code'], $relatedTables);
    }

    /**
     * @return void
     * @param $conditions[] = ['condition' 'column', 'value']
     * @param $relatedTables[] = [
     *            'name' => 'table_name',
     *            'column' => 'project_id',
     *            'on_column' => 'self.id',
     *        ]
     */
    public static function setIds($conditions = [['deleted' => 0]], $relatedTables = [])
    {
        $className = get_called_class();
        $className::$ids = $className::getList($conditions, ['key' => 'id', 'value' => 'id'], $relatedTables);
    }

    /**
     * @return array
     * @param $conditions[] = ['condition' 'column', 'value']
     * @param $attributes = ['key' => 'id', 'value' => 'name']
     * @param $relatedTables[] = [
     *            'name' => 'table_name',
     *            'column' => 'project_id',
     *            'on_column' => 'self.id',
     *        ]
     */
    public static function getList($conditions = [['deleted' => 0]], $attributes = ['key' => 'id', 'value' => 'id'], $relatedTables = [])
    {
        $className = get_called_class();
        $model = new $className;
        $tableName = $attributes['tableName'] ?? 'self';
        $value = $attributes['valueSQL'] ?? "{$tableName}.{$attributes['value']}";
        $key = $attributes['keySQL'] ?? "{$tableName}.{$attributes['key']}";

        $query = $model->find()
            ->alias('self')
            ->select(["{$key} AS {$attributes['key']}", "{$value} AS {$attributes['value']}"])
            ->orderBy([$value => SORT_ASC]);

        if (!empty($attributes['group_by'])) {
            $query->groupBy($attributes['group_by']);
            if (!empty($attributes['having'])) {
                $query->having($attributes['having']);
            }
        }
        if (!empty($attributes['as_array'])) {
            $query->asArray();
        }

        $list = [];

        if (
            !isset($attributes['key'])
            && !isset($attributes['value'])
        ) {
            return $list;
        }

        if (
            is_array($conditions)
            && count($conditions) > 0
        ) {
            foreach ($conditions as $condition) {
                $query->andWhere($condition);
            }
        }

        if (!empty($relatedTables)) {
            foreach ($relatedTables as $table) {
                if (
                    isset($table['name'])
                    && isset($table['column'])
                    && isset($table['on_column'])
                ) {
                    $tblName = $table['name'];
                    $joinType = $table['join_type'] ?? 'INNER JOIN';
                    $query->join("{$joinType}", "{$tblName}", "{$tblName}.{$table['column']} = {$table['on_column']}");
                }
            }
        }

        $models = $query->all();

        foreach ($models as $model) {
            $key = $model[$attributes['key']];
            $value = $model[$attributes['value']];

            if (!empty($attributes['depDrop'])) {
                $list[] = ['id' => $key, 'name' => $value];
                continue;
            }

            $list[$key] = $value;
        }

        return $list;
    }

}
