<?php

namespace backend\modules\entity\models;

use Yii;

class Domain extends DomainParent
{
    public static $names = [];

    public static function setNames()
    {
        self::$names = [];
        $models = self::find()->where(['deleted' => 0])->orderBy('name')->all();
        foreach ($models as $model) {
            self::$names[$model->id] = $model->description;
        }
    }
}
