<?php

namespace common\components;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use Yii;
use yii\web\HttpException;

class DateTimeHelper
{

    /**
     * @return string|null
     * @throws Exception
     * @author Daniel L.
     * @since 02/08/2022
     * This function turning seconds in a duration format like: "2 hours and 30 minutes"
     */
    public static function getDuration($seconds)
    {
        $duration = null;
        if (!empty($seconds)) {
            $formatTime = gmdate("H:i", $seconds);
            $hours = number_format(explode(':', $formatTime)[0]);
            $minutes = number_format(explode(':', $formatTime)[1]);
            if ($hours == 1) {
                $duration = Yii::t('app', ' One hour');
            } elseif ($hours > 1) {
                $duration .= $hours;
                $duration .= !empty($hours) && $hours > 19 ? ' ' . Yii::t('app', 'of') . ' ' : ' ';
                $duration .= Yii::t('app', 'hours');
            }

            if ($minutes == 1) {
                $duration .= !empty($hours) && $hours !== '00' ? ' ' . Yii::t('app', 'and') . ' ' : '';
                $duration .= Yii::t('app', 'One minute');
            } elseif ($minutes > 1) {
                $duration .= !empty($hours) && $hours !== '00' ? ' ' . Yii::t('app', 'and') . ' ' : '';
                $duration .= $minutes;
                $duration .= !empty($minutes) ? ' ' : '';
                $duration .= 'min';
            }

            if (empty($duration)) {
                $duration = Yii::t('app', 'Less than a minute');
            }
        }
        return $duration;
    }

    /**
     * Get da difference between a start and a stop time in requested format
     * @param $start | A date/time string. Valid formats are explained in Date and Time Formats
     * @param $stop | A date/time string. Valid formats are explained in Date and Time Formats
     * @param $format | A format string. Valid formats are explained in Date and Time Formats
     * @return string
     * @throws HttpException
     * @throws Exception
     */
    public static function getTimeDifference($start, $stop, $format)
    {
        $_start = date_parse($start);
        $_stop = date_parse($start);
        if ($_start['error_count'] >= 1
            || $_start['warning_count'] >= 1
            || $_stop['error_count'] >= 1
            || $_stop['warning_count'] >= 1
        ) {
            throw new HttpException(400, Yii::t('app', 'The start, stop values or both, are not valid date/time'));
        }

        $start_datetime = new DateTime($start);
        $diff = $start_datetime->diff(new DateTime($stop));
        return $diff->format($format);
    }

    /**
     * Count the days between two dates, by default, it counts also the weekend days, if $excludeWeekends = true it will skip this day's
     * @param $startDate
     * @param $endDate
     * @param $excludeWeekends
     * @return int
     */
    public static function countDaysInInterval($startDate, $endDate, $excludeWeekends = false)
    {
        $interval = new DateInterval('P1D'); // Create a one-day interval
        $period = new DatePeriod($startDate, $interval, $endDate); // Create a period between the start and end date
        $counter = 0;
        foreach ($period as $date) {
            $dayOfWeek = $date->format('N'); // Get the day of the week (1 - Monday, 7 - Sunday)
            if ($excludeWeekends && ($dayOfWeek >= 6)) {
                continue;
            }
            $counter++;
        }

        return $counter;
    }
}
