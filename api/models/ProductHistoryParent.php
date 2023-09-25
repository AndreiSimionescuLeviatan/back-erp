<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "product_history".
 *
 * @property int $id
 * @property int $product_id
 * @property string $version
 * @property string|null $file_name
 * @property string $added
 * @property int $added_by
 *
 * @property Changelog[] $changelogs
 * @property Product $product
 * @property ProductVersionEnvironment[] $productVersionEnvironments
 * @property TestCaseHistory[] $testCaseHistories
 * @property TestCaseHistoryOutput[] $testCaseHistoryOutputs
 */
class ProductHistoryParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_history';
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
            [['product_id', 'version', 'added', 'added_by'], 'required'],
            [['product_id', 'added_by'], 'integer'],
            [['added'], 'safe'],
            [['version'], 'string', 'max' => 11],
            [['file_name'], 'string', 'max' => 255],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
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
            'version' => Yii::t('app', 'Version'),
            'file_name' => Yii::t('app', 'File Name'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
        ];
    }

    /**
     * Gets query for [[Changelogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChangelogs()
    {
        return $this->hasMany(Changelog::className(), ['product_history_id' => 'id']);
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
     * Gets query for [[ProductVersionEnvironments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductVersionEnvironments()
    {
        return $this->hasMany(ProductVersionEnvironment::className(), ['product_history_id' => 'id']);
    }

    /**
     * Gets query for [[TestCaseHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestCaseHistories()
    {
        return $this->hasMany(TestCaseHistory::className(), ['product_version_id' => 'id']);
    }

    /**
     * Gets query for [[TestCaseHistoryOutputs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestCaseHistoryOutputs()
    {
        return $this->hasMany(TestCaseHistoryOutput::className(), ['product_version_id' => 'id']);
    }
}
