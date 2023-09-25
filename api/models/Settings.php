<?php

namespace api\models;

class Settings extends SettingsParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_INITIAL . '.settings';
    }

    /**
     * @param $name
     * @param $defaultValue
     * @return mixed|string|null
     */
    public static function getValue($name, $defaultValue = null)
    {
        $model = self::find()->where('name = :name', [
            ':name' => $name
        ])->one();
        if ($model === null) {
            return $defaultValue;
        }
        return $model->value;
    }
}
