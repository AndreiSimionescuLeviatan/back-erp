<?php

namespace backend\modules\location\models;

use Yii;

/**
 * This is the model class for all modules models
 *
 */
class LocationActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_location_db');
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

    public static function getByAttributes($attributes, $createAttributes = [])
    {
        $model = self::findOneByAttributes($attributes);

        if ($model === null && $createAttributes) {
            try {
                $model = self::createByAttributes($createAttributes);
            } catch (\Exception $exc) {
                throw new \Exception($exc->getMessage(), $exc->getCode());
            }

        }
        return $model;
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
        if (!empty($options['group_by'])) {
            $command->groupBy($options['group_by']);
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
                    throw new \Exception(Yii::t('build', $error[0]));
                }
            }
        }
        return $model;
    }

    /**
     * @param $conditions [] = ['condition' 'column', 'value']
     * @param $relatedTables [] = [
     *            'name' => 'table_name',
     *            'column' => 'project_id',
     *            'on_column' => 'self.id',
     *        ]
     * @return void
     */
    public static function setAllNames($conditions = [['deleted' => 0]], $relatedTables = [])
    {
        $className = get_called_class();
        $className::$names = $className::getList($conditions, ['key' => 'id', 'value' => 'name'], $relatedTables);
    }


    /**
     * @param $conditions [] = ['condition' 'column', 'value']
     * @param $relatedTables [] = [
     *            'name' => 'table_name',
     *            'column' => 'project_id',
     *            'on_column' => 'self.id',
     *        ]
     * @return void
     */
    public static function setAllCodes($conditions = [['deleted' => 0]], $relatedTables = [])
    {
        $className = get_called_class();
        $className::$codes = $className::getList($conditions, ['key' => 'id', 'value' => 'code'], $relatedTables);
    }

    /**
     * @param $conditions [] = ['condition' 'column', 'value']
     * @param $relatedTables [] = [
     *            'name' => 'table_name',
     *            'column' => 'project_id',
     *            'on_column' => 'self.id',
     *        ]
     * @return void
     */
    public static function setIds($conditions = [['deleted' => 0]], $relatedTables = [])
    {
        $className = get_called_class();
        $className::$ids = $className::getList($conditions, ['key' => 'id', 'value' => 'id'], $relatedTables);
    }

    /**
     * @param $conditions [] = ['condition' 'column', 'value']
     * @param $attributes = ['key' => 'id', 'value' => 'name']
     * @param $relatedTables [] = [
     *            'name' => 'table_name',
     *            'column' => 'project_id',
     *            'on_column' => 'self.id',
     *        ]
     * @return array
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
            ->select("{$key} AS {$attributes['key']}, {$value} AS {$attributes['value']}")
            ->orderBy("{$value}");

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