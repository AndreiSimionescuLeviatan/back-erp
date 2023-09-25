<?php

namespace api\modules\v1\models;

use Yii;
use yii\web\HttpException;

/**
 * This is the model class for table "meeting_recurrence".
 */
class MeetingRecurrence extends MeetingRecurrenceParent
{
    public $reservationDetails;
    public $rRule;

    /**
     * {@inheritdoc}
     */

    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_LOGISTIC . '.meeting_recurrence';
    }

    public function saveMultipleRecurence()
    {
        $this->meeting_id = $this->reservationDetails->id;
        $this->added = date('Y-m-d H:i:s');
        $this->added_by = Yii::$app->user->id;

        if ($this->reservationDetails->recurring == 1) {
            $evtDuration = strtotime($this->check_in) - strtotime($this->check_out);
            foreach ($this->rRule as $event) {
                $evtCheckIn = $event->format('Y-m-d H:i');
                $evtCheckOut = date('Y-m-d H:i', $event->getTimestamp() + $evtDuration);

                $this->setIsNewRecord(true);
                $this->id = null;
//                $this->check_in = $evtCheckIn;
//                $this->check_out = $evtCheckOut;
                $this->recurrence_date = date('Y-m-d', strtotime($evtCheckIn));

                if (!$this->save()) {
                    if ($this->hasErrors()) {
                        foreach ($this->errors as $error) {
                            throw new HttpException(409, $error[0]);
                        }
                    }
                    throw new HttpException(500, Yii::t('api-logistic', 'Failed to create meeting recurrence. Please contact an administrator!'));
                }
            }
        } else {
            if (!$this->save()) {
                if ($this->hasErrors()) {
                    foreach ($this->errors as $error) {
                        throw new HttpException(409, $error[0]);
                    }
                }
                throw new HttpException(500, Yii::t('api-logistic', 'Failed to create meeting recurrence. Please contact an administrator!'));
            }
        }
    }
}
