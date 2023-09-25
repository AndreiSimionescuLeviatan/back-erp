<?php

namespace backend\modules\crm\models;

use Yii;

/**
 * This is the model class for table "entity_domain".
 *
 * @property int $id
 * @property int|null $domain_id
 * @property int|null $entity_id
 * @property int|null $subdomain_id
 * @property int $item_id Can be any item(article, equipment, company...) from application
 * @property string $added
 * @property int $added_by
 */
class EntityDomainParent extends CrmActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'entity_domain';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['domain_id', 'entity_id', 'subdomain_id', 'item_id', 'added_by'], 'integer'],
            [['item_id', 'added', 'added_by'], 'required'],
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
            'domain_id' => Yii::t('crm', 'Domain ID'),
            'entity_id' => Yii::t('crm', 'Entity ID'),
            'subdomain_id' => Yii::t('crm', 'Subdomain ID'),
            'item_id' => Yii::t('crm', 'Item ID'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
        ];
    }
}
