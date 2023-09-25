<?php

namespace backend\modules\entity\models;

use Yii;

/**
 * This is the model class for table "entity_action_category".
 *
 * @property int $id
 * @property int $entity_id
 * @property int $category_check_id
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class EntityActionCategoryParent extends EntityActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'entity_action_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['entity_id', 'category_check_id', 'added', 'added_by'], 'required'],
            [['entity_id', 'category_check_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('entity', 'ID'),
            'entity_id' => Yii::t('entity', 'Entity ID'),
            'category_check_id' => Yii::t('entity', 'Category Check ID'),
            'deleted' => Yii::t('entity', 'Deleted'),
            'added' => Yii::t('entity', 'Added'),
            'added_by' => Yii::t('entity', 'Added By'),
            'updated' => Yii::t('entity', 'Updated'),
            'updated_by' => Yii::t('entity', 'Updated By'),
        ];
    }
}
