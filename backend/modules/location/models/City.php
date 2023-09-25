<?php

namespace backend\modules\location\models;

use backend\modules\adm\models\User;
use backend\modules\crm\models\Company;
use Yii;

/**
 * This is the model class that extends the "CityParent" class.
 * 
 * @property Company[] $companies
 */
class City extends CityParent
{
    public static $userAdded = [];
    public static $userUpdated = [];
    public static $names = [];

    /**
     * Filter cities by the user who added
     * @return void
     * @since 30/06/2022
     * @author Anca P.
     */
    public static function setUserAdded()
    {
        self::$userAdded = [];

        $models = City::find()->distinct('added_by')->where(['deleted' => 0])->all();
        foreach ($models as $model) {
            self::$userAdded[$model->added_by] = isset(User::$users[$model->added_by]) ? User::$users[$model->added_by] : '-';
        }
    }

    /**
     * Filter cities by the user who updated
     * @return void
     * @since 30/06/2022
     * @author Anca P.
     */
    public static function setUserUpdated()
    {
        self::$userUpdated = [];

        $models = City::find()->distinct('updated_by')->where(['deleted' => 0])->all();
        foreach ($models as $model) {
            self::$userUpdated[$model->updated_by] = $model->updated_by ? (isset(User::$users[$model->updated_by]) ? User::$users[$model->updated_by] : '-') : null;
        }
    }

    /**
     * Set the name's cities
     * @since 16.08.2022
     */
    public static function setNames()
    {
        self::$names = [];
        $models = self::find()->where(['deleted' => 0])->orderBy('name')->all();
        foreach ($models as $model) {
            self::$names[$model->id] = $model->name;
        }
    }

    /**
     * Gets query for [[Companies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompanies()
    {
        return $this->hasMany(Company::className(), ['city_id' => 'id']);
    }
}
