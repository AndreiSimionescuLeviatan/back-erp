<?php

namespace api\modules\v2\controllers;

use api\modules\v2\models\MeetingExternalAttendee;
use backend\modules\adm\models\Settings;
use common\components\DateTimeHelper;
use common\components\OutlookCalendarHelper;
use Microsoft\Graph\Exception\GraphException;
use Throwable;
use Yii;
use api\modules\v2\models\MeetingAttendee;
use api\modules\v2\models\MeetingRoom;
use api\modules\v2\models\RoomReservation;
use api\modules\v1\models\User;
use RRule\RRule;
use RRule\RSet;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\web\HttpException;

/**
 * RoomReservation controller
 */
class RoomReservationController extends \api\modules\v1\controllers\RoomReservationController
{
    public $modelClass = 'api\modules\v2\models\RoomReservation';

    /**
     * @return array|mixed
     * @throws InvalidConfigException|\Exception
     */
    public function actionCreateMeeting()
    {
        $post = Yii::$app->request->post();
        $postRRule = $post['RRule'];
        $attendersIds = Yii::$app->request->post('attenders_ids', []);
        $externalAttendersEmails = Yii::$app->request->post('external_attenders_email', []);
        $reservation = new RoomReservation();
        $commonIdentifier = (string)time();
        $start_datetime = Yii::$app->request->post('check_in');
        $stop_datetime = Yii::$app->request->post('check_out');

        $transaction = RoomReservation::getDb()->beginTransaction();
        try {
            /**
             * try to create a new rule, if none created something is not ok
             */
            $rrule = new RRule($postRRule);
            if (empty($rrule)) {
                $this->return['status'] = 422;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-logistic', "Your request could not be processed");
                return $this->return;
            }

            if (!$rrule->isFinite()) {
                $this->return['status'] = 400;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-logistic', "The recurrence has no ending date");
                return $this->return;
            }

            $ruleParams = $rrule->getRule();
            $reservation->load(Yii::$app->getRequest()->getBodyParams(), '');
            $reservation->common_identifier = $commonIdentifier;
            $reservation->recurrence_frequency = strtolower($ruleParams['FREQ']);
            $reservation->recurrence_weekday = $reservation->recurring == 1 ? implode(",", $post['recurrence_weekday']) : null;
            $reservation->recurrent_from = $rrule[0]->format('Y-m-d');
            $reservation->recurrent_until = $rrule[$rrule->count() - 1]->format('Y-m-d');
            $reservation->recurrence_count = $ruleParams['COUNT'];
            $reservation->rfc_string = $rrule->rfcString();
            $reservation->human_readable = $rrule->humanReadable(['locale' => 'ro']);
            $reservation->added = date('Y-m-d H:i:s');
            $reservation->added_by = Yii::$app->user->id;
            $reservation->duration = DateTimeHelper::getTimeDifference($start_datetime, $stop_datetime, '%H:%I');;

            if (!$reservation->save()) {
                if ($reservation->hasErrors()) {
                    foreach ($reservation->errors as $error) {
                        throw new HttpException(409, $error[0]);
                    }
                }
                throw new HttpException(500, Yii::t('api-logistic', 'Failed to create meeting. Please contact an administrator!'));
            }

            $room = MeetingRoom::findOne($reservation->room_id);
            if (empty($room)) {
                $this->return['status'] = 400;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-logistic', "Your request contains some invalid data about the room. Please contact an administrator!");
                return $this->return;
            }

            $outlookEvent = new OutlookCalendarHelper($reservation, true, [
                'oldReservation' => $reservation->oldAttributes,
                'calendarRrule' => $rrule,
                'calendarReservationLocation' => $room->name,
                'hasAttenders' => true,
                'hasExternalAttenders' => !empty($externalAttendersEmails)
            ]);

            /**
             * prepare the list with permanent attenders to be sent to Outlook
             */
            $permanentAttenders = Settings::find()->where(['name' => 'MEETING_ROOM_CC_LIST'])->asArray()->one();
            $permanentAttenders = !empty($permanentAttenders) && !empty($permanentAttenders['value']) ?
                $permanentAttenders['value'] :
                Yii::$app->params['meetingRoomCcList'];
            $permanentAttenders = explode(',', $permanentAttenders);
            foreach ($permanentAttenders as $value) {
                $outlookEvent->calendarAttenders[] = [
                    "emailAddress" => [
                        "address" => $value
                    ],
                    "type" => "optional" //The attendee type: required, optional, resource.
                ];
            }

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
                $attender = User::find()->where("id = {$attenderId}")->one();
                if (!empty($attender)) {
                    $outlookEvent->calendarAttenders[] = [
                        "emailAddress" => [
                            "address" => $attender->email,
                            "name" => $attender->fullName()
                        ],
                        "type" => "required"//The attendee type: required, optional, resource.
                    ];
                }
            }

            foreach ($externalAttendersEmails as $attenderEmail) {
                $extAttender = new MeetingExternalAttendee();
                $extAttender->meeting_common_identifier = $commonIdentifier;
                $extAttender->email_address = $attenderEmail;

                if (!$extAttender->save()) {
                    if ($extAttender->hasErrors()) {
                        foreach ($extAttender->errors as $error) {
                            throw new HttpException(409, $error[0]);
                        }
                    }
                    throw new HttpException(500, Yii::t(
                        'api-logistic',
                        "Failed to create meeting. External attender with email address {attenderEmail} could not be added. Please contact an administrator!", [
                            'attenderEmail' => $attenderEmail,
                        ]

                    ));
                }
                $outlookEvent->calendarAttenders[] = [
                    "emailAddress" => [
                        "address" => strtolower($extAttender->email_address)
                    ],
                    "type" => "required"//The attendee type: required, optional, resource.
                ];
            }

//            if (!empty($externalAttendersEmails)) {//send notifications only to new attenders
//                $external_notification_sent = MeetingExternalAttendee::notifyExternalAttenders($reservation, $externalAttendersEmails, $room->name);
//                $this->return['external_notification_sent'] = $external_notification_sent;
//                $this->return['new_email_sent_to'] = $externalAttendersEmails;
//            }

            $savedOutlookEvent = $outlookEvent->saveEventToOutlookCalendar();
            $this->return['outlook_data'] = $savedOutlookEvent;

            $reservation->outlook_id = $savedOutlookEvent['response']['id'];
            if (!$reservation->save()) {
                if ($reservation->hasErrors()) {
                    foreach ($reservation->errors as $error) {
                        throw new HttpException(409, $error[0]);
                    }
                }
                throw new HttpException(500, Yii::t('api-logistic', 'Could not set Outlook details. Please contact an administrator!'));
            }

            $this->return['message'] = Yii::t('api-logistic', 'Successfully saved the meeting details');
            $transaction->commit();
            return $this->return;
        } catch (HttpException  $exc) {
            $transaction->rollBack();

            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['status'] = $exc->statusCode;
            $this->return['message'] = Yii::t('api-logistic', $exc->getMessage());
            return $this->return;
        } catch (GraphException|Exception|\yii\base\Exception $exc) {
            $transaction->rollBack();

            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('api-logistic', $exc->getMessage());
            return $this->return;
        }
    }

    /**
     * @return array|mixed
     * @throws InvalidConfigException
     */
    public function actionUpdateMultipleMeeting()
    {
        $post = Yii::$app->request->post();
        $id = $post['id'];
        $receivedAttendersList = Yii::$app->request->post('attenders_ids', []);
        $receivedExtAttendersList = Yii::$app->request->post('external_attenders_email', []);
        $start_datetime = Yii::$app->request->post('check_in');
        $stop_datetime = Yii::$app->request->post('check_out');
        $attenderListChanged = false;

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
        if (!$newRRule->isFinite()) {
            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', "The new recurrence has no ending date");
            return $this->return;
        }

        $ruleParams = $newRRule->getRule();
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

        //load received data into model to check latter if any modifications are made
        $reservation->load(Yii::$app->getRequest()->getBodyParams(), '');
        $reservation->recurrence_frequency = strtolower($ruleParams['FREQ']);
        $reservation->recurrence_weekday = $reservation->recurring == 1 ? implode(",", $post['recurrence_weekday']) : null;
        $reservation->recurrent_from = $newRRule[0]->format('Y-m-d');
        $reservation->recurrent_until = $newRRule[$newRRule->count() - 1]->format('Y-m-d');
        $reservation->recurrence_count = $ruleParams['COUNT'];
        $reservation->rfc_string = $newRRule->rfcString();
        $reservation->human_readable = $newRRule->humanReadable(['locale' => 'ro']);
        $reservation->validate();

        /**
         * used to compare rfc strings and detect if are any changes
         * between old and new rule
         */
        foreach ($oldRRule->getRRules() as $rrule) {
            $oldRRule = $rrule;
            break;
        }

        /**Check for changes in external attenders*/
        $existingAttenders = MeetingAttendee::find()
            ->select('user_id')
            ->where(['meeting_common_identifier' => $reservation['common_identifier']])
            ->asArray()
            ->column();
        $addedAttenders = array_values(array_diff($receivedAttendersList, $existingAttenders));
        $removedAttenders = array_values(array_diff($existingAttenders, $receivedAttendersList));

        /**
         * Get all attenders email/name to be used on Outlook calendar.
         * The new updated list will receive new meeting details if changed.
         * If only the participants where changed only the new ones will receive event info
         */
        $updatedAttenderList = array_values(array_merge(array_diff($existingAttenders, $removedAttenders), $addedAttenders));

        /**Check for changes in external attenders*/
        $existingExtAttenders = MeetingExternalAttendee::find()
            ->select('email_address')
            ->where(['meeting_common_identifier' => $reservation['common_identifier']])
            ->asArray()
            ->column();
        $addedExtAttenders = array_values(array_diff($receivedExtAttendersList, $existingExtAttenders));
        $removedExtAttenders = array_values(array_diff($existingExtAttenders, $receivedExtAttendersList));

        //if the meeting data are the same no need to go further
        $rRuleNotChanged = $oldRRule->rfcString() === $newRRule->rfcString();
        $reservationDetailsChanged = $reservation->getDirtyAttributes();
        if (empty($reservationDetailsChanged) &&
            $rRuleNotChanged &&
            empty($addedAttenders) &&
            empty($removedAttenders) &&
            empty($addedExtAttenders) &&
            empty($removedExtAttenders)
        ) {
            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', "No valid changes received");
            return $this->return;
        }

        $transaction = RoomReservation::getDb()->beginTransaction();
        try {
            $reservation->updated = date('Y-m-d H:i:s');
            $reservation->updated_by = Yii::$app->user->id;
            $reservation->duration = DateTimeHelper::getTimeDifference($start_datetime, $stop_datetime, '%H:%I');;

            $outlookEvent = new OutlookCalendarHelper($reservation, false, [
                'oldReservation' => $reservation->oldAttributes,
                'calendarRrule' => $newRRule,
                'hasAttenders' => !empty($addedAttenders) || !empty($removedAttenders),
                'hasExternalAttenders' => !empty($addedExtAttenders) || !empty($removedExtAttenders)
            ]);

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

            /** deal with external attenders update*/
            if (empty($receivedExtAttendersList) && !empty($existingExtAttenders)) {//we prev had ext attenders and all of them where removed
                MeetingExternalAttendee::deleteAll(['meeting_common_identifier' => $reservation['common_identifier']]);
                $attenderListChanged = true;

            } elseif (!empty($removedExtAttenders) || !empty($addedExtAttenders)) {//we have ext attenders to be removed/add
                MeetingExternalAttendee::addOrRemoveExternalAttenders($reservation['common_identifier'], $existingExtAttenders, $receivedExtAttendersList, $addedExtAttenders, $removedExtAttenders);
                $attenderListChanged = true;
            }

            /** deal with internal attenders update*/
            if (empty($receivedAttendersList) && !empty($existingAttenders)) {//we prev had attenders and all of them where removed
                MeetingAttendee::deleteAll(['meeting_common_identifier' => $reservation['common_identifier']]);
                $attenderListChanged = true;
            } elseif (!empty($removedAttenders) || !empty($addedAttenders)) {//we have attenders to be removed/add
                MeetingAttendee::addOrRemoveInternalAttenders($reservation['common_identifier'], $existingAttenders, $receivedAttendersList, $addedAttenders, $removedAttenders);
                $attenderListChanged = true;
            }

            /**
             * prepare attenders data to be sent to Outlook
             */
            $permanentAttenders = Settings::find()->where(['name' => 'MEETING_ROOM_CC_LIST'])->asArray()->one();
            $permanentAttenders = !empty($permanentAttenders) && !empty($permanentAttenders['value']) ?
                $permanentAttenders['value'] :
                Yii::$app->params['meetingRoomCcList'];
            $permanentAttenders = explode(',', $permanentAttenders);
            foreach ($permanentAttenders as $value) {
                $outlookEvent->calendarAttenders[] = [
                    "emailAddress" => [
                        "address" => $value
                    ],
                    "type" => "optional" //The attendee type: required, optional, resource.
                ];
            }
            if (!empty($updatedAttenderList)) {
                $attenders = User::find()->where(['id' => $updatedAttenderList])->all();
                foreach ($attenders as $attender) {
                    $outlookEvent->calendarAttenders[] = [
                        "emailAddress" => [
                            "name" => $attender->fullName(),
                            "address" => $attender->email,
                        ],
                        "type" => "required" //The attendee type: required, optional, resource.
                    ];
                }
            }
            foreach ($addedExtAttenders as $extAttender) {
                $outlookEvent->calendarAttenders[] = [
                    "emailAddress" => [
                        "address" => strtolower($extAttender)
                    ],
                    "type" => "required"//The attendee type: required, optional, resource.
                ];
            }
            if ($rRuleNotChanged || !empty($reservationDetailsChanged) || $attenderListChanged) {
                $savedOutlookEvent = $outlookEvent->updateEventInOutlookCalendar();
                $this->return['outlook_data'] = $savedOutlookEvent;
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

            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', $exc->getMessage());
            return $this->return;
        } catch (Throwable $exc) {
            $transaction->rollBack();

            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', $exc->getMessage());
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
        $postRRule = $post['RRule'];
        $start_datetime = Yii::$app->request->post('check_in');
        $stop_datetime = Yii::$app->request->post('check_out');

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

        $rrule = new RRule($postRRule);
        if (empty($rrule)) {
            $this->return['status'] = 422;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', "Your update request could not be processed");
            return $this->return;
        }
        if (!$rrule->isFinite()) {
            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', "The recurrence has no ending date");
            return $this->return;
        }
        $newFromRecurrence = $rrule[0]->format('Y-m-d');

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

                $ruleParams = $rrule->getRule();
                $newReservation = new RoomReservation();
                $newReservation->load(Yii::$app->getRequest()->getBodyParams(), '');
                $newReservation->common_identifier = $reservation->common_identifier;
//                $newReservation->outlook_id = $reservation->outlook_id;
                $newReservation->recurrence_frequency = strtolower($ruleParams['FREQ']);
                $newReservation->recurrent_from = $rrule[0]->format('Y-m-d');
                $newReservation->recurrent_until = $rrule[$rrule->count() - 1]->format('Y-m-d');
                $newReservation->recurrence_count = $ruleParams['COUNT'];
                $newReservation->rfc_string = $rrule->rfcString();
                $newReservation->duration = DateTimeHelper::getTimeDifference($start_datetime, $stop_datetime, '%H:%I');
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

                $outlookEvent = new OutlookCalendarHelper($newReservation, false, [
                    'oldReservation' => $reservation->attributes, /** used to identify the edited occurrence event in Outlook calendar */
                    'isPartOfSeries' => true,
                    'initialEventStart' => $post["initial_check_in"],
                    'initialEventStop' => $post["initial_check_out"],
                ]);
                $savedOutlookEvent = $outlookEvent->updateEventInOutlookCalendar();
                $this->return['outlook_data'] = $savedOutlookEvent;

                $newReservation->outlook_id = $savedOutlookEvent['response']['id'];
                $newReservation->parent_id = $reservation->id;
                if (!$newReservation->save()) {
                    if ($newReservation->hasErrors()) {
                        foreach ($reservation->errors as $error) {
                            throw new HttpException(409, $error[0]);
                        }
                    }
                    throw new HttpException(500, Yii::t('api-logistic', 'Could not set Outlook details. Please contact an administrator!'));
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
        if ($deleteAll === 0 && empty($reservation->outlook_id)) {
            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', 'Sorry for inconvenience but, the reservation that you are trying to delete is added before Outlook calendar implementation and, for that reason, the option to remove single occurrence is not available. Please remove meeting series and add her again to align meeting details to new implementation.');
            return $this->return;
        }

        $transaction = RoomReservation::getDb()->beginTransaction();
        try {
            $toDelReservationQuery = RoomReservation::find();
            $toDelReservationQuery->select("id, common_identifier");
            $toDelReservationQuery->where("common_identifier = :common_identifier", [
                ':common_identifier' => $reservation['common_identifier']
            ]);

            $outlookEvent = new OutlookCalendarHelper($reservation);
            if ($deleteAll === 1) {
                if (!empty($reservation->parent_id)) {
                    $reservation->delete();
                } else {
                    RoomReservation::deleteAll(['in', 'common_identifier', $reservation->common_identifier]);
                    MeetingAttendee::deleteAll(['in', 'meeting_common_identifier', $reservation->common_identifier]);
                    MeetingExternalAttendee::deleteAll(['in', 'meeting_common_identifier', $reservation->common_identifier]);
                }
                /**
                 * !empty($reservation->outlook_id) condition was added as a quickfix to allow users to delete events added before Outlook calendar implementation
                 * @todo in time will check in db to see if are still meeting that are not sync with Outlook and if not
                 * will remove this condition
                 */
                if (!empty($reservation->outlook_id) && !$outlookEvent->deleteEventFromOutlookCalendar()) {
                    if ((int)$reservation->recurring === 1) {
                        $this->return['message'] =
                            Yii::t('api-logistic', 'Local reservations where successfully deleted, but we could not remove them from Outlook calendar.');
                    } else {
                        $this->return['message'] =
                            Yii::t('api-logistic', 'Reservation successfully deleted, but we could not remove her from Outlook calendar.');
                    }
                } else {
                    if ((int)$reservation->recurring === 1) {
                        $this->return['message'] = Yii::t('api-logistic', 'All reservations where successfully deleted');
                    } else {
                        $this->return['message'] = Yii::t('api-logistic', 'Reservation successfully deleted');
                    }
                    if (empty($reservation->outlook_id)) {
                        $this->return['message'] .= ". ";
                        $this->return['message'] .= Yii::t('api-logistic', 'Please beware, because no Outlook/Teams calendar event removed!');
                    }
                }
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

                if (!$outlookEvent->deleteEventFromOutlookCalendar($exDate)) {
                    $this->return['message'] = Yii::t('api-logistic', 'Reservation successfully deleted, but we could not remove her from Outlook calendar.');
                } else {
                    $this->return['message'] = Yii::t('api-logistic', 'Reservation successfully deleted');
                }
                $this->return['message'] = Yii::t('api-logistic', 'The meeting was successfully deleted');
            } else {
                $this->return['message'] = Yii::t('api-logistic', 'No valid changes received');
            }

            $transaction->commit();
            return $this->return;
        } catch (\Exception $exc) {
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
//        $meeting['initial_check_in'] = date('Y-m-d H:i', strtotime($meeting['recurrent_from'] . $meeting['check_in']));
//        $meeting['initial_check_out'] = date('Y-m-d H:i', strtotime($meeting['recurrent_from'] . $meeting['check_out']));
        $meeting['initial_check_in'] = date('Y-m-d H:i', strtotime($meeting_date . $meeting['check_in']));
        $meeting['initial_check_out'] = date('Y-m-d H:i', strtotime($meeting_date . $meeting['check_out']));
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
        $externalAttenders = MeetingExternalAttendee::find()
            ->select(['id', 'email_address'])
            ->where("meeting_common_identifier = :common_identifier", [
                ':common_identifier' => $meeting['common_identifier']
            ])->asArray()->all();


        $meeting['current_user_can_edit'] = (int)Yii::$app->user->id === (int)$meeting['added_by'] || Yii::$app->user->can('SuperAdmin');
        $meeting['organizer'] = $organizer;
        $meeting['attenders'] = $attenders;
        $meeting['external_attenders'] = $externalAttenders;
        $this->return['meeting'] = $meeting;

        $this->return['status'] = 200;
        Yii::$app->response->statusCode = $this->return['status'];
        $this->return['message'] = Yii::t('api-logistic', 'Successfully retrieved meeting details');
        return $this->return;
    }

}