<?php

namespace backend\modules\entity\models;

use Yii;

/**
 * This is the model class for table "entity".
 *
 * @property int $id
 * @property int $domain_id
 * @property string $name
 * @property string|null $display_column
 * @property string|null $description
 * @property int $find_replace
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class EntityParent extends EntityActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['domain_id', 'name', 'added', 'added_by'], 'required'],
            [['domain_id', 'find_replace', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['display_column'], 'string', 'max' => 250],
            [['description'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('entity', 'ID'),
            'domain_id' => Yii::t('entity', 'Domain ID'),
            'name' => Yii::t('entity', 'Name'),
            'display_column' => Yii::t('entity', 'Display Column'),
            'description' => Yii::t('entity', 'Description'),
            'find_replace' => Yii::t('entity', 'Find Replace'),
            'deleted' => Yii::t('entity', 'Deleted'),
            'added' => Yii::t('entity', 'Added'),
            'added_by' => Yii::t('entity', 'Added By'),
            'updated' => Yii::t('entity', 'Updated'),
            'updated_by' => Yii::t('entity', 'Updated By'),
        ];
    }
}
