<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "test_case".
 *
 * @property int $id
 * @property int $order
 * @property int $test_scenario_id
 * @property int $product_id
 * @property string|null $code
 * @property string $name
 * @property int $test_case_outputs
 * @property int|null $total_ran
 * @property int|null $total_passed
 * @property int|null $status 0: failed; 1: passed; 2: unknown;
 * @property string|null $description
 * @property int $category_test 1: UserAcceptance; 2: System; 3: Integration; 4: Unit
 * @property int $case_type 1:  Accesarea functionalitatii; 2: Validarea input-ului; 3: Controlul accesului;
 * @property string|null $preconditions
 * @property string|null $test_data
 * @property string|null $test_steps
 * @property string|null $expected_results
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 * @property int $deleted
 *
 * @property Product $product
 * @property TestScenario $testScenario
 * @property TestCaseHistory[] $testCaseHistories
 * @property TestCaseHistoryOutput[] $testCaseHistoryOutputs
 * @property TestCaseOutput[] $testCaseOutputs
 */
class TestCaseParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'test_case';
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
            [['order', 'test_scenario_id', 'product_id', 'test_case_outputs', 'total_ran', 'total_passed', 'status', 'category_test', 'case_type', 'added_by', 'updated_by', 'deleted'], 'integer'],
            [['test_scenario_id', 'product_id', 'name', 'category_test', 'case_type', 'added', 'added_by'], 'required'],
            [['description'], 'string'],
            [['added', 'updated'], 'safe'],
            [['code'], 'string', 'max' => 255],
            [['name', 'preconditions', 'test_data', 'test_steps', 'expected_results'], 'string', 'max' => 1024],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
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
            'order' => Yii::t('app', 'Order'),
            'test_scenario_id' => Yii::t('app', 'Test Scenario ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'code' => Yii::t('app', 'Code'),
            'name' => Yii::t('app', 'Name'),
            'test_case_outputs' => Yii::t('app', 'Test Case Outputs'),
            'total_ran' => Yii::t('app', 'Total Ran'),
            'total_passed' => Yii::t('app', 'Total Passed'),
            'status' => Yii::t('app', 'Status'),
            'description' => Yii::t('app', 'Description'),
            'category_test' => Yii::t('app', 'Category Test'),
            'case_type' => Yii::t('app', 'Case Type'),
            'preconditions' => Yii::t('app', 'Preconditions'),
            'test_data' => Yii::t('app', 'Test Data'),
            'test_steps' => Yii::t('app', 'Test Steps'),
            'expected_results' => Yii::t('app', 'Expected Results'),
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
     * Gets query for [[TestScenario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestScenario()
    {
        return $this->hasOne(TestScenario::className(), ['id' => 'test_scenario_id']);
    }

    /**
     * Gets query for [[TestCaseHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestCaseHistories()
    {
        return $this->hasMany(TestCaseHistory::className(), ['test_case_id' => 'id']);
    }

    /**
     * Gets query for [[TestCaseHistoryOutputs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestCaseHistoryOutputs()
    {
        return $this->hasMany(TestCaseHistoryOutput::className(), ['test_case_id' => 'id']);
    }

    /**
     * Gets query for [[TestCaseOutputs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestCaseOutputs()
    {
        return $this->hasMany(TestCaseOutput::className(), ['test_case_id' => 'id']);
    }
}
