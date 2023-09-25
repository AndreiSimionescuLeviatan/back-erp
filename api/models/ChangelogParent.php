<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "changelog".
 *
 * @property int $id
 * @property int $product_id
 * @property int $system_id
 * @property int $domain_id
 * @property int $product_history_id
 * @property int $type 1-create_feature, 2-update_feature, 3-delete_feature, 4-bug
 * @property string $name
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Domain $domain
 * @property ProductHistory $productHistory
 * @property Product $product
 * @property System $system
 */
class ChangelogParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'changelog';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_pmp_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'system_id', 'domain_id', 'product_history_id', 'type', 'name', 'added', 'added_by'], 'required'],
            [['product_id', 'system_id', 'domain_id', 'product_history_id', 'type', 'added_by', 'updated_by'], 'integer'],
            [['name'], 'string'],
            [['added', 'updated'], 'safe'],
            [['domain_id'], 'exist', 'skipOnError' => true, 'targetClass' => Domain::className(), 'targetAttribute' => ['domain_id' => 'id']],
            [['product_history_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductHistory::className(), 'targetAttribute' => ['product_history_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['system_id'], 'exist', 'skipOnError' => true, 'targetClass' => System::className(), 'targetAttribute' => ['system_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'system_id' => Yii::t('app', 'System ID'),
            'domain_id' => Yii::t('app', 'Domain ID'),
            'product_history_id' => Yii::t('app', 'Product History ID'),
            'type' => Yii::t('app', 'Type'),
            'name' => Yii::t('app', 'Name'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Domain]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDomain()
    {
        return $this->hasOne(Domain::className(), ['id' => 'domain_id']);
    }

    /**
     * Gets query for [[ProductHistory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductHistory()
    {
        return $this->hasOne(ProductHistory::className(), ['id' => 'product_history_id']);
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * Gets query for [[System]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSystem()
    {
        return $this->hasOne(System::className(), ['id' => 'system_id']);
    }
}
