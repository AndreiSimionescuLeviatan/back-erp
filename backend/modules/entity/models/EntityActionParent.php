<?php

namespace backend\modules\entity\models;

use Yii;

/**
 * This is the model class for table "entity_action".
 *
 * @property int $id
 * @property string $added
 * @property int $added_by
 */
class EntityActionParent extends EntityActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'entity_action';
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
            [['added', 'added_by'], 'required'],
            [['added'], 'safe'],
            [['added_by'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('entity', 'ID'),
            'added' => Yii::t('entity', 'Added'),
            'added_by' => Yii::t('entity', 'Added By'),
        ];
    }
}
