<?php

namespace backend\modules\crm\models\search;

use common\components\AppHelper;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\modules\crm\models\Brand;

/**
 * BrandSearch represents the model behind the search form of `backend\modules\crm\models\Brand`.
 */
class BrandSearch extends Brand
{
    public $pageSize;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['name', 'added', 'updated', 'deleted'], 'safe'],
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
        $query = Brand::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'deleted' => $this->deleted,
            'added' => $this->added,
            'added_by' => $this->added_by,
            'updated' => $this->updated,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'pagination' => [
            'pageSize' => $params['BrandSearch']['pageSize'] ?? 20,
        ],
            'sort' => [
            'defaultOrder' => ['name' => SORT_ASC],
            'attributes' => [
                'id' => [
                    'asc' => ['id' => SORT_ASC],
                    'desc' => ['id' => SORT_DESC],
                ],
                'name' => [
                    'asc' => ['name' => SORT_ASC],
                    'desc' => ['name' => SORT_DESC],
                ],
                'added_by' => [
                    'asc' => ['added' => SORT_ASC],
                    'desc' => ['added' => SORT_DESC],
                ],
                'updated_by' => [
                    'asc' => ['updated' => SORT_ASC],
                    'desc' => ['updated' => SORT_DESC],
                ],
            ],
        ]
    ]);

        if (AppHelper::checkPermissionViewDeletedEntities($_GET['BrandSearch']['deleted'] ?? '', 'activateBrand')) {
            $query->andFilterWhere(['<>', 'deleted', 0]);
        } else {
            $query->andFilterWhere(['deleted' => 0]);
        }

        return $dataProvider;
    }
}
