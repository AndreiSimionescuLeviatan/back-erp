<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "equipment".
 *
 * @property int $id
 * @property int $category_id
 * @property int $subcategory_id
 * @property string $code
 * @property int $speciality_id
 * @property int|null $brand_id Furnizor
 * @property int $measure_unit_id
 * @property int $equipment_type 1 - equipment; 2- dotare(folosit doar pt Arhitectura)
 * @property string $long_name denumire lucrare
 * @property string $short_name Denumire Utilaj, echipament tehnologic 
 * @property string|null $technical_parameters Parametri tehnici şi funcţionali
 * @property string|null $performance_specs_ssm Specificaţii de performanţă şi condiţii privind siguranţa în exploatare
 * @property string|null $compliance_conditions_stas Condiţii privind  conformitatea cu standardele relevante
 * @property string|null $warranty_conditions Condiţii de garanţie şi postgaranţie
 * @property string|null $other_technical_conditions Alte condiţii cu caracter tehnic
 * @property string|null $unit_price_total
 * @property string|null $supplier_tech_sheet FISE TEHNICE Furnizor
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property CentralizerEquipment[] $centralizerEquipments
 * @property CentralizerFeatures[] $centralizerFeatures
 * @property CentralizerFitting[] $centralizerFittings
 * @property EquipmentCategory $category
 * @property EquipmentCategory $subcategory
 * @property MeasureUnit $measureUnit
 * @property EquipmentBeneficiaryPriceHistory[] $equipmentBeneficiaryPriceHistories
 * @property EquipmentProcurementPriceHistory[] $equipmentProcurementPriceHistories
 * @property EquipmentQuantity[] $equipmentQuantities
 */
class EquipmentParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'equipment';
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
            [['category_id', 'subcategory_id', 'code', 'speciality_id', 'measure_unit_id', 'long_name', 'short_name', 'added', 'added_by'], 'required'],
            [['category_id', 'subcategory_id', 'speciality_id', 'brand_id', 'measure_unit_id', 'equipment_type', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['long_name', 'short_name', 'technical_parameters', 'performance_specs_ssm', 'compliance_conditions_stas', 'warranty_conditions', 'other_technical_conditions', 'unit_price_total', 'supplier_tech_sheet'], 'string'],
            [['added', 'updated'], 'safe'],
            [['code'], 'string', 'max' => 32],
            [['code'], 'unique'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => EquipmentCategory::className(), 'targetAttribute' => ['category_id' => 'id']],
            [['subcategory_id'], 'exist', 'skipOnError' => true, 'targetClass' => EquipmentCategory::className(), 'targetAttribute' => ['subcategory_id' => 'id']],
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
            'category_id' => Yii::t('app', 'Category ID'),
            'subcategory_id' => Yii::t('app', 'Subcategory ID'),
            'code' => Yii::t('app', 'Code'),
            'speciality_id' => Yii::t('app', 'Speciality ID'),
            'brand_id' => Yii::t('app', 'Brand ID'),
            'measure_unit_id' => Yii::t('app', 'Measure Unit ID'),
            'equipment_type' => Yii::t('app', 'Equipment Type'),
            'long_name' => Yii::t('app', 'Long Name'),
            'short_name' => Yii::t('app', 'Short Name'),
            'technical_parameters' => Yii::t('app', 'Technical Parameters'),
            'performance_specs_ssm' => Yii::t('app', 'Performance Specs Ssm'),
            'compliance_conditions_stas' => Yii::t('app', 'Compliance Conditions Stas'),
            'warranty_conditions' => Yii::t('app', 'Warranty Conditions'),
            'other_technical_conditions' => Yii::t('app', 'Other Technical Conditions'),
            'unit_price_total' => Yii::t('app', 'Unit Price Total'),
            'supplier_tech_sheet' => Yii::t('app', 'Supplier Tech Sheet'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[CentralizerEquipments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCentralizerEquipments()
    {
        return $this->hasMany(CentralizerEquipment::className(), ['equipment_id' => 'id']);
    }

    /**
     * Gets query for [[CentralizerFeatures]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCentralizerFeatures()
    {
        return $this->hasMany(CentralizerFeatures::className(), ['equipment_id' => 'id']);
    }

    /**
     * Gets query for [[CentralizerFittings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCentralizerFittings()
    {
        return $this->hasMany(CentralizerFitting::className(), ['equipment_id' => 'id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(EquipmentCategory::className(), ['id' => 'category_id']);
    }

    /**
     * Gets query for [[Subcategory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubcategory()
    {
        return $this->hasOne(EquipmentCategory::className(), ['id' => 'subcategory_id']);
    }

    /**
     * Gets query for [[MeasureUnit]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeasureUnit()
    {
        return $this->hasOne(MeasureUnit::className(), ['id' => 'measure_unit_id']);
    }

    /**
     * Gets query for [[EquipmentBeneficiaryPriceHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEquipmentBeneficiaryPriceHistories()
    {
        return $this->hasMany(EquipmentBeneficiaryPriceHistory::className(), ['equipment_id' => 'id']);
    }

    /**
     * Gets query for [[EquipmentProcurementPriceHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEquipmentProcurementPriceHistories()
    {
        return $this->hasMany(EquipmentProcurementPriceHistory::className(), ['equipment_id' => 'id']);
    }

    /**
     * Gets query for [[EquipmentQuantities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEquipmentQuantities()
    {
        return $this->hasMany(EquipmentQuantity::className(), ['equipment_id' => 'id']);
    }
}
