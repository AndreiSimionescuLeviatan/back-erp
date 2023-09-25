<?php

namespace backend\modules\entity\models;

use Yii;

class EntityActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_entity_db');
    }

    /**
     * @param $sql
     * @return array|\yii\db\DataReader
     * @throws \yii\db\Exception
     */
    public static function queryAll($sql)
    {
        return self::getDb()->createCommand($sql)->queryAll();
    }

    public static function queryOne($sql)
    {
        return self::getDb()->createCommand($sql)->queryOne();
    }

    public static function queryScalar($sql)
    {
        return self::getDb()->createCommand($sql)->queryScalar();
    }

    public static function execute($sql)
    {
        return self::getDb()->createCommand($sql)->execute();
    }

    public static function findByAttributes($attributes, $all = false)
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
        if ($all) {
            $response = [];
        }
        if (!empty($attributes) && empty($where)) {
            return $response;
        }

        if ($all) {
            return self::find()->where($where, $bind)->all();
        }
        return self::find()->where($where, $bind)->one();
    }

    public static function getByAttributes($attributes, $createAttributes = [])
    {
        $model = self::findByAttributes($attributes);
        if ($model !== null) {
            return $model;
        }

        if (!empty($createAttributes)) {
            return self::createByAttributes($createAttributes);
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
        $model->added = date('Y-m-d H:i:s');
        if (empty($model->added_by)) {
            $model->added_by = Yii::$app->params['superAdmin'];
        }
        if (!$model->save()) {
            if ($model->hasErrors()) {
                foreach ($model->errors as $error) {
                    throw new \Exception(Yii::t('windoc', $error[0]));
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

    public static function setAllNames($conditions = [['deleted' => 0]], $relatedTables = [])
    {
        $className = get_called_class();
        $className::$names = $className::getList($conditions, ['key' => 'id', 'value' => 'name'], $relatedTables);
    }

    public static function setAllCodes($conditions = [['deleted' => 0]], $relatedTables = [])
    {
        $className = get_called_class();
        $className::$codes = $className::getList($conditions, ['key' => 'id', 'value' => 'code'], $relatedTables);
    }

    /**
     * @param $conditions [] = [['deleted' => 0]]
     * @param $attributes = ['key' => 'id', 'value' => 'name', 'tableName' => 'self']
     * @param $relatedTables [] = [
     *            'name' => 'table_name',
     *            'column' => 'column_id',
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

        $query = $model->find()
            ->alias('self')
            ->select("{$tableName}.{$attributes['key']}, {$value} AS {$attributes['value']}")
            ->orderBy("{$value}");

        if (!empty($attributes['group_by'])) {
            $query->groupBy($attributes['group_by']);
        }

        $list = [];

        if (
            !isset($attributes['key'])
            && !isset($attributes['value'])
        ) {
            return $list;
        }

        $query = self::getQueryWithCondition($query, $conditions);

        if (!empty($relatedTables)) {
            foreach ($relatedTables as $table) {
                if (
                    isset($table['name'])
                    && isset($table['column'])
                    && isset($table['on_column'])
                ) {
                    $tblName = $table['name'];
                    $query->join('INNER JOIN', "{$tblName}", "{$tblName}.{$table['column']} = {$table['on_column']}");
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

    public static function getQueryWithCondition($query, $conditions)
    {
        if (
            is_array($conditions)
            && count($conditions) > 0
        ) {
            foreach ($conditions as $condition) {
                $query->andWhere($condition);
            }
        }

        return $query;
    }

    public static function getFilter($attributes, $operator = 'AND')
    {
        $condition = '';
        foreach ($attributes as $attribute => $value) {
            if (empty($value)) {
                continue;
            }
            if (!empty($condition)) {
                $condition .= " {$operator} ";
            }
            $condition .= "{$attribute} = {$value}";
        }
        return $condition;
    }

}