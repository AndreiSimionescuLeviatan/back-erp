<?php

namespace api\modules\v2\models;

use yii\behaviors\AttributeTypecastBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "room_reservation".
 */
class RoomReservation extends RoomReservationParent
{

    public function rules()
    {
        $rules = parent::rules();
        return ArrayHelper::merge($rules, [
            [['outlook_id'], 'required', 'on' => 'update'],
            [['all_day', 'recurring'], 'default', 'value' => 0],
            [['details'], 'default', 'value' => null],
            ['recurrence_interval', 'default', 'value' => 1],
            ['recurrence_type', 'required', 'when' => function ($model) {
                return $model->recurring == 1;
            }]
        ]);
    }

    public function behaviors()
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'typecastAfterValidate' => true
            ]
        ];
    }
}
