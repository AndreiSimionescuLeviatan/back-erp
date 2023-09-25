<?php

namespace api\modules\v2\models;

use Yii;
use yii\web\HttpException;

/**
 * This is the model class for table "meeting_attendee".
 */
class MeetingAttendee extends MeetingAttendeeParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_LOGISTIC . '.meeting_attendee';
    }

    /**
     * @param string $reservationCommonIdentifier
     * @param array $existingAttenders
     * @param array $receivedAttendersList
     * @param array $toAddList
     * @param array $toRemoveList
     * @return void
     * @throws HttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function addOrRemoveInternalAttenders($reservationCommonIdentifier, $existingAttenders, $receivedAttendersList, $toAddList, $toRemoveList)
    {
        if (!empty($toAddList)) {
            foreach ($receivedAttendersList as $newAttender) {
                if (!in_array($newAttender, $existingAttenders)) {
                    $attender = new MeetingAttendee();
                    $attender->meeting_common_identifier = $reservationCommonIdentifier;
                    $attender->user_id = $newAttender;

                    if (!$attender->save()) {
                        if ($attender->hasErrors()) {
                            foreach ($attender->errors as $error) {
                                throw new HttpException(409, $error[0]);
                            }
                        }
                        throw new HttpException(500, Yii::t('api-logistic', 'Failed to update meeting. One attender could not be added. Please contact an administrator!'));
                    }
                }
            }
        }
        if (!empty($toRemoveList)) {
            $toRemoveAttenders = MeetingAttendee::find()
                ->where([
                    'user_id' => $toRemoveList,
                    'meeting_common_identifier' => $reservationCommonIdentifier
                ])->all();

            if (!empty($toRemoveAttenders)) {
                foreach ($toRemoveAttenders as $attender) {
                    $attender->delete();
                }
            }
        }
    }
}
