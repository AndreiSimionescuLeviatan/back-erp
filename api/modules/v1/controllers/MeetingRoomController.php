<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\MeetingRoom;
use api\modules\v1\models\RoomReservation;
use api\modules\v1\models\User;
use RRule\RSet;
use Yii;

/**
 * MeetingRoom controller
 */
class MeetingRoomController extends RestV1Controller
{
    public $modelClass = 'api\modules\v1\models\MeetingRoom';

    /**
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $rooms = MeetingRoom::find()
            ->asArray()
            ->all();

        if (empty($rooms)) {
            $this->return['status'] = 404;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', "No meeting rooms found. Please ask your system administrator to add at least one meeting room.");
            return $this->return;
        }

        $this->return['meeting_rooms'] = $rooms;
        $message = Yii::t('api-logistic', 'Successfully sent the meeting rooms list');
        return $this->prepareResponse($message);
    }

    /**
     * @param $id
     * @param $from
     * @param $until
     * @return array|mixed
     */
    public function actionView($id, $from = null, $until = null)
    {
        $room = MeetingRoom::find()
            ->where("id = {$id}")
            ->asArray()
            ->one();

        if (empty($room)) {
            $this->return['status'] = 404;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', "No meeting rooms found. Please ask your system administrator to add at least one meeting room.");
            return $this->return;
        }

        $room['meetings'] = [];
        if (!empty($from) && !empty($until)) {
            $reservationsQuery = RoomReservation::find()
                ->select(["id", "room_id", "title", "duration", "rfc_string"])
                ->where([
                    'and', "room_id = {$id}",
                    ['or',
                        ['and',
                            ['recurring' => 1],
                            ['between', 'recurrent_from', $from, $until],
                            ['>=', 'recurrent_until', $until]
                        ],
                        ['and',
                            ['recurring' => 1],
                            ['<=', 'recurrent_from', $from],
                            ['>=', 'recurrent_until', $until]
                        ],
                        ['and', ['recurring' => 0], ['between', 'recurrent_from', $from, $until]],
                        /**
                         * @todo when a meeting is recurrent by a number of fixed counts the sql doesn't return any data
                         */
//                        ['and', ['recurring' => 1], ['>=', 'recurrence_count', 1], ['between', 'recurrent_from', $from, $until]]
                    ]
                ]);
            $reservations = $reservationsQuery->all();
            if (!empty($reservations)) {
                foreach ($reservations as $key => $reservation) {
                    $rset = new RSet($reservation->rfc_string);
                    $room['meetings'][$key]['id'] = $reservation->id;
                    $room['meetings'][$key]['title'] = $reservation->title;
                    foreach ($rset->getRRules() as $rrule) {
                        $room['meetings'][$key]['duration'] = $reservation->duration;
                        $room['meetings'][$key]['url'] = "/reservation-details/reservation-id/{$reservation->id}/";
                        $room['meetings'][$key]['rrule'] = array_change_key_case(array_filter($rrule->getRule()), CASE_LOWER);
                        (array_key_exists('dtstart', $room['meetings'][$key]['rrule']) && get_class($room['meetings'][$key]['rrule']['dtstart']) === 'DateTime') && $room['meetings'][$key]['rrule']['dtstart'] = $room['meetings'][$key]['rrule']['dtstart']->format('c');
                        (array_key_exists('until', $room['meetings'][$key]['rrule']) && get_class($room['meetings'][$key]['rrule']['until']) === 'DateTime') && $room['meetings'][$key]['rrule']['until'] = $room['meetings'][$key]['rrule']['until']->format('c');
                        if (array_key_exists('byday', $room['meetings'][$key]['rrule'])) {
                            $room['meetings'][$key]['rrule']['byweekday'] = explode(",", $room['meetings'][$key]['rrule']['byday']);
                            unset($room['meetings'][$key]['rrule']['byday']);
                        }
                        foreach ($rset->getExDates() as $occurrence) {
                            $room['meetings'][$key]['exdate'][] = $occurrence->format('c');
                        }
                    }
                }
            }
        }

        $this->return['meeting_room'] = $room;
        $message = Yii::t('api-logistic', 'Successfully sent the meeting room details');
        return $this->prepareResponse($message);
    }

    /**
     * Sends user details to app, details like `car_id` and others that are normally sent when user logs in
     * @return array
     */
    public function actionUsersList($query)
    {

        $userQuery = User::find();
        $userQuery->select(['concat(first_name, SPACE(1), last_name) as full_name', 'id', 'email']);
        $userQuery->where(['status' => User::STATUS_ACTIVE]);
        $userQuery->andWhere(['OR', ['like', 'first_name', $query], ['like', 'last_name', $query]]);
        $userQuery->orderBy(['first_name' => SORT_ASC, 'last_name' => SORT_ASC]);
        $this->return['user_list'] = $userQuery->asArray()->all();

        $this->return['status'] = 200;
        Yii::$app->response->statusCode = $this->return['status'];
        $this->return['message'] = Yii::t('api-logistic', 'Successfully retrieved users list');
        return $this->return;
    }

}