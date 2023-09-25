<?php

namespace api\models;

use Yii;

class LocationName extends LocationNameParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_AUTO . '.location_name';
    }

    public static function getLocationName($locationId)
    {
        $locationName = self::findOneByAttributes([
            'location_id' => $locationId,
            'deleted' => 0,
            'user_id' => Yii::$app->user->id
        ]);
        if (!empty($locationName)) {
            return $locationName->location_new_name;
        }
        return null;
    }
}