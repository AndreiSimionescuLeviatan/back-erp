<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "domain".
 *
 * @property int $id
 * @property int $company_id beneficiarul sistemului
 * @property int $system_id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Changelog[] $changelogs
 * @property System $system
 * @property Product[] $products
 * @property ProductComponent[] $productComponents
 * @property ProjectSystemDomain[] $projectSystemDomains
 */
class DomainParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'domain';
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
            [['company_id', 'system_id', 'code', 'name', 'added', 'added_by'], 'required'],
            [['company_id', 'system_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['description'], 'string'],
            [['added', 'updated'], 'safe'],
            [['code'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['name'], 'unique'],
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
            'company_id' => Yii::t('app', 'Company ID'),
            'system_id' => Yii::t('app', 'System ID'),
            'code' => Yii::t('app', 'Code'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
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
        return $this->hasMany(Changelog::className(), ['domain_id' => 'id']);
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

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['domain_id' => 'id']);
    }

    /**
     * Gets query for [[ProductComponents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductComponents()
    {
        return $this->hasMany(ProductComponent::className(), ['domain_id' => 'id']);
    }

    /**
     * Gets query for [[ProjectSystemDomains]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectSystemDomains()
    {
        return $this->hasMany(ProjectSystemDomain::className(), ['domain_id' => 'id']);
    }
}
