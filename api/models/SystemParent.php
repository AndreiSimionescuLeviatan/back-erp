<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "system".
 *
 * @property int $id
 * @property int $company_id beneficiarul sistemului
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $current_version
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Changelog[] $changelogs
 * @property Domain[] $domains
 * @property Product[] $products
 * @property ProductComponent[] $productComponents
 * @property ProjectSystemDomain[] $projectSystemDomains
 */
class SystemParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'system';
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
            [['company_id', 'code', 'name', 'current_version', 'added', 'added_by'], 'required'],
            [['company_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['description'], 'string'],
            [['added', 'updated'], 'safe'],
            [['code', 'current_version'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 1024],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'company_id' => Yii::t('app', 'Company ID'),
            'code' => Yii::t('app', 'Code'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'current_version' => Yii::t('app', 'Current Version'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Changelogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChangelogs()
    {
        return $this->hasMany(Changelog::className(), ['system_id' => 'id']);
    }

    /**
     * Gets query for [[Domains]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDomains()
    {
        return $this->hasMany(Domain::className(), ['system_id' => 'id']);
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['system_id' => 'id']);
    }

    /**
     * Gets query for [[ProductComponents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductComponents()
    {
        return $this->hasMany(ProductComponent::className(), ['system_id' => 'id']);
    }

    /**
     * Gets query for [[ProjectSystemDomains]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectSystemDomains()
    {
        return $this->hasMany(ProjectSystemDomain::className(), ['system_id' => 'id']);
    }
}
