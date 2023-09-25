<?php

namespace common\components;

use api\modules\v1\models\RoomReservation;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Event;
use RRule\RRule;
use Yii;
use yii\base\Component;
use yii\web\HttpException;

class OutlookCalendarHelper extends Component
{

    private $calendarTimeZone = 'Europe/Bucharest';
    /**
     * @var null | RoomReservation
     */
    private $newOccurrence = null;
    private $eventBody = [];
    /**
     * @var null | RoomReservation
     */
    private $reservation;
    /**
     * Used to compare reservation details when user updates an event
     * @var null | RoomReservation
     */
    public $oldReservation;
    private $isNewEvent;
    private $initialEventStart;
    private $initialEventStop;
    private $isPartOfSeries = false;
    public $calendarReservationLocation = '';
    public $calendarAttenders = [];
    public $hasAttenders = false;
    public $hasExternalAttenders = false;
    /**
     * @var RRule
     */
    public $calendarRrule;

    /**
     * @param $reservation RoomReservation
     * @param $config
     */
    public function __construct($reservation, $isNewEvent = false, $config = [])
    {
        $this->reservation = $reservation;
        $this->isNewEvent = $isNewEvent;
        parent::__construct($config);
    }

    /**
     * @return array
     * @throws HttpException
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function saveEventToOutlookCalendar()
    {

        try {
            $this->eventBody['allowNewTimeProposals'] = false;
            $this->eventBody['isOrganizer'] = false;
            $this->setEventRequestData();

            $graph = $this->MsGraphConnect();
            $event = $graph->createRequest("POST", "/me/calendar/events")
                ->addHeaders(['Prefer' => 'outlook.timezone="Europe/Bucharest"'])
                ->attachBody($this->eventBody)
                ->execute();
            if ((int)$event->getStatus() === 201) {
                return ['sent_data' => $this->eventBody, 'response' => $event->getBody()];
            }
            throw new HttpException(400, Yii::t('app', 'Received an unexpected code when setting the meeting to Outlook calendar'));
        } catch (GuzzleException $exc) {
            $response = $exc->getResponse();
            $resStr = $response->getBody()->getContents();
            $resToJson = json_decode($resStr, true);
            throw new HttpException($response->getStatusCode(), Yii::t("app", "Save event error. Error received: {remoteErr}", [
                'remoteErr' => $resToJson['error']['message']
            ]));
        }
    }

    /**
     * @return array
     * @throws HttpException
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function updateEventInOutlookCalendar()
    {
        if (empty($this->reservation) || empty($this->oldReservation)) {
            throw new HttpException(409, Yii::t('app', 'Invalid reservation series. Please contact an administrator'));
        }

        $mainOutlookEvtId = $this->oldReservation['outlook_id'];
        $url = "/me/events/{$mainOutlookEvtId}";
        if ((int)$this->oldReservation['recurring'] === 1 && $this->isPartOfSeries) {
            $start_datetime = date('Y-m-d\TH:i', strtotime($this->initialEventStart));
            $end_datetime = date('Y-m-d\TH:i', strtotime($this->initialEventStop));
            $url .= "/instances?startDateTime={$start_datetime}&endDateTime={$end_datetime}";
        }

        try {
            $graph = $this->MsGraphConnect();
            $mainEvent = $graph->createRequest("GET", $url)
                ->addHeaders(['Prefer' => 'outlook.timezone="Europe/Bucharest"'])
                ->execute();
            if ((int)$mainEvent->getStatus() === 200) {
                $_mainEvt = $mainEvent->getResponseAsObject(Event::class);
                $evtId = is_array($_mainEvt) ? $_mainEvt[0]->getId() : $_mainEvt->getId();
                if (empty($_mainEvt)) {
                    throw new HttpException(404, Yii::t('app', 'Outlook calendar main event not found. Please contact an administrator!'));
                }

                $this->setEventRequestData();

                $_url = "/me/events/{$evtId}";
                $occurrence = $graph->createRequest("PATCH", $_url)
                    ->attachBody($this->eventBody)
                    ->execute();
                if ((int)$mainEvent->getStatus() === 200) {
                    return ['sent_data' => $this->eventBody, 'response' => $occurrence->getBody()];
                }
                throw new HttpException(400, Yii::t('app', 'Received an unexpected code while updating the meeting in Outlook calendar'));
            }
            throw new HttpException(400, Yii::t('app', 'Received an unexpected code while getting the meeting from Outlook calendar'));
        } catch (GuzzleException $exc) {
            $response = $exc->getResponse();
            $resStr = $response->getBody()->getContents();
            $resToJson = json_decode($resStr, true);
            if (!empty($resToJson['error']) && !empty($resToJson['error']['code']) && $resToJson['error']['code'] === 'ErrorOccurrenceCrossingBoundary') {
                throw new HttpException($response->getStatusCode(), Yii::t('app', 'An reservation that is part of a recurrence cannot be moved to or before the day of the previous occurrence, and cannot be moved to or after the day of the following occurrence.'));
            }
            throw new HttpException($response->getStatusCode(), Yii::t("app", "Update event error. Error received: {remoteErr}", [
                'remoteErr' => $resToJson['error']['message']
            ]));
        }
    }

    /**
     * @param $dateToRemove string
     * A date/time string.
     * Valid formats are explained in https://www.php.net/manual/en/datetime.formats.php
     * @return true
     * @throws HttpException
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function deleteEventFromOutlookCalendar($dateToRemove = null)
    {
        try {
            if (empty($this->reservation->outlook_id)) {
                throw new HttpException(409, Yii::t('app', 'Unavailable Outlook calendar details'));
            }
            $graph = $this->MsGraphConnect();

            $outlookEvtId = $this->reservation->outlook_id;
            //if we have $dateToRemove param set it means that we remove only one instance from event series
            //and we need his id based on parent outlook id
            if (!empty($dateToRemove)) {
                $evtStart = new DateTime($dateToRemove);
                $evtStart = $evtStart->format('Y-m-d\TH:i');
                $evtDurationInMs = strtotime($this->reservation->duration) - strtotime('00:00');
                $evtStop = new DateTime(date('Y-m-d H:i', strtotime($dateToRemove) + $evtDurationInMs));
                $evtStop = $evtStop->format('Y-m-d\TH:i');
                $url = "/me/events/{$outlookEvtId}/instances?startDateTime={$evtStart}&endDateTime={$evtStop}";
                $mainEvt = $graph->createRequest("GET", $url)
                    ->addHeaders(['Prefer' => 'outlook.timezone="Europe/Bucharest"'])
                    ->execute();
                if ((int)$mainEvt->getStatus() === 200) {
                    $occurrences = $mainEvt->getResponseAsObject(Event::class);
                    $outlookEvtId = is_array($occurrences) ? $occurrences[0]->getId() : $occurrences->getId();
                }
            }
            $event = $graph->createRequest("DELETE", "/me/events/{$outlookEvtId}")->execute();
            if ((int)$event->getStatus() === 204) {
                return true;
            }
            throw new HttpException(400, Yii::t('app', 'Received an unexpected code when deleting the meeting from Outlook calendar'));
        } catch (GuzzleException $exc) {
            $response = $exc->getResponse();
            $resStr = $response->getBody()->getContents();
            $resToJson = json_decode($resStr, true);
            throw new HttpException($response->getStatusCode(), Yii::t("app", "Delete event error. Error received: {remoteErr}", [
                'remoteErr' => $resToJson['error']['message']
            ]));
        }
    }

    /**
     * @return array
     * @throws HttpException
     */
    private function setOutlookCalendarRecurrenceDetails()
    {
        $recurrence = [];
        if (empty($this->calendarRrule)) {
            throw new HttpException(400, Yii::t('app', 'The recurrence rule to be used in Outlook calendar are invalid'));
        }

        $rRule = $this->calendarRrule->getRule();
        $byDayList = $rRule['BYDAY'];
        $wkStart = $rRule['WKST'];

        /**
         * Docs
         * @url https://learn.microsoft.com/en-us/graph/api/resources/recurrencepattern?view=graph-rest-1.0
         */
        $recurrence["pattern"] = [
            /**
             * The first day of the week.
             * The possible values are: sunday, monday, tuesday, wednesday, thursday, friday, saturday.
             * Default is sunday. Required if type is weekly.
             */
            "firstDayOfWeek" => !empty($wkStart) ? $this->convertShorDayNameToLong($wkStart, true) : 'monday',
            /**
             * The day of the month on which the event occurs. Required if type is absoluteMonthly or absoluteYearly
             */
            // "dayOfMonth" => null, //@todo check when & if needed
            /**
             * Specifies on which instance of the allowed days specified in daysOfWeek the event occurs, counted from the first instance in the month.
             * The possible values are: first, second, third, fourth, last. Default is first.
             * Optional and used if type is relativeMonthly or relativeYearly.
             */
            //"index" => null, //@todo check when & if needed
            /**
             * The number of units between occurrences, where units can be in days, weeks, months, or years, depending on the type.
             * Required.
             */
            "interval" => $rRule['INTERVAL'],
            /**
             * The month in which the event occurs. This is a number from 1 to 12.
             */
            // "month" => null, //@todo check when & if needed
            /**
             * The recurrence pattern type: daily, weekly, absoluteMonthly, relativeMonthly, absoluteYearly, relativeYearly.
             * Required.
             * For more information, see values of type property.
             * @url https://learn.microsoft.com/en-us/graph/api/resources/recurrencepattern?view=graph-rest-1.0#values-of-type-property
             */
            "type" => $rRule['FREQ']
        ];

        /**
         * A collection of the days of the week on which the event occurs.
         * The possible values are: sunday, monday, tuesday, wednesday, thursday, friday, saturday.
         * If type is relativeMonthly or relativeYearly, and daysOfWeek specifies more than one day, the event falls on the first day that satisfies the pattern.
         * Required if type is weekly, relativeMonthly, or relativeYearly.
         */
        if ($rRule['FREQ'] === 'weekly')
            $recurrence["pattern"]["daysOfWeek"] = $this->convertShorDayNameToLong($byDayList, false);

        /**
         * more info
         * @url https://learn.microsoft.com/en-us/graph/api/resources/recurrencerange?view=graph-rest-1.0
         */
        $recurrence["range"] = [
            /**
             * The recurrence range.
             * The possible values are: endDate, noEnd, numbered. Required.
             */
            "type" => $this->getOutlookRecurrenceRangeType(),
            /**
             * The date to start applying the recurrence pattern.
             * The first occurrence of the meeting may be this date or later, depending on the recurrence pattern of the event.
             * Must be the same value as the start property of the recurring event. Required.
             */
            "startDate" => $this->reservation->recurrent_from,
            /**
             * The date to stop applying the recurrence pattern.
             * Depending on the recurrence pattern of the event, the last occurrence of the meeting may not be this date.
             * Required if type is endDate
             */
            "endDate" => $this->reservation->recurrent_until,
            /**
             * Time zone for the startDate and endDate properties. Optional.
             * If not specified, the time zone of the event is used.
             */
            "recurrenceTimeZone" => $this->calendarTimeZone,
            /**
             * The number of times to repeat the event.
             * Required and must be positive if type is numbered.
             */
            "numberOfOccurrences" => $this->reservation->recurrence_type === 'end_date' ? 0 : $rRule['COUNT']
        ];
        return $recurrence;
    }

    /**
     * @param $dayList
     * @param $asString
     * @return null[]|string|string[]
     * @throws HttpException
     */
    public function convertShorDayNameToLong($dayList, $asString)
    {
        if (!is_array($dayList)) {
            $dayList = explode(',', $dayList);
        }

        if ($asString && count(array_filter($dayList)) > 1) {
            throw new HttpException(400, Yii::t('app', 'Wrong params received.'));
        }

        $dayNames = array_map(function ($value) {
            switch (strtolower($value)) {
                case 'mo':
                    return 'monday';
                case 'tu':
                    return 'tuesday';
                case 'we':
                    return 'wednesday';
                case 'th':
                    return 'thursday';
                case 'fr':
                    return 'friday';
                case 'sa':
                    return 'saturday';
                case 'su':
                    return 'sunday';
                default:
                    return null;
            }
        }, $dayList);

        if (empty($dayNames))
            return 'monday';

        return $asString ? implode($dayNames) : array_filter($dayNames);
    }

    /**
     * Converts DB stored values for recurrence_type col to Outlook accepted values
     * @return string
     */
    private function getOutlookRecurrenceRangeType()
    {
        switch (strtolower($this->reservation->recurrence_type)) {
            case 'end_date':
                return 'endDate';
            case 'end_after':
                return 'numbered';
            case 'no_end':
                return 'noEnd';
            default:
                return null;
        }
    }

    /**
     * @return Graph
     * @throws HttpException
     */
    private function MsGraphConnect()
    {
        try {
            $guzzle = new Client();
            $url = 'https://login.microsoftonline.com/' . TENANT_ID . '/oauth2/token';
            $graphUser = json_decode($guzzle->post($url, [
                'form_params' => [
                    'client_id' => CLIENT_ID,
                    'client_secret' => CLIENT_SECRET,
                    'resource' => 'https://graph.microsoft.com/',
                    'grant_type' => 'password',
                    'username' => SHARE_POINT_USERNAME,
                    'password' => SHARE_POINT_PASS,
                ],
            ])->getBody()->getContents());
            $user_accessToken = $graphUser->access_token;
            $graph = new Graph();
            $graph->setAccessToken($user_accessToken);
            return $graph;
        } catch (GuzzleException $exc) {
            throw new HttpException(400, Yii::t('app', 'Get an error trying to validate the email sender credentials. Could not send the email'));
        }
    }

    /**
     * @return void
     *
     * More info about Outlook calendar event props on
     * @url https://learn.microsoft.com/en-us/graph/api/resources/event?view=graph-rest-1.0#properties
     * @throws HttpException
     */
    private function setEventRequestData()
    {
        $this->setEventSubject();
        $this->setEventBody();
        $this->setEventStartEnd();
        $this->setEventLocation();
        $this->setEventOrganizer();
        $this->setEventAttenders();
        $this->setEventTransactionId();
        if ($this->reservation->recurring == 1) {
            $this->eventBody['recurrence'] = $this->setOutlookCalendarRecurrenceDetails();
        } elseif ($this->reservation->recurring == 0 && $this->oldReservation['recurring'] == 1) {
            $this->eventBody['recurrence'] = null;
        }
    }

    /**
     * @return void
     * @throws HttpException
     */
    public function setEventSubject()
    {
        if ($this->isNewEvent && empty($this->reservation->title)) {
            throw new HttpException(400, Yii::t('app', 'Missing event subject'));
        }
        if ($this->isNewEvent || $this->reservation->title !== $this->oldReservation['title']) {
            $this->eventBody['subject'] = $this->reservation->title;
        }
    }

    /**
     * @return void
     */
    public function setEventBody()
    {
        $userFullName = Yii::$app->user->identity->fullName();
        $_evtBody = "Organizator: {$userFullName}<br>";
        if (($this->isNewEvent && !empty($this->reservation->details)) || ($this->reservation->details !== $this->oldReservation['details'])) {
            $_evtBody .= "<b>Detalii ședință:</b><br>";
            $_evtBody .= $this->reservation->details;
            $this->eventBody['body'] = [
                "contentType" => "HTML",
                "content" => $_evtBody
            ];
        } elseif ($this->isNewEvent && empty($this->reservation->details)) {
            if ($this->hasExternalAttenders) {
                $_evtBody = "Bună ziua,<br>";
                $_evtBody .= "Sunteți așteptat în data de <b>{$this->reservation->recurrent_from}</b> ";
                $_evtBody .= "la Sediul din Timpuri Noi pentru întâlnirea <b>{$this->reservation->title}</b> ";
                $_evtBody .= "care va avea loc în <b>{$this->calendarReservationLocation}</b>.<br><br>";
                $_evtBody .= "Mulțumim";
            }
            $this->eventBody['body'] = [
                "contentType" => "HTML",
                "content" => $_evtBody
            ];
        }
    }

    /**
     * @return void
     * @throws HttpException
     */
    public function setEventStartEnd()
    {
        $startDateTime = date('Y-m-d\TH:i:s', strtotime($this->reservation->recurrent_from . $this->reservation->check_in));
        $oldStartDateTime = date('Y-m-d\TH:i:s', strtotime($this->oldReservation['recurrent_from'] . $this->oldReservation['check_in']));
        $stopDateTime = date('Y-m-d\TH:i:s', strtotime($this->reservation->recurrent_from . $this->reservation->check_out));
        $oldStopDateTime = date('Y-m-d\TH:i:s', strtotime($this->oldReservation['recurrent_from'] . $this->oldReservation['check_out']));
        if ($this->isNewEvent &&
            (
                empty($this->reservation->recurrent_from) ||
                empty($this->reservation->check_in) ||
                empty($this->reservation->check_out)
            )) {
            throw new HttpException('400', Yii::t('app', 'Missing event start or stop'));
        }
        if ($this->isNewEvent || ($startDateTime !== $oldStartDateTime)) {
            $this->eventBody['start'] = [
                "dateTime" => $startDateTime,
                "timeZone" => $this->calendarTimeZone
            ];
        }
        if ($this->isNewEvent || ($stopDateTime !== $oldStopDateTime)) {
            $this->eventBody['end'] = [
                "dateTime" => $stopDateTime,
                "timeZone" => $this->calendarTimeZone
            ];
        }
    }

    /**
     * @return void
     * @throws HttpException
     */
    public function setEventLocation()
    {
        if ($this->isNewEvent && empty($this->calendarReservationLocation)) {
            throw new HttpException(400, Yii::t('app', 'Missing event location'));
        }
        if ($this->isNewEvent)
            $this->eventBody['location'] = [
                "displayName" => $this->calendarReservationLocation
            ];
    }

    /**
     * @return void
     */
    public function setEventOrganizer()
    {
        if ($this->isNewEvent)
            $this->eventBody['organizer'] = [
                'emailAddress' => [
                    'name' => Yii::$app->user->identity->fullName(),
                    'address' => Yii::$app->user->identity->email
                ]
            ];
    }

    /**
     * @return void
     */
    public function setEventAttenders()
    {
        if ($this->hasAttenders || $this->hasExternalAttenders)
            $this->eventBody['attendees'] = $this->calendarAttenders;
    }

    /**
     * @return void
     */
    public function setEventTransactionId()
    {
        if ($this->isNewEvent)
            $this->eventBody['transactionId'] = "{$this->reservation->id}-{$this->reservation->common_identifier}";
            $this->eventBody['responseRequested'] = true;
    }

    /**
     * @param string $value
     * @return void
     */
    public function setCalendarTimeZone($value)
    {
        $this->calendarTimeZone = $value;
    }

    /**
     * @param string $value
     * @return void
     */
    public function setIsPartOfSeries($value)
    {
        $this->isPartOfSeries = $value;
    }

    /**
     * @param string $value
     * @return void
     */
    public function setInitialEventStart($value)
    {
        $this->initialEventStart = $value;
    }

    /**
     * @param string $value
     * @return void
     */
    public function setInitialEventStop($value)
    {
        $this->initialEventStop = $value;
    }

    /**
     * @param $value
     * @return void
     */
    public function setNewOccurrence($value)
    {
        $this->newOccurrence = $value;
    }

    /**
     * @param RoomReservation $value
     * @return void
     */
    public function setOldReservation($value)
    {
        $this->oldReservation = $value;
    }

    /**
     * @return RoomReservation|null
     */
    public function getOldReservation()
    {
        return $this->oldReservation;
    }

    /**
     * @return RoomReservation|null
     */
    public function getReservation()
    {
        return $this->reservation;
    }

    /**
     * @param RRule $value
     * @return void
     */
    public function setCalendarRrule($value)
    {
        $this->calendarRrule = $value;
    }
}