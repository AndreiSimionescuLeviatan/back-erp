<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property int $project_id
 * @property int $system_id sistemul produsului
 * @property int $domain_id domeniul produsului
 * @property int $product_type_id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $current_version
 * @property int $test_scenarios
 * @property int $test_cases
 * @property int|null $test_cases_ran
 * @property int $test_cases_passed
 * @property int $status 0-NEW; 1-PLAN; 2-IMPLEMENT; 3-PAUSED; 4-MNT
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Changelog[] $changelogs
 * @property Feature[] $features
 * @property Page[] $pages
 * @property Domain $domain
 * @property ProductType $productType
 * @property Project $project
 * @property System $system
 * @property ProductComponent[] $productComponents
 * @property ProductHistory[] $productHistories
 * @property TestCase[] $testCases
 * @property TestCaseHistory[] $testCaseHistories
 * @property TestCaseHistoryOutput[] $testCaseHistoryOutputs
 * @property TestCaseOutput[] $testCaseOutputs
 * @property TestScenario[] $testScenarios
 */
class ProductParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product';
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
            [['project_id', 'system_id', 'domain_id', 'product_type_id', 'code', 'name', 'current_version', 'test_scenarios', 'test_cases', 'test_cases_passed', 'added', 'added_by'], 'required'],
            [['project_id', 'system_id', 'domain_id', 'product_type_id', 'test_scenarios', 'test_cases', 'test_cases_ran', 'test_cases_passed', 'status', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['description'], 'string'],
            [['added', 'updated'], 'safe'],
            [['code', 'current_version'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 1024],
            [['domain_id'], 'exist', 'skipOnError' => true, 'targetClass' => Domain::className(), 'targetAttribute' => ['domain_id' => 'id']],
            [['product_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductType::className(), 'targetAttribute' => ['product_type_id' => 'id']],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::className(), 'targetAttribute' => ['project_id' => 'id']],
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
            'project_id' => Yii::t('app', 'Project ID'),
            'system_id' => Yii::t('app', 'System ID'),
            'domain_id' => Yii::t('app', 'Domain ID'),
            'product_type_id' => Yii::t('app', 'Product Type ID'),
            'code' => Yii::t('app', 'Code'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'current_version' => Yii::t('app', 'Current Version'),
            'test_scenarios' => Yii::t('app', 'Test Scenarios'),
            'test_cases' => Yii::t('app', 'Test Cases'),
            'test_cases_ran' => Yii::t('app', 'Test Cases Ran'),
            'test_cases_passed' => Yii::t('app', 'Test Cases Passed'),
            'status' => Yii::t('app', 'Status'),
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
        return $this->hasMany(Changelog::className(), ['product_id' => 'id']);
    }

    /**
     * Gets query for [[Features]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFeatures()
    {
        return $this->hasMany(Feature::className(), ['product_id' => 'id']);
    }

    /**
     * Gets query for [[Pages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPages()
    {
        return $this->hasMany(Page::className(), ['product_id' => 'id']);
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
     * Gets query for [[ProductType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductType()
    {
        return $this->hasOne(ProductType::className(), ['id' => 'product_type_id']);
    }

    /**
     * Gets query for [[Project]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['id' => 'project_id']);
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
     * Gets query for [[ProductComponents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductComponents()
    {
        return $this->hasMany(ProductComponent::className(), ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductHistories()
    {
        return $this->hasMany(ProductHistory::className(), ['product_id' => 'id']);
    }

    /**
     * Gets query for [[TestCases]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestCases()
    {
        return $this->hasMany(TestCase::className(), ['product_id' => 'id']);
    }

    /**
     * Gets query for [[TestCaseHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestCaseHistories()
    {
        return $this->hasMany(TestCaseHistory::className(), ['product_id' => 'id']);
    }

    /**
     * Gets query for [[TestCaseHistoryOutputs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestCaseHistoryOutputs()
    {
        return $this->hasMany(TestCaseHistoryOutput::className(), ['product_id' => 'id']);
    }

    /**
     * Gets query for [[TestCaseOutputs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestCaseOutputs()
    {
        return $this->hasMany(TestCaseOutput::className(), ['product_id' => 'id']);
    }

    /**
     * Gets query for [[TestScenarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestScenarios()
    {
        return $this->hasMany(TestScenario::className(), ['product_id' => 'id']);
    }
}
