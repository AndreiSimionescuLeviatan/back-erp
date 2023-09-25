<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "zone".
 */
class Zone extends ZoneParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_AUTO . '.zone';
    }

    /**
     * @return array
     */
    public static function getZonesIds()
    {
        $models = self::find()->where("deleted = 0")->all();

        $zones = [];
        foreach ($models as $model) {
            $zones[] = $model->id;
        }
        return $zones;
    }



    public function getCarZone()
    {
        return $this->hasOne(CarZone::className(), ['zone_id' => 'id']);
    }
}
