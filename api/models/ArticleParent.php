<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "article".
 *
 * @property int $id
 * @property int $speciality_id
 * @property int $category_id
 * @property int $subcategory_id
 * @property string $code
 * @property string $name
 * @property string $measure_unit_name
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property ArticleCategory $category
 * @property ArticleCategory $subcategory
 * @property MeasureUnit $measureUnit
 * @property ArticleBeneficiaryPriceHistory[] $articleBeneficiaryPriceHistories
 * @property ArticleProcurementPriceHistory[] $articleProcurementPriceHistories
 * @property ArticleQuantity[] $articleQuantities
 * @property CentralizerArticle[] $centralizerArticles
 */
class ArticleParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_build_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['speciality_id', 'category_id', 'subcategory_id', 'code', 'name', 'measure_unit_id', 'added', 'added_by'], 'required'],
            [['speciality_id', 'category_id', 'subcategory_id', 'measure_unit_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['name'], 'string'],
            [['added', 'updated'], 'safe'],
            [['code'], 'string', 'max' => 32],
            [['code'], 'unique'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ArticleCategory::className(), 'targetAttribute' => ['category_id' => 'id']],
            [['subcategory_id'], 'exist', 'skipOnError' => true, 'targetClass' => ArticleCategory::className(), 'targetAttribute' => ['subcategory_id' => 'id']],
            [['measure_unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => MeasureUnit::className(), 'targetAttribute' => ['measure_unit_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'speciality_id' => Yii::t('app', 'Speciality ID'),
            'category_id' => Yii::t('app', 'Category ID'),
            'subcategory_id' => Yii::t('app', 'Subcategory ID'),
            'code' => Yii::t('app', 'Code'),
            'name' => Yii::t('app', 'Name'),
            'measure_unit_id' => Yii::t('app', 'Measure Unit ID'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ArticleCategory::className(), ['id' => 'category_id']);
    }

    /**
     * Gets query for [[Subcategory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubcategory()
    {
        return $this->hasOne(ArticleCategory::className(), ['id' => 'subcategory_id']);
    }

    /**
     * Gets query for [[ArticleBeneficiaryPriceHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArticleBeneficiaryPriceHistories()
    {
        return $this->hasMany(ArticleBeneficiaryPriceHistory::className(), ['article_id' => 'id']);
    }

    /**
     * Gets query for [[ArticleProcurementPriceHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArticleProcurementPriceHistories()
    {
        return $this->hasMany(ArticleProcurementPriceHistory::className(), ['article_id' => 'id']);
    }

    /**
     * Gets query for [[ArticleQuantities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArticleQuantities()
    {
        return $this->hasMany(ArticleQuantity::className(), ['article_id' => 'id']);
    }

    /**
     * Gets query for [[CentralizerArticles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCentralizerArticles()
    {
        return $this->hasMany(CentralizerArticle::className(), ['article_id' => 'id']);
    }
}
