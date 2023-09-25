<?php

namespace api\models;

use Yii;

class ApiActiveRecord extends \yii\db\ActiveRecord
{
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
        $model->added_by = Yii::$app->params['superAdmin'];
        if (!$model->save()) {
            if ($model->hasErrors()) {
                foreach ($model->errors as $error) {
                    throw new \Exception(Yii::t('app', $error[0]));
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
}
