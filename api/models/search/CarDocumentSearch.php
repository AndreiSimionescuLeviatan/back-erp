<?php

namespace api\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\models\CarDocument;

/**
 * CarDocumentSearch represents the model behind the search form of `api\models\CarDocument`.
 */
class CarDocumentSearch extends CarDocument
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'car_id', 'rca_company_id', 'casco_company_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['rca_valid_until', 'rca_agent', 'casco_valid_until', 'itp_valid_until', 'vignette_valid_until', 'added', 'updated'], 'safe'],
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
        $query = CarDocument::find();

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
            'rca_company_id' => $this->rca_company_id,
            'rca_valid_until' => $this->rca_valid_until,
            'casco_company_id' => $this->casco_company_id,
            'casco_valid_until' => $this->casco_valid_until,
            'itp_valid_until' => $this->itp_valid_until,
            'vignette_valid_until' => $this->vignette_valid_until,
            'deleted' => $this->deleted,
            'added' => $this->added,
            'added_by' => $this->added_by,
            'updated' => $this->updated,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'rca_agent', $this->rca_agent]);

        return $dataProvider;
    }
}
