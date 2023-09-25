<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "test_case_history_output".
 *
 * @property int $id
 * @property int $product_id
 * @property int $product_version_id
 * @property int $test_case_id
 * @property int $test_case_history_id
 * @property int $test_case_output_id
 * @property string $result
 *
 * @property Product $product
 * @property ProductHistory $productVersion
 * @property TestCaseHistory $testCaseHistory
 * @property TestCase $testCase
 */
class TestCaseHistoryOutputParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'test_case_history_output';
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
            [['product_id', 'product_version_id', 'test_case_id', 'test_case_history_id', 'test_case_output_id', 'result'], 'required'],
            [['product_id', 'product_version_id', 'test_case_id', 'test_case_history_id', 'test_case_output_id'], 'integer'],
            [['result'], 'string', 'max' => 255],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['product_version_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductHistory::className(), 'targetAttribute' => ['product_version_id' => 'id']],
            [['test_case_history_id'], 'exist', 'skipOnError' => true, 'targetClass' => TestCaseHistory::className(), 'targetAttribute' => ['test_case_history_id' => 'id']],
            [['test_case_id'], 'exist', 'skipOnError' => true, 'targetClass' => TestCase::className(), 'targetAttribute' => ['test_case_id' => 'id']],
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
            'product_version_id' => Yii::t('app', 'Product Version ID'),
            'test_case_id' => Yii::t('app', 'Test Case ID'),
            'test_case_history_id' => Yii::t('app', 'Test Case History ID'),
            'test_case_output_id' => Yii::t('app', 'Test Case Output ID'),
            'result' => Yii::t('app', 'Result'),
        ];
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
     * Gets query for [[ProductVersion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductVersion()
    {
        return $this->hasOne(ProductHistory::className(), ['id' => 'product_version_id']);
    }

    /**
     * Gets query for [[TestCaseHistory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestCaseHistory()
    {
        return $this->hasOne(TestCaseHistory::className(), ['id' => 'test_case_history_id']);
    }

    /**
     * Gets query for [[TestCase]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestCase()
    {
        return $this->hasOne(TestCase::className(), ['id' => 'test_case_id']);
    }
}
