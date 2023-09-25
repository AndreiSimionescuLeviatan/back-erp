<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "entity_domain".
 *
 * @property int $id
 * @property int $domain_id
 * @property int $entity_id
 * @property int $subdomain_id
 * @property int $item_id
 * @property string $added
 * @property int $added_by
 */
class EntityDomainParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'entity_domain';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_crm_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['domain_id', 'entity_id', 'subdomain_id', 'item_id', 'added', 'added_by'], 'required'],
            [['domain_id', 'entity_id', 'subdomain_id', 'item_id', 'added_by'], 'integer'],
            [['added'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'domain_id' => Yii::t('app', 'Domain ID'),
            'entity_id' => Yii::t('app', 'Entity ID'),
            'subdomain_id' => Yii::t('app', 'Subdomain ID'),
            'item_id' => Yii::t('app', 'Item ID'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
        ];
    }
}
