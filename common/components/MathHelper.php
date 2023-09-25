<?php

namespace common\components;

use Yii;
use yii\web\HttpException;

class MathHelper
{
    /**
     * generate weighed average
     *
     * @param $numbers []
     * @param $percentage []
     * @param $numberOfDecimals
     * @return float|int
     * @throws HttpException
     */
    public static function weightedAverage($numbers, $percentage, $numberOfDecimals)
    {
        if (count($numbers) !== count($percentage)) {
            throw new HttpException(HttpStatus::CONFLICT, Yii::t('app', 'The number of numbers and number of percentage must be the same.'));
        }
        // Calculate the sum of the percentage
        $weightedSum = 0;
        foreach ($numbers as $key => $number) {
            $weightedSum += number_format($number, $numberOfDecimals) * $percentage[$key];
        }

        // Calculate the sum of the percentage
        $totalWeight = array_sum($percentage);

        // Calculate the weighted average
        return number_format($weightedSum / $totalWeight, $numberOfDecimals);
    }
}