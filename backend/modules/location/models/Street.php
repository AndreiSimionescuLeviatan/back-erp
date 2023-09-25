<?php

namespace backend\modules\location\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Street extends StreetParent
{
    public $state_id;
    public $country_id;

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['added'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated'],
                ],
                'value' => date('Y-m-d H:i:s'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'added_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => Yii::t('location', 'Name field'),
            'country_id' => Yii::t('location', 'Country field'),
            'state_id' => Yii::t('location', 'State field'),
            'city_id' => Yii::t('location', 'City field'),
        ];
    }

    public function initParams()
    {
        $city = City::findOneByAttributes(['id' => $this->city_id]);
        if ($city === null) {
            return;
        }

        $this->state_id = $city->state_id;
        $this->country_id = $city->state->country_id;
    }

}