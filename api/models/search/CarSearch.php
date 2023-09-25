<?php

namespace api\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\models\Car;

/**
 * CarSearch represents the model behind the search form of `api\models\Car`.
 * CarSearch model serves the purpose of defining which properties and values are allowed for filtering
 */
class CarSearch extends Car
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'gps_car_id', 'brand_id', 'model_id', 'fabrication_year', 'fuel_id', 'company_id', 'acquisition_type', 'holder_id', 'user_id', 'status', 'added_by', 'updated_by'], 'integer'],
            [['plate_number', 'vin', 'contract_number', 'color'], 'string'],
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
        $query = Car::find();

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
            'gps_car_id' => $this->gps_car_id,
            'brand_id' => $this->brand_id,
            'model_id' => $this->model_id,
            'fabrication_year' => $this->fabrication_year,
            'fuel_id' => $this->fuel_id,
            'company_id' => $this->company_id,
            'acquisition_type' => $this->acquisition_type,
            'holder_id' => $this->holder_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'added_by' => $this->added_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'plate_number', $this->plate_number])
            ->andFilterWhere(['like', 'vin', $this->vin])
            ->andFilterWhere(['like', 'contract_number', $this->contract_number])
            ->andFilterWhere(['like', 'color', $this->color]);

        return $dataProvider;
    }
}
