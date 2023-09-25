<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "test_case_output".
 *
 * @property int $id
 * @property int $product_id
 * @property int $test_scenario_id
 * @property int $test_case_id
 * @property string $name
 * @property string|null $description
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 * @property int $deleted
 *
 * @property Product $product
 * @property TestCase $testCase
 * @property TestScenario $testScenario
 */
class TestCaseOutputParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'test_case_output';
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
            [['product_id', 'test_scenario_id', 'test_case_id', 'name', 'added', 'added_by'], 'required'],
            [['product_id', 'test_scenario_id', 'test_case_id', 'added_by', 'updated_by', 'deleted'], 'integer'],
            [['description'], 'string'],
            [['added', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 1024],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
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
            'test_scenario_id' => Yii::t('app', 'Test Scenario ID'),
            'test_case_id' => Yii::t('app', 'Test Case ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
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
}
