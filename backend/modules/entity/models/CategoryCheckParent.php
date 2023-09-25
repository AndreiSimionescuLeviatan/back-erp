<?php

namespace backend\modules\entity\models;

use Yii;

/**
 * This is the model class for table "category_check".
 *
 * @property int $id
 * @property string $name
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class CategoryCheckParent extends EntityActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category_check';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'added', 'added_by'], 'required'],
            [['deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('entity', 'ID'),
            'name' => Yii::t('entity', 'Name'),
            'deleted' => Yii::t('entity', 'Deleted'),
            'added' => Yii::t('entity', 'Added'),
            'added_by' => Yii::t('entity', 'Added By'),
            'updated' => Yii::t('entity', 'Updated'),
            'updated_by' => Yii::t('entity', 'Updated By'),
        ];
    }
}
