<?php

namespace api\models\search;

use api\models\Journey;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\models\JourneyParent;

/**
 * JourneySearch represents the model behind the search form of `api\models\JourneyParent`.
 */
class JourneySearch extends JourneyParent
{
    public $interval;
    public $week;
    public $hotspot_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        /**
         * status should be an int, marked as safe because it crashes when validating journey
         * @todo find a way to convert received status from GET request from string to int
         */
        return [
            [['id', 'car_id', 'start_hotspot_id', 'stop_hotspot_id', 'user_id', 'project_id', 'type', 'merged_with_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['distance', 'fuel', 'odo'], 'number'],
            [['status', 'started', 'stopped', 'observation', 'added', 'updated', 'interval', 'week', 'hotspot_id'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     * @throws \Exception
     */
    public function search($params)
    {
        $query = JourneyParent::find();
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'car_id' => $this->car_id,
            'start_hotspot_id' => $this->start_hotspot_id,
            'stop_hotspot_id' => $this->stop_hotspot_id,
            'distance' => $this->distance,
            'fuel' => $this->fuel,
            'odo' => $this->odo,
            'started' => $this->started,
            'stopped' => $this->stopped,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'project_id' => $this->project_id,
            'type' => $this->type,
            'merged_with_id' => $this->merged_with_id,
            'deleted' => $this->deleted,
            'added' => $this->added,
            'added_by' => $this->added_by,
            'updated' => $this->updated,
            'updated_by' => $this->updated_by,
        ]);
        $query->select('id, car_id, start_hotspot_id, merged_with_id, supplementary, time, stop_hotspot_id, distance, started, stopped, status, user_id, project_id, validation_option_id, type, deleted');
        if (isset($params['filter']['status']) && $params['filter']['status'] == 2) {
            $query->andFilterWhere(['deleted' => 1, 'merged_with_id' => 0]);
        } else {
            $query->andFilterWhere(['deleted' => 0]);
        }
        if (array_key_exists('filter', $params) && array_key_exists('interval', $params['filter']) && $params['filter']['interval'] > 0) {
            $query->andFilterWhere($this->parseTimeIntervalFilters($params['filter']['interval']));
        }
        if (array_key_exists('filter', $params) && array_key_exists('hotspot_id', $params['filter']) && $params['filter']['hotspot_id'] > 0) {
            $query->andFilterWhere($this->getWhereConditionByLocationID($params['filter']['hotspot_id']));
        }
        return $dataProvider;
    }

    private function parseTimeIntervalFilters($filterVal)
    {
        if (empty($filterVal) && $filterVal == '') {
            return [];
        }

        return [
            'BETWEEN',
            'started',
            date('Y-m-d H:i:s', strtotime("-{$filterVal} day", time())), date('Y-m-d H:i:s')
        ];
    }

    public static function filterJourneysByWeekDay($weekDays, $journeys)
    {
        $filteredJourneys = [];
        $weekDays = explode(',', $weekDays);
        foreach ($journeys as $journey) {
            $started = Journey::convertDateToWeekDay($journey['start_date']);
            $stopped = Journey::convertDateToWeekDay($journey['end_date']);

            foreach ($weekDays as $weekDay) {
                $weekDay = date('w', strtotime($weekDay));
                if ($weekDay == $started || $weekDay == $stopped) {
                    $startDate = explode(', ', $journey['start_date']);
                    if (empty($startDate[1])) {
                        continue;
                    }
                    $date = explode(' ', $startDate[1]);
                    $tmpDate = explode(' ', $startDate[1]);
                    if (!empty($date[1]) && !empty($tmpDate[1])) {
                        if ($date[1] === 'Iun') {
                            $tmpDate[1] = 'Jun';
                        } else if ($date[1] === 'Iul') {
                            $tmpDate[1] = 'Jul';
                        } else if ($date[1] === 'Ian') {
                            $tmpDate[1] = 'Jan';
                        } else if ($date[1] === 'Mai') {
                            $tmpDate[1] = 'May';
                        }
                    }
                    $startDate[1] = implode(' ', $tmpDate);
                    $filteredJourneys[strtotime("{$startDate[1]} {$journey['start_hour']}")] = $journey;
                }
            }
        }
        return $filteredJourneys;
    }

    private function getWhereConditionByLocationID($hotspotId)
    {
        $this->hotspot_id = null;
        return [
            'OR',
            ['start_hotspot_id' => $hotspotId],
            ['stop_hotspot_id' => $hotspotId],
        ];
    }
}