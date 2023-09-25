<?php

namespace api\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\models\CarDetail;

/**
 * CarDetailSearch represents the model behind the search form of `api\models\CarDetail`.
 */
class CarDetailSearch extends CarDetail
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'car_id', 'fuel_ring_company_id', 'fuel_card_company_id', 'gps_company_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['observations', 'added', 'updated'], 'safe'],
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
     */
    public function search($params)
    {
        $query = CarDetail::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'fuel_ring_company_id' => $this->fuel_ring_company_id,
            'fuel_card_company_id' => $this->fuel_card_company_id,
            'gps_company_id' => $this->gps_company_id,
            'deleted' => $this->deleted,
            'added' => $this->added,
            'added_by' => $this->added_by,
            'updated' => $this->updated,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'observations', $this->observations]);

        return $dataProvider;
    }
}
