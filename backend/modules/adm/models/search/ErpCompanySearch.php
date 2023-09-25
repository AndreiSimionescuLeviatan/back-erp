<?php

namespace backend\modules\adm\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\modules\adm\models\ErpCompany;

/**
 * ErpCompanySearch represents the model behind the search form of `backend\modules\adm\models\ErpCompany`.
 */
class ErpCompanySearch extends ErpCompany
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'general_manager_id', 'deputy_general_manager_id', 'technical_manager_id', 'executive_manager_id', 'added_by', 'updated_by', 'deleted'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['latitude', 'longitude', 'radius'], 'number'],
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
        $query = ErpCompany::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->deleted = 0;
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'company_id' => $this->company_id,
            'general_manager_id' => $this->general_manager_id,
            'deputy_general_manager_id' => $this->deputy_general_manager_id,
            'technical_manager_id' => $this->technical_manager_id,
            'executive_manager_id' => $this->executive_manager_id,
            'added' => $this->added,
            'added_by' => $this->added_by,
            'updated' => $this->updated,
            'updated_by' => $this->updated_by,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius' => $this->radius,
            'deleted' => $this->deleted,
        ]);

        return $dataProvider;
    }
}
