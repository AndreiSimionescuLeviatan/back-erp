<?php

namespace backend\modules\crm\models\search;

use common\components\AppHelper;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\modules\crm\models\BrandModel;

class BrandModelSearch extends BrandModel
{
    public $pageSize;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'brand_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['name', 'added', 'updated', 'pageSize'], 'safe'],
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
        $query = BrandModel::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $params['BrandModelSearch']['pageSize'] ?? 20,
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
            'brand_id' => $this->brand_id,
            'deleted' => $this->deleted,
            'added' => $this->added,
            'added_by' => $this->added_by,
            'updated' => $this->updated,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        if (AppHelper::checkPermissionViewDeletedEntities($_GET['BrandModelSearch']['deleted'] ?? '', 'activateModel')) {
            $query->andFilterWhere(['in', 'deleted', [1]]);
        } else {
            $query->andFilterWhere(['in', 'deleted', [0]]);
        }

        $dataProvider->setSort([
            'defaultOrder' => ['name' => SORT_ASC],
        ]);

        return $dataProvider;
    }
}