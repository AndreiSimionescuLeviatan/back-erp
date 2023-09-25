<?php

namespace backend\modules\entity\models\search;

use backend\modules\entity\models\Domain;
use backend\modules\entity\models\Entity;
use backend\modules\entity\models\EntityAction;
use backend\modules\entity\models\EntityActionLog;
use backend\modules\entity\models\EntityActiveRecord;
use yii\data\ArrayDataProvider;

class EntityActionLogSearch extends EntityActionLog
{
    public $domain_id;
    public $entity_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['domain_id', 'entity_id', 'added', 'added_by'], 'safe'],
            [['domain_id'], 'exist', 'skipOnError' => true, 'targetClass' => Domain::className(), 'targetAttribute' => ['domain_id' => 'id']],
            [['entity_id'], 'exist', 'skipOnError' => true, 'targetClass' => Entity::className(), 'targetAttribute' => ['entity_id' => 'id']],
        ];
    }

    public function search($params)
    {
        $this->load($params);
        $where = '';
        $conditionsSQL = [];
        $conditions = self::getFilter([
            'd.`id`' => $this->domain_id,
            'e.`id`' => $this->entity_id,
            'a.`added_by`' => $this->added_by,
        ]);

        if (!empty($conditions)) {
            $conditionsSQL[] = $conditions;
        }

        if (!empty($this->added)) {
            $conditionsSQL[] = "DATE_FORMAT(a.`added`, '%Y-%m-%d') = DATE_FORMAT('{$this->added}', '%Y-%m-%d')";
        }

        if (count($conditionsSQL) > 0) {
            $where = ' WHERE ' . implode(' AND ', $conditionsSQL);
        }

        $allModels = EntityActiveRecord::queryAll(EntityAction::getSqlAction($where));

        $dataProvider = new ArrayDataProvider([
            'allModels' => $allModels,
            'pagination' => [
                'pageSize' => $params['EntityActionLogSearch']['pageSize'] ?? 20,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id' => [
                        'asc' => ['id' => SORT_ASC],
                        'desc' => ['id' => SORT_DESC],
                    ],
                    'domain_id' => [
                        'asc' => ['domain_description' => SORT_ASC],
                        'desc' => ['domain_description' => SORT_DESC],
                    ],
                    'entity_id' => [
                        'asc' => ['entity_description' => SORT_ASC],
                        'desc' => ['entity_description' => SORT_DESC],
                    ],
                    'added' => [
                        'asc' => ['added' => SORT_ASC],
                        'desc' => ['added' => SORT_DESC],
                    ],
                    'added_by' => [
                        'asc' => ['added_by' => SORT_ASC],
                        'desc' => ['added_by' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        return $dataProvider;
    }

}