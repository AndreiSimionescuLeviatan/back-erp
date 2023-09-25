<?php

namespace backend\modules\entity\models;

use Yii;

/**
 * This is the model class for table "domain".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class DomainParent extends EntityActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_ENTITY . '.domain';
    }

    public static function getDb()
    {
        return Yii::$app->get('ecf_entity_db');
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
            [['name'], 'string', 'max' => 50],
            [['description'], 'string', 'max' => 500],
            [['name'], 'unique'],
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
            'description' => Yii::t('entity', 'Description'),
            'deleted' => Yii::t('entity', 'Deleted'),
            'added' => Yii::t('entity', 'Added'),
            'added_by' => Yii::t('entity', 'Added By'),
            'updated' => Yii::t('entity', 'Updated'),
            'updated_by' => Yii::t('entity', 'Updated By'),
        ];
    }
}
