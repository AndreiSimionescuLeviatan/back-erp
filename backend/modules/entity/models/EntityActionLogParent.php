<?php

namespace backend\modules\entity\models;

use Yii;

/**
 * This is the model class for table "entity_action_log".
 *
 * @property int $id
 * @property int $entity_action_id
 * @property int $entity_operation_id
 * @property string|null $old_value
 * @property string|null $new_value
 * @property string $added
 * @property int $added_by
 */
class EntityActionLogParent extends EntityActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_ENTITY . '.entity_action_log';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
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
            [['entity_action_id', 'entity_operation_id', 'added', 'added_by'], 'required'],
            [['entity_action_id', 'entity_operation_id', 'added_by'], 'integer'],
            [['added'], 'safe'],
            [['old_value', 'new_value'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('entity', 'ID'),
            'entity_action_id' => Yii::t('entity', 'Entity Action ID'),
            'entity_operation_id' => Yii::t('entity', 'Entity Operation ID'),
            'old_value' => Yii::t('entity', 'Old value'),
            'new_value' => Yii::t('entity', 'New value'),
            'added' => Yii::t('entity', 'Added'),
            'added_by' => Yii::t('entity', 'Added By'),
        ];
    }
}
