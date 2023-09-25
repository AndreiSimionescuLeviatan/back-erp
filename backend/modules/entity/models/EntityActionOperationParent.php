<?php

namespace backend\modules\entity\models;

use Yii;

/**
 * This is the model class for table "entity_action_operation".
 *
 * @property int $id
 * @property int|null $action_category_id
 * @property int $entity_change_id
 * @property int|null $entity_source_id
 * @property string $action_sql
 * @property string|null $name_column_change
 * @property string|null $name_column_source
 * @property string|null $condition_sql
 * @property string|null $default_value
 * @property string|null $description
 * @property int $deleted
 * @property int $order_by
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class EntityActionOperationParent extends EntityActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'entity_action_operation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['action_category_id', 'entity_change_id', 'entity_source_id', 'deleted', 'order_by', 'added_by', 'updated_by'], 'integer'],
            [['entity_change_id', 'action_sql', 'added', 'added_by'], 'required'],
            [['added', 'updated'], 'safe'],
            [['action_sql', 'default_value'], 'string', 'max' => 50],
            [['name_column_change', 'name_column_source'], 'string', 'max' => 80],
            [['condition_sql'], 'string', 'max' => 200],
            [['description'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('entity', 'ID'),
            'action_category_id' => Yii::t('entity', 'Action Category ID'),
            'entity_change_id' => Yii::t('entity', 'Entity Change ID'),
            'entity_source_id' => Yii::t('entity', 'Entity Source ID'),
            'action_sql' => Yii::t('entity', 'Action Sql'),
            'name_column_change' => Yii::t('entity', 'Name Column Change'),
            'name_column_source' => Yii::t('entity', 'Name Column Source'),
            'condition_sql' => Yii::t('entity', 'Condition Sql'),
            'default_value' => Yii::t('entity', 'Default Value'),
            'description' => Yii::t('entity', 'Description'),
            'deleted' => Yii::t('entity', 'Deleted'),
            'order_by' => Yii::t('entity', 'Order By'),
            'added' => Yii::t('entity', 'Added'),
            'added_by' => Yii::t('entity', 'Added By'),
            'updated' => Yii::t('entity', 'Updated'),
            'updated_by' => Yii::t('entity', 'Updated By'),
        ];
    }
}
