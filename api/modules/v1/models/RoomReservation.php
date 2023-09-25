<?php

namespace api\modules\v1\models;

use RRule\RSet;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "room_reservation".
 */
class RoomReservation extends RoomReservationParent
{

    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_LOGISTIC . '.room_reservation';
    }

    public function rules()
    {
        $rules = parent::rules();
        return ArrayHelper::merge($rules, [
            [['all_day', 'recurring'], 'default', 'value' => 0],
            ['recurrence_interval', 'default', 'value' => 1]
        ]);
    }

    public function update($runValidation = true, $attributeNames = null)
    {
        $this->scenario = 'update';
        return parent::update($runValidation, $attributeNames);
    }

    /**
     * @param $rfcString
     * @param $exDate
     * @return string
     */
    public static function addExclusionToRRUleString($rfcString, $exDate)
    {
        $rSet = new RSet($rfcString);
        $rSet->addExDate($exDate);
        $exDates = $rSet->getExDates();
        $newRfcRule = '';
        foreach ($rSet->getRRules() as $rrule) {
            $newRfcRule .= $rrule->rfcString() . PHP_EOL;
        }
        if (count($exDates)) {
            $newRfcRule .= 'EXDATE;';
            foreach ($exDates as $key => $date) {
                if ($key === 0) {
                    $newRfcRule .= 'TZID=' . $date->format('e') . ':' . $date->format('Ymd\THis') . ',';
                } else {
                    $newRfcRule .= $date->format('Ymd\THis') . ',';
                }
            }
            $newRfcRule = rtrim($newRfcRule, ',');
            $newRfcRule .= PHP_EOL;
        }
        return $newRfcRule;
    }

}
