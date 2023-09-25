<?php

namespace backend\modules\adm\models;

class Subdomain extends SubdomainParent
{
    /**
     * The fuel list that will contain mapped fuels in form of key=>value
     * This var is populated when setNames function is called
     * @var array
     */
    public static $names = [];

    /**
     * Populates the above $names with id as key and name as value.
     * We created this function because is more sql optimized
     * Will use this instead of ArrayHelper::map(Subdomain::....., 'id', 'name') and also on other places where possible
     * @author Cornel E.
     * @since 05/04/2022
     */
    public static function setNames()
    {
        self::$names = [];
        $models = self::find()->where(['deleted' => 0])->orderBy('name')->all();
        foreach ($models as $model) {
            self::$names[$model->id] = $model->name;
        }
    }
}