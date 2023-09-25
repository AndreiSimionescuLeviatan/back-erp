<?php

namespace api\modules\v2\controllers;

use api\modules\v2\models\MeetingRoom;
use api\modules\v2\models\MeetingRoomErpCompany;
use api\modules\v2\models\RoomReservation;
use RRule\RSet;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * MeetingRoom controller
 */
class MeetingRoomController extends \api\modules\v1\controllers\MeetingRoomController
{
    public $modelClass = 'api\modules\v2\models\MeetingRoom';


    public function actionIndex()
    {
        if (empty(Yii::$app->user->identity->userCompanies)) {
            $this->return['status'] = 412;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', "Sorry, you are not assigned to any company. Please contact an administrator to fix this!");
            return $this->return;
        }

        $userCompaniesList = ArrayHelper::getColumn(Yii::$app->user->identity->userCompanies, 'company_id');
        $roomCompany = MeetingRoomErpCompany::find()
            ->select('room_id')
            ->where(['company_id' => $userCompaniesList])
            ->distinct()
            ->column();

        $rooms = MeetingRoom::find()
            ->where(['id' => $roomCompany])
            ->asArray()
            ->all();

        if (empty($rooms)) {
            $this->return['status'] = 404;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', "No meeting rooms found. Please ask your system administrator to add at least one meeting room.");
            return $this->return;
        }

        $templates = MeetingRoom::getTemplates();
        foreach ($rooms as $key => $room) {
            $templateKey = array_search($room['template_id'], array_column($templates, 'id'));
            $rooms[$key]['template'] = str_replace('.css', '', $templates[$templateKey]['file_name']);
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

        $templates = MeetingRoom::getTemplates();
        $templateKey = array_search($room['template_id'], array_column($templates, 'id'));
        $room['template'] = str_replace('.css', '', $templates[$templateKey]['file_name']);

        $room['meetings'] = [];
        if (!empty($from) && !empty($until)) {
            $reservationsQuery = RoomReservation::find()
                ->select(["id", "room_id", "title", "duration", "rfc_string", "added", "added_by"])
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
                        ['and',
                            ['recurring' => 1],
                            ['<=', 'recurrent_from', $from],
                            ['between', 'recurrent_until', $from, $until],
                        ],
                        ['and',
                            ['recurring' => 0],
                            ['between', 'recurrent_from', $from, $until]
                        ],
                        ['and',
                            ['recurring' => 1],
                            ['>=', 'recurrence_count', 1],
                            ['<=', 'recurrent_from', $from],
                            ['<=', 'recurrent_until', $until]
                        ]
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
                        $room['meetings'][$key]['className'] = (int)Yii::$app->user->id === (int)$reservation->added_by ? 'is-organizer' : null;
                        $room['meetings'][$key]['extendedProps'] = ['added' => $reservation->added];
                        (array_key_exists('dtstart', $room['meetings'][$key]['rrule']) &&
                            get_class($room['meetings'][$key]['rrule']['dtstart']) === 'DateTime') &&
                        $room['meetings'][$key]['rrule']['dtstart'] = $room['meetings'][$key]['rrule']['dtstart']->format('Y-m-d\TH:i:s');
                        (array_key_exists('until', $room['meetings'][$key]['rrule']) &&
                            get_class($room['meetings'][$key]['rrule']['until']) === 'DateTime') &&
                        $room['meetings'][$key]['rrule']['until'] = $room['meetings'][$key]['rrule']['until']->format('Y-m-d\TH:i:s');
                        if (array_key_exists('byday', $room['meetings'][$key]['rrule'])) {
                            $room['meetings'][$key]['rrule']['byweekday'] = explode(",", $room['meetings'][$key]['rrule']['byday']);
                            unset($room['meetings'][$key]['rrule']['byday']);
                        }
                        foreach ($rset->getExDates() as $occurrence) {
                            $room['meetings'][$key]['exdate'][] = $occurrence->format('Y-m-d\TH:i:s');
                        }
                    }
                }
            }
        }

        $this->return['meeting_room'] = $room;
        $message = Yii::t('api-logistic', 'Successfully sent the meeting room details');
        return $this->prepareResponse($message);
    }
}