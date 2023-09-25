<?php

namespace api\modules\v2\models;

use api\modules\v1\models\MailHelper;
use backend\modules\adm\models\Settings;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

/**
 * This is the model class for table "meeting_external_attendee".
 */
class MeetingExternalAttendee extends MeetingExternalAttendeeParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_LOGISTIC . '.meeting_external_attendee';
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['email_address'], 'email'],
        ]);
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
    public static function addOrRemoveExternalAttenders($reservationCommonIdentifier, $existingAttenders, $receivedAttendersList, $toAddList, $toRemoveList)
    {
        if (!empty($toAddList)) {
            foreach ($receivedAttendersList as $newAttender) {
                if (!in_array($newAttender, $existingAttenders)) {
                    $attender = new self();
                    $attender->meeting_common_identifier = $reservationCommonIdentifier;
                    $attender->email_address = $newAttender;

                    if (!$attender->save()) {
                        if ($attender->hasErrors()) {
                            foreach ($attender->errors as $error) {
                                throw new HttpException(409, $error[0]);
                            }
                        }
                        throw new HttpException(500, Yii::t('api-logistic', 'Failed to update meeting. One external attender could not be added. Please contact an administrator!'));
                    }
                }
            }
        }
        if (!empty($toRemoveList)) {
            $toRemoveAttenders = self::find()
                ->where([
                    'email_address' => $toRemoveList,
                    'meeting_common_identifier' => $reservationCommonIdentifier
                ])->all();

            if (!empty($toRemoveAttenders)) {
                foreach ($toRemoveAttenders as $attender) {
                    $attender->delete();
                }
            }
        }
    }

    /**
     * Sends an email to external participant
     * No CC because the user will see this details
     * If requested a BCC || ReplayTo will be set if Outlook allow us
     * @param RoomReservation $reservation
     * @param array $attenders
     * @param string $roomName
     * @return string
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public static function notifyExternalAttenders($reservation, $attenders, $roomName = "")
    {
        if ($roomName === "") {
            $roomDetails = MeetingRoom::findOne($reservation->room_id);
            $roomName = $roomDetails->name;
        }
        $reservationDate = $reservation->recurrent_from;
        $post['subject'] = $reservation->title;
        $mailBody = "Bună ziua,<br>";
        $mailBody .= "Sunteți așteptat în data de <b>{$reservationDate}</b> la Sediul din Timpuri Noi pentru întâlnirea <b>{$reservation->title}</b> care va avea loc în <b>{$roomName}</b>.<br><br>";
        $mailBody .= "Mulțumim";

        $post['to'] = implode(",", $attenders);
        $post['content'] = $mailBody;
        $mailNotification = new MailHelper();
        return $mailNotification->sendEmailNotification($post);
    }
}
