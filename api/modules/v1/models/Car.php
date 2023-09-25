<?php

namespace api\modules\v1\models;


class Car extends \api\models\Car
{
    /**
     * @return string|null
     */
    public static function getUserOperation()
    {
        $carModel = Car::find()
            ->where(['status' => CarOperation::CAR_CHECK_OUT, 'user_id' => Yii::$app->user->id])
            ->orWhere(['status' => CarOperation::CAR_CHECK_IN, 'user_id' => Yii::$app->user->id])
            ->orderBy(['added' => SORT_DESC])
            ->one();
        if (!empty($carModel)) {
            return $carModel->status == CarOperation::CAR_CHECK_IN ? 'check_in' : 'check_out';
        }
        return null;
    }
}
