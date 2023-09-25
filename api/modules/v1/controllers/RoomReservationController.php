<?php

namespace api\modules\v1\controllers;

use backend\modules\adm\models\Settings;
use Yii;
use api\modules\v1\models\MailHelper;
use api\modules\v1\models\MeetingAttendee;
use api\modules\v1\models\MeetingRecurrence;
use api\modules\v1\models\MeetingRoom;
use api\modules\v1\models\RoomReservation;
use api\modules\v1\models\User;
use DateTime;
use RRule\RRule;
use RRule\RSet;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\HttpException;

/**
 * RoomReservation controller
 */
class RoomReservationController extends RestV1Controller
{
    public $modelClass = 'api\modules\v1\models\RoomReservation';

    /**
     * @return array|mixed
     * @throws InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionCreateMeeting()
    {
        $post = Yii::$app->request->post();
        $attendersIds = !empty($post['attenders_ids']) ? $post['attenders_ids'] : [];
        $reservation = new RoomReservation();
        $transaction = RoomReservation::getDb()->beginTransaction();
        $commonIdentifier = (string)time();

        /**
         * try to create a new rule, if none created something is not ok
         */
        $rrule = new RRule($post['RRule']);
        if (empty($rrule)) {
            $this->return['status'] = 422;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', "Your request could not be processed");
            return $this->return;
        }

        try {
            $reservation->load(Yii::$app->getRequest()->getBodyParams(), '');
            $reservation->common_identifier = $commonIdentifier;
            $reservation->recurrence_weekday = $reservation->recurring == 1 ? implode(",", $post['recurrence_weekday']) : null;
            $reservation->recurrent_from = Yii::$app->request->post('recurrence_date');//@todo set correct date
            $reservation->rfc_string = $rrule->rfcString();
            $reservation->added = date('Y-m-d H:i:s');
            $reservation->added_by = Yii::$app->user->id;


            $start_datetime = new DateTime(Yii::$app->request->post('check_in'));
            $diff = $start_datetime->diff(new DateTime(Yii::$app->request->post('check_out')));
            $reservation->duration = $diff->format('%H:%I');

            if (!$reservation->save()) {
                if ($reservation->hasErrors()) {
                    foreach ($reservation->errors as $error) {
                        throw new HttpException(409, $error[0]);
                    }
                }
                throw new HttpException(500, Yii::t('api-logistic', 'Failed to create meeting. Please contact an administrator!'));
            }

            $meetingName = $reservation->title;
            $roomName = MeetingRoom::findOne($reservation->room_id)->name;
            $meetingDate = date('Y-m-d', strtotime($post['RRule']['dtstart']));
            $meetingTime = date('H:i', strtotime($post['RRule']['dtstart']));

            $mailList = '';
            $cCList = Settings::find()->where(['name' => 'MEETING_ROOM_CC_LIST'])->asArray()->one();
            $cCList = !empty($cCList) && !empty($cCList['value']) ? $cCList['value'] : Yii::$app->params['meetingRoomCcList'];
            $post['subject'] = $reservation->title;
            if (!empty($attendersIds)) {
                $userFullName = Yii::$app->user->identity->fullName();
                $mailBody = "{$userFullName} a rezervat <b>{$roomName}</b> pentru ședinta <b>{$meetingName}</b> care va avea loc în data de {$meetingDate} la ora {$meetingTime}.";
                foreach ($attendersIds as $attenderId) {
                    $attender = new MeetingAttendee();
                    $attender->meeting_common_identifier = $commonIdentifier;
                    $attender->user_id = $attenderId;

                    if (!$attender->save()) {
                        if ($attender->hasErrors()) {
                            foreach ($attender->errors as $error) {
                                throw new HttpException(409, $error[0]);
                            }
                        }
                        throw new HttpException(500, Yii::t('api-logistic', 'Failed to create meeting. One attender could not be added. Please contact an administrator!'));
                    }
                    $userEmail = User::find()->select('email')->where("id = {$attenderId}")->scalar();
                    if (!empty($userEmail)) {
                        $mailList .= $userEmail . ',';
                    }
                }

                $post['to'] = rtrim($mailList, ',');
                $post['cc'] = rtrim($cCList, ',');
            } else {
                $mailBody = "Ai fost adăugat în următorul meeting <b>{$meetingName}</b> care va avea loc în <b>{$roomName}</b> în data de {$meetingDate} la ora {$meetingTime}.";
                $post['to'] = rtrim($cCList, ',');
            }
            $post['content'] = $mailBody;
            $mailNotification = new MailHelper();
            $mailNotification->sendEmailNotification($post);

            $this->return['message'] = Yii::t('api-logistic', 'Successfully saved the meeting details');
            $transaction->commit();
            return $this->return;
        } catch (HttpException  $exc) {
            $transaction->rollBack();

            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['status'] = $exc->statusCode;
            $this->return['message'] = Yii::t('api-logistic', $exc->getMessage());
            return $this->return;
        }
    }

    public function actionMeetingsList()
    {
        return 'MeetingsList';
    }

    /**
     * @param $id
     * @return array|mixed
     * @throws InvalidConfigException
     */
    public function actionUpdateMultipleMeeting()
    {
        $post = Yii::$app->request->post();
        $id = $post['id'];
        $currentAttendersList = !empty($post['attenders_ids']) ? $post['attenders_ids'] : [];

        if (empty($id)) {
            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', 'Incomplete data received.');
            return $this->return;
        }

        $reservation = RoomReservation::find()->where("id = :id", [':id' => $id])->one();
        if (empty($reservation)) {
            $this->return['status'] = 404;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', 'The meeting could not be found');
            return $this->return;
        }

        /**
         * try to create a new rule, if none created something is not ok
         */
        $newRRule = new RRule($post['RRule']);
        if (empty($newRRule)) {
            $this->return['status'] = 422;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', "Your request could not be processed");
            return $this->return;
        }

        /**
         * try to create a new rule from old data, if none created something is not ok
         */
        $oldRRule = new RSet($reservation->rfc_string);
        if (empty($oldRRule)) {
            $this->return['status'] = 422;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', "Your request could not be processed because old meeting data are wrong. Please contact an administrator!");
            return $this->return;
        }

        foreach ($oldRRule->getRRules() as $rrule) {
            $oldRRule = $rrule;
            break;
        }

        $existingAttenders = MeetingAttendee::find()
            ->select('user_id')
            ->where(['meeting_common_identifier' => $reservation['common_identifier']])
            ->asArray()
            ->column();
        $addedAttenders = array_values(array_diff($currentAttendersList, $existingAttenders));
        $removedAttenders = array_values(array_diff($existingAttenders, $currentAttendersList));
        $this->return['$addedAttenders'] = $addedAttenders;
        $this->return['$removedAttenders'] = $removedAttenders;

        //if the rules are the same no need to go further
        if ($oldRRule->rfcString() === $newRRule->rfcString() && empty($addedAttenders) && empty($removedAttenders)) {
            $this->return['status'] = 200;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', "No valid changes received");
            return $this->return;
        }

        $transaction = RoomReservation::getDb()->beginTransaction();
        try {
            $start_datetime = new DateTime(Yii::$app->request->post('check_in'));
            $diff = $start_datetime->diff(new DateTime(Yii::$app->request->post('check_out')));
            //load received data into model
            $reservation->load(Yii::$app->getRequest()->getBodyParams(), '');
            $reservation->updated = date('Y-m-d H:i:s');
            $reservation->updated_by = Yii::$app->user->id;
            $reservation->recurrence_weekday = !empty($post['recurrence_weekday']) ? implode(",", $post['recurrence_weekday']) : null;
            $reservation->recurrent_from = Yii::$app->request->post('recurrence_date');//@todo set correct date
            $reservation->recurrent_until = !empty($post['recurrent_until']) ? $post['recurrent_until'] : null;
            $reservation->duration = $diff->format('%H:%I');
            $reservation->rfc_string = $newRRule->rfcString();
            if (!$reservation->save()) {
                if ($reservation->hasErrors()) {
                    foreach ($reservation->errors as $error) {
                        throw new HttpException(409, $error[0]);
                    }
                }
                throw new HttpException(500, Yii::t('api-logistic', 'Failed to update meeting. Please contact an administrator!'));
            }

            /**
             * remove extra recurrences that doesn't meat the new data anymore
             */
            RoomReservation::deleteAll(['and', ['common_identifier' => $reservation->common_identifier], ['not in', 'id', $reservation->id]]);

            if (empty($currentAttendersList) && !empty($existingAttenders)) {//we prev had attenders and all of them where removed
                MeetingAttendee::deleteAll(['meeting_common_identifier' => $reservation['common_identifier']]);
            } elseif (!empty($removedAttenders) || !empty($addedAttenders)) {//we have attenders to be removed/add
                if (!empty($removedAttenders)) {
                    $toRemoveAttenders = MeetingAttendee::find()
                        ->where([
                            'user_id' => $removedAttenders,
                            'meeting_common_identifier' => $reservation['common_identifier']
                        ])->all();

                    if (!empty($toRemoveAttenders)) {
                        foreach ($toRemoveAttenders as $attender) {
                            $attender->delete();
                        }
                    }
                }

                if (!empty($addedAttenders)) {
                    $mailList = '';
                    foreach ($currentAttendersList as $newAttender) {
                        if (!in_array($newAttender, $existingAttenders)) {
                            $attender = new MeetingAttendee();
                            $attender->meeting_common_identifier = $reservation['common_identifier'];
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
                        $userEmail = User::find()->select('email')->where("id = {$newAttender}")->scalar();
                        if (!empty($userEmail)) {
                            $mailList .= $userEmail . ',';
                        }
                    }

                    if (strlen($mailList) > 0) {
                        $meetingName = $reservation->title;
                        $roomName = MeetingRoom::findOne($reservation->room_id)->name;
                        $meetingDate = date('Y-m-d', strtotime($post['RRule']['dtstart']));
                        $meetingTime = date('H:i', strtotime($post['RRule']['dtstart']));
                        $mailBody = "Ai fost adăugat în următorul meeting <b>{$meetingName}</b> care va avea loc în <b>{$roomName}</b> în data de {$meetingDate} la ora {$meetingTime}.";
                        $post = [
                            'to' => rtrim($mailList, ','),
                            'subject' => $meetingName,
                            'content' => $mailBody,
                        ];
                        $mailNotification = new MailHelper();
                        $mailNotification->sendEmailNotification($post);
                    }
                }

            } else {
                $this->return['attenders'] = "No condition was met for the attenders";
            }

            $this->return['message'] = Yii::t('api-logistic', 'Successfully updated all the meeting details');
            $transaction->commit();
            return $this->return;
        } catch (HttpException  $exc) {
            $transaction->rollBack();

            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['status'] = $exc->statusCode;
            $this->return['message'] = Yii::t('api-logistic', $exc->getMessage());
            return $this->return;
        } catch (Exception|\Exception $exc) {
            $transaction->rollBack();

            $this->return['status'] = 503;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', $exc->getMessage() . $exc->getTraceAsString());
            return $this->return;
        }
    }

    /**
     * @return array|mixed
     */
    public function actionUpdateSingleMeeting()
    {

        $post = Yii::$app->request->post();
        $id = $post['id'];

        if (empty($id)) {
            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', 'Incomplete data received.');
            return $this->return;
        }

        $reservation = RoomReservation::find()
            ->where("id = :id", [':id' => $id])
            ->one();
        if (empty($reservation)) {
            $this->return['status'] = 404;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', 'The meeting could not be found');
            return $this->return;
        }

        $rrule = new RRule($post['RRule']);
        if (empty($rrule)) {
            $this->return['status'] = 422;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', "Your update request could not be processed");
            return $this->return;
        }
        $newFromRecurrence = Yii::$app->request->post('recurrence_date');//@todo set correct date

        if (
            strtotime($newFromRecurrence . $post["check_in"]) !== strtotime($post["initial_check_in"])
            || strtotime($newFromRecurrence . $post["check_out"]) !== strtotime($post["initial_check_out"])
        ) {
            $transaction = RoomReservation::getDb()->beginTransaction();
            try {
                $exDate = $rrule[0]->format("Ymd\T{$reservation->check_in}");
                $newRfcString = RoomReservation::addExclusionToRRUleString($reservation->rfc_string, $exDate);

                $reservation->rfc_string = $newRfcString;
                $reservation->updated = date('Y-m-d H:i:s');
                $reservation->updated_by = Yii::$app->user->id;
                if (!$reservation->save()) {
                    if ($reservation->hasErrors()) {
                        foreach ($reservation->errors as $error) {
                            throw new HttpException(409, $error[0]);
                        }
                    }
                    throw new HttpException(500, Yii::t('api-logistic', 'Failed to update meeting. Parent details could not be updated. Please contact an administrator!'));
                }

                $start_datetime = new DateTime(Yii::$app->request->post('check_in'));
                $diff = $start_datetime->diff(new DateTime(Yii::$app->request->post('check_out')));

                $newReservation = new RoomReservation();
                $newReservation->load(Yii::$app->getRequest()->getBodyParams(), '');
                $newReservation->common_identifier = $reservation->common_identifier;
                $newReservation->recurrent_from = $newFromRecurrence;
                $newReservation->rfc_string = $rrule->rfcString();
                $newReservation->duration = $diff->format('%H:%I');
                $newReservation->added = date('Y-m-d H:i:s');
                $newReservation->added_by = Yii::$app->user->id;

                if (!$newReservation->save()) {
                    if ($newReservation->hasErrors()) {
                        foreach ($newReservation->errors as $error) {
                            throw new HttpException(409, $error[0]);
                        }
                    }
                    throw new HttpException(500, Yii::t('api-logistic', 'Failed to update meeting. New details could not be updated. Please contact an administrator!'));
                }

                $transaction->commit();
                return $this->return;
            } catch (HttpException  $exc) {
                $transaction->rollBack();

                Yii::$app->response->statusCode = $exc->statusCode;
                $this->return['status'] = $exc->statusCode;
                $this->return['message'] = Yii::t('api-logistic', $exc->getMessage());
                return $this->return;
            } catch (\Exception $exc) {
                $transaction->rollBack();

                $this->return['status'] = 503;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-logistic', $exc->getMessage() . $exc->getTraceAsString());
                return $this->return;
            }
        } else {
            $this->return['message'] = Yii::t('api-logistic', "The reservation has no modifications compared with previous version");
            return $this->return;
        }
    }

    /**
     * @return array|mixed
     */
    public function actionDeleteMeeting()
    {
        $post = Yii::$app->request->post();
        $id = $post['id'];
        $commonIdentifier = $post['common_identifier'];
        $exDate = $post['remove_date'];
        $deleteAll = (int)$post['delete_all'];

        if (empty($id) || empty($commonIdentifier)) {
            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', 'The received data are incomplete');
            return $this->return;
        }

        $reservation = RoomReservation::find()->where("id = :id AND common_identifier = :common_identifier", [
            ':id' => $id,
            ':common_identifier' => $commonIdentifier
        ])->one();
        if (empty($reservation)) {
            $this->return['status'] = 404;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', 'The meeting could not be found');
            return $this->return;
        }

        $transaction = RoomReservation::getDb()->beginTransaction();
        try {
            $toDelReservationQuery = RoomReservation::find();
            $toDelReservationQuery->select("id, common_identifier");
            $toDelReservationQuery->where("common_identifier = :common_identifier", [
                ':common_identifier' => $reservation['common_identifier']
            ]);

            if (($deleteAll === 1 && (int)$reservation->recurring === 1) || (int)$reservation->recurring === 0) {
                RoomReservation::deleteAll(['in', 'common_identifier', $reservation->common_identifier]);
                MeetingAttendee::deleteAll(['in', 'meeting_common_identifier', $reservation->common_identifier]);
                $this->return['message'] = Yii::t('api-logistic', "All reservations where successfully deleted");
            } elseif ($deleteAll === 0 && (int)$reservation->recurring === 1) {
                $newRfcRule = RoomReservation::addExclusionToRRUleString($reservation->rfc_string, $exDate);
                $reservation->rfc_string = $newRfcRule;
                $reservation->updated = date('Y-m-d H:i:s');
                $reservation->updated_by = Yii::$app->user->id;
                if (!$reservation->save()) {
                    if ($reservation->hasErrors()) {
                        foreach ($reservation->errors as $error) {
                            throw new HttpException(409, $error[0]);
                        }
                    }
                    throw new HttpException(500, Yii::t('api-logistic', 'Failed to delete meeting recurrence. Please contact an administrator!'));
                }
                $this->return['message'] = Yii::t('api-logistic', "The meeting was successfully deleted");
            } else {
                $this->return['message'] = Yii::t('api-logistic', "No valid changes received");
            }

            $transaction->commit();
            return $this->return;
        } catch (StaleObjectException|\Exception $exc) {
            $transaction->rollBack();

            $this->return['status'] = 500;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', $exc->getMessage());
            return $this->return;
        }
    }

    /**
     * @param $id
     * @param $meeting_date | the date of the selected meeting in calendar
     * @return array|mixed
     * @throws \Exception
     */
    public function actionDetails($id, $meeting_date)
    {
        $meeting = RoomReservation::find()
            ->where("id = :id", [':id' => $id])
            ->asArray()
            ->one();

        if (empty($meeting)) {
            $this->return['status'] = 404;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', 'The meeting could not be found');
            return $this->return;
        }

        $rRuleDetails = new RSet($meeting['rfc_string']);
        if (!$rRuleDetails->occursAt(date('YmdHi', strtotime($meeting_date . $meeting['check_in'])))) {
            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', 'The received meeting details are wrong');
            return $this->return;
        }
        $meeting['initial_check_in'] = date('Y-m-d H:i', strtotime($meeting['recurrent_from'] . $meeting['check_in']));
        $meeting['initial_check_out'] = date('Y-m-d H:i', strtotime($meeting['recurrent_from'] . $meeting['check_out']));
        $meeting['check_in'] = date('Y-m-d H:i', strtotime($meeting_date . $meeting['check_in']));
        $meeting['check_out'] = date('Y-m-d H:i', strtotime($meeting_date . $meeting['check_out']));

        $attendersIds = MeetingAttendee::find()
            ->select("user_id")
            ->where("meeting_common_identifier = :common_identifier", [':common_identifier' => $meeting['common_identifier']]);

        $organizer = User::find()
            ->select(['concat(first_name, SPACE(1), last_name) AS full_name', 'id', 'email'])
            ->where(['id' => $meeting['added_by']])
            ->asArray()
            ->one();
        $attenders = User::find()
            ->select(['concat(first_name, SPACE(1), last_name) as full_name', 'id', 'email'])
            ->where(['status' => User::STATUS_ACTIVE])
            ->andWhere(['in', 'id', $attendersIds])
            ->asArray()
            ->all();
        $meeting['current_user_can_edit'] =
            (int)Yii::$app->user->id === (int)$meeting['added_by']
            || Yii::$app->user->can('SuperAdmin');
        $meeting['organizer'] = $organizer;
        $meeting['attenders'] = $attenders;
        $this->return['meeting'] = $meeting;

        $this->return['status'] = 200;
        Yii::$app->response->statusCode = $this->return['status'];
        $this->return['message'] = Yii::t('api-logistic', 'Successfully retrieved meeting details');
        return $this->return;
    }

}