<?php

namespace backend\modules\crm\models\search;

use common\components\AppHelper;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\modules\crm\models\Company;

/**
 * CompanySearch represents the model behind the search form of `backend\modules\crm\models\Company`.
 */
class CompanySearch extends Company
{
    public $pageSize;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'country_id', 'city_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['code', 'name', 'short_name', 'cui', 'reg_number', 'address', 'added', 'updated'], 'safe'],
            ['pageSize', 'in', 'allowArray' => true, 'range' => \Yii::$app->params['pagination']]
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
        $query = Company::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $params['CompanySearch']['pageSize'] ?? 20,
            ],
        ]);

        $this->deleted = 0;
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'country_id' => $this->country_id,
            'city_id' => $this->city_id,
            'deleted' => $this->deleted,
            'added' => $this->added,
            'added_by' => $this->added_by,
            'updated' => $this->updated,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'short_name', $this->short_name])
            ->andFilterWhere(['like', 'cui', $this->cui])
            ->andFilterWhere(['like', 'reg_number', $this->reg_number])
            ->andFilterWhere(['like', 'address', $this->address]);

        if (AppHelper::checkPermissionViewDeletedEntities($_GET['CompanySearch']['deleted'] ?? '', 'activateCompany')) {
            $query->andFilterWhere(['<>', 'deleted', 0]);
        } else {
            $query->andFilterWhere(['deleted' => 0]);
        }

        return $dataProvider;
    }
}
