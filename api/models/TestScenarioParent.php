<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "test_scenario".
 *
 * @property int $id
 * @property int $order
 * @property string|null $code
 * @property string $name
 * @property string|null $description
 * @property int $product_id
 * @property int $test_cases
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 * @property int $deleted
 *
 * @property TestCase[] $testCases
 * @property TestCaseHistory[] $testCaseHistories
 * @property TestCaseOutput[] $testCaseOutputs
 * @property Product $product
 */
class TestScenarioParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'test_scenario';
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
            [['order', 'product_id', 'test_cases', 'added_by', 'updated_by', 'deleted'], 'integer'],
            [['name', 'product_id', 'added', 'added_by'], 'required'],
            [['description'], 'string'],
            [['added', 'updated'], 'safe'],
            [['code'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 1024],
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
            'order' => Yii::t('app', 'Order'),
            'code' => Yii::t('app', 'Code'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'product_id' => Yii::t('app', 'Product ID'),
            'test_cases' => Yii::t('app', 'Test Cases'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'deleted' => Yii::t('app', 'Deleted'),
        ];
    }

    /**
     * Gets query for [[TestCases]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestCases()
    {
        return $this->hasMany(TestCase::className(), ['test_scenario_id' => 'id']);
    }

    /**
     * Gets query for [[TestCaseHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestCaseHistories()
    {
        return $this->hasMany(TestCaseHistory::className(), ['test_scenario_id' => 'id']);
    }

    /**
     * Gets query for [[TestCaseOutputs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestCaseOutputs()
    {
        return $this->hasMany(TestCaseOutput::className(), ['test_scenario_id' => 'id']);
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
}
