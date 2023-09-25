<?php

namespace api\models;

use yii\db\Query;

/**
 * This is the model class for table "project".
 */
class Project extends ProjectParent
{
    public static $names = [];
    public static $namesAuto = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_PMP . '.project';
    }

    public static function setNames()
    {
        self::$names = [];
        $models = self::find()->select("id, name")->where('deleted = 0')->all();
        if (!empty($models)) {
            foreach ($models as $model) {
                self::$names[$model->id] = $model->name;
            }
        }
    }

    public static function setNamesAuto()
    {
        $models = (new Query())
            ->from('ecf_auto.project')
            ->where(['deleted' => 0])
            ->all();
        foreach ($models as $model) {
            self::$namesAuto[$model['id']] = $model['name'];
        }
    }
}
