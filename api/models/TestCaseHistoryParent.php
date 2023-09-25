<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "test_case_history".
 *
 * @property int $id
 * @property int $product_id
 * @property int $product_version_id
 * @property int $test_scenario_id
 * @property int $test_case_id
 * @property int $result 0: failed; 1: passed
 * @property string|null $observations
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 * @property int $deleted
 *
 * @property Product $product
 * @property ProductHistory $productVersion
 * @property TestCase $testCase
 * @property TestScenario $testScenario
 * @property TestCaseHistoryOutput[] $testCaseHistoryOutputs
 */
class TestCaseHistoryParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'test_case_history';
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
            [['product_id', 'product_version_id', 'test_scenario_id', 'test_case_id', 'result', 'added', 'added_by'], 'required'],
            [['product_id', 'product_version_id', 'test_scenario_id', 'test_case_id', 'result', 'added_by', 'updated_by', 'deleted'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['observations'], 'string', 'max' => 255],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['product_version_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductHistory::className(), 'targetAttribute' => ['product_version_id' => 'id']],
            [['test_case_id'], 'exist', 'skipOnError' => true, 'targetClass' => TestCase::className(), 'targetAttribute' => ['test_case_id' => 'id']],
            [['test_scenario_id'], 'exist', 'skipOnError' => true, 'targetClass' => TestScenario::className(), 'targetAttribute' => ['test_scenario_id' => 'id']],
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
            'test_scenario_id' => Yii::t('app', 'Test Scenario ID'),
            'test_case_id' => Yii::t('app', 'Test Case ID'),
            'result' => Yii::t('app', 'Result'),
            'observations' => Yii::t('app', 'Observations'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'deleted' => Yii::t('app', 'Deleted'),
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
     * Gets query for [[TestCase]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestCase()
    {
        return $this->hasOne(TestCase::className(), ['id' => 'test_case_id']);
    }

    /**
     * Gets query for [[TestScenario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestScenario()
    {
        return $this->hasOne(TestScenario::className(), ['id' => 'test_scenario_id']);
    }

    /**
     * Gets query for [[TestCaseHistoryOutputs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestCaseHistoryOutputs()
    {
        return $this->hasMany(TestCaseHistoryOutput::className(), ['test_case_history_id' => 'id']);
    }
}
