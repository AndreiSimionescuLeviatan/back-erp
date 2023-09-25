<?php

namespace backend\components;

use backend\modules\build\models\ArticleQuantity;
use backend\modules\build\models\Centralizer;
use backend\modules\build\models\EquipmentQuantity;
use backend\modules\build\models\EstimateF2;
use backend\modules\build\models\EstimateF3F4;
use backend\modules\build\models\ItemPriceAnalytics;
use backend\modules\build\models\QuantityList;
use Yii;
use yii\web\BadRequestHttpException;

/**
 * CalculateF2 represents the totality of the calculations for the f2 forms
 * With the help of this class we calculate the totals from the f3 / f4 forms
 * Each function returns an array with keys and default or calculated values
 */
class CalculateF2
{
    /**
     * The table header for the first part
     */
    public $investCostsHeaderText;

    /**
     * The table body with default values for the first part
     */
    public $investmentCostDefaultData;

    /**
     * The table header for the second part
     */
    public $assemblyWorksHeaderText;

    /**
     * The table body with default values for the second part
     */
    public $assemblyWorksDefaultData;

    /**
     * The table header for the last part
     */
    public $procurementHeaderText;

    /**
     * The table body with default values for the last part
     */
    public $procurementDefaultData;

    /**
     * The table body with calculated values for the first part
     */
    public $calculatedInvestmentCost;

    /**
     * The table body with calculated values for the second part
     */
    public $calculatedAssemblyWorks;

    /**
     * The table body with calculated values for the last part
     */
    public $calculatedProcurement;

    /**
     * Represent the total of all parts
     */
    public $f2TotalValue = [
        'defaultValue' => '0.00',
        'TVAValue' => '0.00',
        'defaultValueWithTVA' => '0.00'
    ];

    /**
     * Represent values of TVA
     */
    public $TVA = 19;
    public $decimals = 4;

    /**
     * This function returns an array with default values for the first part of the table
     */
    public function setInvestmentCostDefaultData()
    {
        $this->investCostsHeaderText = Yii::t('app', 'Cap. 4 - THE COST OF THE INVESTMENT ');
        $this->investmentCostDefaultData = [
            '4.1' => [
                'key' => '4.1',
                'name' => Yii::t('app', 'Constructions'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'header-bold style-rows',
                'autoCalculation' => 'construction',
                'recognizeCalculations' => 'total1',
            ],
            '4.1.1' => [
                'key' => '4.1.1',
                'name' => Yii::t('app', 'Earthworks, vertical systematization and exterior arrangements'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'construction'
            ],
            '4.1.2' => [
                'key' => '4.1.2',
                'name' => Yii::t('app', 'Resistance'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'construction'
            ],
            '4.1.3' => [
                'key' => '4.1.3',
                'name' => Yii::t('app', 'Architecture'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'construction'
            ],
            '4.1.4' => [
                'key' => '4.1.4',
                'name' => Yii::t('app', 'Installations'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'onlyForExport' => 'italic&bold',
                'autoCalculation' => 'installations',
                'recognizeCalculations' => 'total1',
            ],
            '4.1.4.1' => [
                'key' => '4.1.4.1',
                'name' => Yii::t('app', 'Electrical installations (strong currents)'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows-italic',
                'recognizeCalculations' => 'installations'
            ],
            '4.1.4.2' => [
                'key' => '4.1.4.2',
                'name' => Yii::t('app', 'Electrical installations (low currents)'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows-italic',
                'recognizeCalculations' => 'installations'
            ],
            '4.1.4.3' => [
                'key' => '4.1.4.3',
                'name' => Yii::t('app', 'Plumbing'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows-italic',
                'recognizeCalculations' => 'installations'
            ],
            '4.1.4.4' => [
                'key' => '4.1.4.4',
                'name' => Yii::t('app', 'Fire extinguishing systems'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows-italic',
                'recognizeCalculations' => 'installations'
            ],
            '4.1.4.5' => [
                'key' => '4.1.4.5',
                'name' => Yii::t('app', 'HVAC installations'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows-italic',
                'recognizeCalculations' => 'installations'
            ],
            '4.1.4.6' => [
                'key' => '4.1.4.6',
                'name' => Yii::t('app', 'Natural gas installations'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows-italic',
                'recognizeCalculations' => 'installations'
            ],
            '4.1.5' => [
                'key' => '4.1.5',
                'name' => Yii::t('app', 'Roads, platforms and alleys'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'construction'
            ],
            '4.1.6' => [
                'key' => '4.1.6',
                'name' => Yii::t('app', 'Tempestizare'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'construction'
            ],
            'TOTAL I' => [
                'key' => 'TOTAL I',
                'name' => 'TOTAL I',
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'total-rows',
                'classTotal' => 'exist',
                'autoCalculation' => 'totalI',
                'recognizeCalculations' => 'total',
            ],
        ];
    }

    /**
     * This function returns an array with default values for the second part of the table
     */
    public function setAssemblyWorksDefaultData()
    {
        $this->assemblyWorksHeaderText = Yii::t('app', 'II. Assembly work');
        $this->assemblyWorksDefaultData = [
            '4.2' => [
                'key' => '4.2',
                'name' => Yii::t('app', 'Installation of machinery, technological and functional equipment'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'header-bold style-rows',
                'autoCalculation' => 'machinery',
                'recognizeCalculations' => 'total2',
            ],
            '4.2.1' => [
                'key' => '4.2.1',
                'name' => Yii::t('app', 'Installation of machinery, technological and functional equipment - electrical installations (strong currents)'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'machinery'
            ],
            '4.2.2' => [
                'key' => '4.2.2',
                'name' => Yii::t('app', 'Installation of machinery, technological and functional equipment - electrical installations (low currents)'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'machinery'
            ],
            '4.2.3' => [
                'key' => '4.2.3',
                'name' => Yii::t('app', 'Installation of machinery, technological and functional equipment - sanitary installations'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'machinery'
            ],
            '4.2.4' => [
                'key' => '4.2.4',
                'name' => Yii::t('app', 'Installation of machinery, technological and functional equipment - firefighting installations'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'machinery'
            ],
            '4.2.5' => [
                'key' => '4.2.5',
                'name' => Yii::t('app', 'Installation of machinery, technological and functional equipment - heating, ventilation, air conditioning'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'machinery'
            ],
            '4.2.6' => [
                'key' => '4.2.6',
                'name' => Yii::t('app', 'Installation of machinery, technological and functional equipment - natural gas installations'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'machinery'
            ],
            '4.2.7' => [
                'key' => '4.2.7',
                'name' => Yii::t('app', 'Installation of machinery, technological and functional equipment - architecture'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'machinery'
            ],
            '4.2.8' => [
                'key' => '4.2.8',
                'name' => Yii::t('app', 'Installation of machinery, technological and functional equipment - roads, platforms and alleys'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'machinery'
            ],
            '4.2.9' => [
                'key' => '4.2.9',
                'name' => Yii::t('app', 'Installation of machinery, technological and functional equipment - tempestizare'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'machinery'
            ],
            'TOTAL II' => [
                'key' => 'TOTAL II',
                'name' => 'TOTAL II',
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'total-rows',
                'classTotal' => 'exist',
                'autoCalculation' => 'totalII',
                'recognizeCalculations' => 'total',
            ]
        ];
    }

    /**
     * This function returns an array with default values for the last part of the table
     */
    public function setProcurementDefaultData()
    {
        $this->procurementHeaderText = Yii::t('app', 'III. Purchase of machinery, equipment and endowments');
        $this->procurementDefaultData = [
            '4.3' => [
                'key' => '4.3',
                'name' => Yii::t('app', 'Machinery, technological and functional equipment that requires installation'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'header-bold style-rows',
                'autoCalculation' => 'equipment',
                'recognizeCalculations' => 'total3',
            ],
            '4.3.1' => [
                'key' => '4.3.1',
                'name' => Yii::t('app', 'Machinery, technological and functional equipment that requires installation - electrical installations (strong currents)'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'equipment'
            ],
            '4.3.2' => [
                'key' => '4.3.2',
                'name' => Yii::t('app', 'Machinery, technological and functional equipment that requires installation - electrical installations (low currents)'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'equipment'
            ],
            '4.3.3' => [
                'key' => '4.3.3',
                'name' => Yii::t('app', 'Machinery, technological and functional equipment that requires installation - sanitary installations'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'equipment'
            ],
            '4.3.4' => [
                'key' => '4.3.4',
                'name' => Yii::t('app', 'Machinery, technological and functional equipment that requires installation - firefighting installations'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'equipment'
            ],
            '4.3.5' => [
                'key' => '4.3.5',
                'name' => Yii::t('app', 'Machinery, technological and functional equipment that requires installation - heating, ventilation, air conditioning'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'equipment'
            ],
            '4.3.6' => [
                'key' => '4.3.6',
                'name' => Yii::t('app', 'Machinery, technological and functional equipment that requires installation - natural gas installations'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'equipment'
            ],
            '4.3.7' => [
                'key' => '4.3.7',
                'name' => Yii::t('app', 'Machinery, technological and functional equipment that requires installation - architecture'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'equipment'
            ],
            '4.3.8' => [
                'key' => '4.3.8',
                'name' => Yii::t('app', 'Machinery, technological and functional equipment that requires installation - roads, platforms and alley'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'equipment'
            ],
            '4.3.9' => [
                'key' => '4.3.9',
                'name' => Yii::t('app', 'Machinery, technological and functional equipment that requires installation - tempestizare'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'equipment'
            ],
            '4.4' => [
                'key' => '4.4',
                'name' => Yii::t('app', 'Machinery, technological and functional equipment that does not require assembly and transport equipment'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'header-bold style-rows',
                'autoCalculation' => 'not_require_transport',
                'recognizeCalculations' => 'total3',
            ],
            '4.5' => [
                'key' => '4.5',
                'name' => Yii::t('app', 'Features'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'header-bold style-rows',
                'autoCalculation' => 'features',
                'recognizeCalculations' => 'total3',
            ],
            '4.5.1' => [
                'key' => '4.5.1',
                'name' => Yii::t('app', 'PSI Features'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'features'
            ],
            '4.5.2' => [
                'key' => '4.5.2',
                'name' => Yii::t('app', 'Architecture Features'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'style-rows',
                'recognizeCalculations' => 'features'
            ],
            '4.6' => [
                'key' => '4.6',
                'name' => Yii::t('app', 'Intangible assets'),
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'header-bold style-rows',
                'autoCalculation' => 'assets',
                'recognizeCalculations' => 'total3',
            ],
            'TOTAL III' => [
                'key' => 'TOTAL III',
                'name' => 'TOTAL III',
                'defaultValue' => '0.00',
                'TVAValue' => '0.00',
                'defaultValueWithTVA' => '0.00',
                'class' => 'total-rows',
                'classTotal' => 'exist',
                'autoCalculation' => 'totalIII',
                'recognizeCalculations' => 'total',
            ]
        ];

        $this->f2TotalValue['name'] = Yii::t('app', 'TOTAL ESTIMATE PER ITEM');
    }


    /**
     * This function returns an array with calculated values for the first part of the table
     */
    public function calculatedInvestmentCost($specialityIds, $projectId, $objectId, $estimateId, $typeValues)
    {
        if (empty($specialityIds) || empty($projectId) || empty($objectId) || empty($estimateId) || empty($typeValues)) {
            throw new BadRequestHttpException(Yii::t('app', 'Something went wrong in solving the calculations!'));
        }

        ItemPriceAnalytics::getPricesForCentralizer(99999999, 1);

        if (empty($this->calculatedInvestmentCost)) {
            $this->setInvestmentCostDefaultData();
            $this->calculatedInvestmentCost = $this->investmentCostDefaultData;
        }

        $specialityCodeLink = EstimateF2::getSpecialityCodeLink(1);

        $totals = [
            '4.1' => ['4.1.1', '4.1.2', '4.1.3', '4.1.5', '4.1.6'],
            '4.1.4' => ['4.1.4.1', '4.1.4.2', '4.1.4.3', '4.1.4.4', '4.1.4.5', '4.1.4.6'],
            'TOTAL I' => ['4.1', '4.1.4']
        ];

        foreach ($specialityIds as $specialityId) {
            $estimatesF3F4 = EstimateF3F4::find()->with('estimate')->where([
                'project_id' => $projectId,
                'object_id' => $objectId,
                'estimate_id' => $estimateId,
                'speciality_id' => $specialityId
            ])->asArray()->one();

            $quantityList = QuantityList::find()->where([
                'project_id' => $projectId,
                'object_id' => $objectId,
                'speciality_id' => $specialityId,
                'quantity_list_type' => $estimatesF3F4['estimate']['estimate_type'],
                'deleted' => 0
            ])->one();

            $quantities = [];
            if (!empty($quantityList)) {
                $quantities = ArticleQuantity::find()->with('article.measureUnit', 'speciality', 'quantityList', 'estimateF3F4Locked')
                    ->where("project_id = :project_id AND object_id = :object_id AND `article_quantity`.`speciality_id` = :speciality_id", [
                        ':project_id' => $projectId,
                        ':object_id' => $objectId,
                        ':speciality_id' => $specialityId,
                    ])->innerJoinWith(['article' => function ($article) {
                    }])->innerJoinWith(['category' => function ($category) {
                    }])->joinWith(['estimateF3F4Locked' => function ($estimateF3F4Locked) use ($estimateId) {
                        $estimateF3F4Locked->andOnCondition("`estimate_f3_f4_locked`.`estimate_id` = {$estimateId}");
                    }])->andWhere(['quantity_list_id' => $quantityList->id])
                    ->asArray()->orderBy('article_category.name ASC, LENGTH(article.code) ASC, article.code ASC')->asArray()->all();
            }

            $centralizer = Centralizer::find()
                ->where(['estimate_id' => $estimateId, 'project_id' => $projectId, 'speciality_id' => $specialityId])->one();

            foreach ($quantities as $quantity) {
                $quantityReduced = 0;
                $centralizerAndCD = EstimateF3F4::getCentralizerAndCD(1, $quantity, $centralizer, ItemPriceAnalytics::$minAvgMaxPrices);

                $unitRecapitulated = EstimateF3F4::getUnitRecapitulatedPrice(1, $centralizerAndCD);

                if (!empty($estimatesF3F4)) {
                    if ($typeValues !== 'basic_quantity' && $typeValues !== 'ptde_quantity') {
                        $quantityReduced = (float)$quantity[$typeValues];
                    } else {
                        if (!empty($quantity['estimateF3F4Locked'])) {
                            $quantityReduced = (float)$quantity['estimateF3F4Locked']['reduced_' . $typeValues];
                        } else {
                            if (in_array($quantity['article']['measureUnit']['code'], EstimateF3F4::setMeasureUnitToExclude())) {
                                $quantityReduced = (float)$quantity[$typeValues];
                                if ($typeValues === 'ptde_quantity') {
                                    $quantityReduced = (float)$quantity['basic_quantity'] + (float)$quantity['necessary_quantity'];
                                }
                            } else {
                                $column = $typeValues === 'ptde_quantity' ? 'basic_quantity' : $typeValues;
                                $quantityReduced = EstimateF3F4::roundUpDecimal((float)$quantity[$column] * (float)$estimatesF3F4['global_adjustment'] *
                                    (float)$estimatesF3F4['specific_adjustment'], $this->decimals);
                                if ($typeValues === 'ptde_quantity') {
                                    $quantityReduced += (float)($quantity['necessary_quantity'] ?? 0);
                                }
                            }
                        }
                    }
                }

                if (!empty($specialityCodeLink[$quantity['speciality']['code']])) {
                    $this->calculatedInvestmentCost[$specialityCodeLink[$quantity['speciality']['code']]]['defaultValue'] +=
                        EstimateF3F4::roundUpDecimal($quantityReduced * $unitRecapitulated, $this->decimals);
                    $this->calculatedInvestmentCost[$specialityCodeLink[$quantity['speciality']['code']]]['TVAValue'] +=
                        EstimateF3F4::roundUpDecimal((($quantityReduced * $unitRecapitulated) * $this->TVA) / 100, $this->decimals);
                }
            }
        }

        $this->calculatedInvestmentCost = $this->setCalculatedValues($this->calculatedInvestmentCost, $specialityCodeLink, true);

        $this->calculatedInvestmentCost = $this->setCalculatedValues($this->calculatedInvestmentCost, $totals);

        $this->f2TotalValue['defaultValue'] += (float)$this->calculatedInvestmentCost['TOTAL I']['defaultValue'];

        $this->f2TotalValue['TVAValue'] += EstimateF3F4::roundUpDecimal(
            ((float)$this->calculatedInvestmentCost['TOTAL I']['defaultValue'] * $this->TVA) / 100,
            $this->decimals
        );

        $this->f2TotalValue['defaultValueWithTVA'] += EstimateF3F4::roundUpDecimal(
            (($this->calculatedInvestmentCost['TOTAL I']['defaultValue'] * $this->TVA) / 100) + $this->calculatedInvestmentCost['TOTAL I']['defaultValue'],
            $this->decimals
        );
    }


    /**
     * This function returns an array with calculated values for the second part of the table
     */
    public function calculatedAssemblyWorksCost($specialityIds, $projectId, $objectId, $estimateId, $typeValues)
    {
        if (empty($specialityIds) || empty($projectId) || empty($objectId) || empty($estimateId) || empty($typeValues)) {
            throw new BadRequestHttpException(Yii::t('app', 'Something went wrong in solving the calculations!'));
        }
        ItemPriceAnalytics::getPricesForCentralizer(99999999, 2);

        if (empty($this->calculatedAssemblyWorks)) {
            $this->setAssemblyWorksDefaultData();
            $this->calculatedAssemblyWorks = $this->assemblyWorksDefaultData;
        }

        $specialityCodeLink = EstimateF2::getSpecialityCodeLink(2);

        $totals = [
            '4.2' => ['4.2.1', '4.2.2', '4.2.3', '4.2.4', '4.2.5', '4.2.6', '4.2.7', '4.2.8', '4.2.9'],
            'TOTAL II' => ['4.2']
        ];

        foreach ($specialityIds as $specialityId) {
            $estimatesF3F4 = EstimateF3F4::find()->with('estimate')->where([
                'project_id' => $projectId,
                'object_id' => $objectId,
                'estimate_id' => $estimateId,
                'speciality_id' => $specialityId
            ])->asArray()->one();

            $quantityList = QuantityList::find()->where([
                'project_id' => $projectId,
                'object_id' => $objectId,
                'speciality_id' => $specialityId,
                'quantity_list_type' => $estimatesF3F4['estimate']['estimate_type'],
                'deleted' => 0
            ])->one();

            $quantities = [];
            if (!empty($quantityList)) {
                $quantities = EquipmentQuantity::find()->with('equipment.category', 'speciality', 'quantityList', 'estimateF3F4Locked')
                    ->where([
                        'project_id' => $projectId,
                        'object_id' => $objectId,
                        'equipment_quantity.speciality_id' => $specialityId,
                    ])->innerJoinWith(['equipment' => function ($equipment) {
                    }])->innerJoinWith(['category' => function ($category) {
                    }])->joinWith(['estimateF3F4Locked' => function ($estimateF3F4Locked) use ($estimateId) {
                        $estimateF3F4Locked->andOnCondition("`estimate_f3_f4_locked`.`estimate_id` = {$estimateId}");
                    }])->andWhere(['quantity_list_id' => $quantityList->id])
                    ->asArray()->orderBy('equipment_category.name ASC, LENGTH(equipment.code) ASC, equipment.code ASC')->all();
            }

            $centralizer = Centralizer::find()
                ->where(['estimate_id' => $estimateId, 'project_id' => $projectId, 'speciality_id' => $specialityId])->one();

            foreach ($quantities as $quantity) {

                $centralizerAndCD = EstimateF3F4::getCentralizerAndCD(3, $quantity, $centralizer, ItemPriceAnalytics::$minAvgMaxPrices);

                $unitRecapitulated = EstimateF3F4::getUnitRecapitulatedPrice(3, $centralizerAndCD);

                $quantityReduced = 0.00;
                if (!empty($estimatesF3F4)) {
                    $quantityReduced = $quantity[$typeValues];
                    if ($typeValues === 'ptde_quantity') {
                        $quantityReduced = (float)$quantity['basic_quantity'] + (float)$quantity['necessary_quantity'];
                    }
                }

                if (!empty($specialityCodeLink[$quantity['speciality']['code']])) {
                    $this->calculatedAssemblyWorks[$specialityCodeLink[$quantity['speciality']['code']]]['defaultValue'] +=
                        EstimateF3F4::roundUpDecimal((float)$quantityReduced * (float)$unitRecapitulated, $this->decimals);
                    $this->calculatedAssemblyWorks[$specialityCodeLink[$quantity['speciality']['code']]]['TVAValue'] +=
                        EstimateF3F4::roundUpDecimal((((float)$quantityReduced * (float)$unitRecapitulated) * $this->TVA) / 100, $this->decimals);
                }
            }
        }

        $this->calculatedAssemblyWorks = $this->setCalculatedValues($this->calculatedAssemblyWorks, $specialityCodeLink, true);

        $this->calculatedAssemblyWorks = $this->setCalculatedValues($this->calculatedAssemblyWorks, $totals);

        $this->f2TotalValue['defaultValue'] += $this->calculatedAssemblyWorks['TOTAL II']['defaultValue'];

        $this->f2TotalValue['TVAValue'] += EstimateF3F4::roundUpDecimal(
            ($this->calculatedAssemblyWorks['TOTAL II']['defaultValue'] * $this->TVA) / 100,
            $this->decimals
        );

        $this->f2TotalValue['defaultValueWithTVA'] += EstimateF3F4::roundUpDecimal(
            (($this->calculatedAssemblyWorks['TOTAL II']['defaultValue'] * $this->TVA) / 100) + $this->calculatedAssemblyWorks['TOTAL II']['defaultValue'],
            $this->decimals
        );
    }

    /**
     * This function returns an array with calculated values for the first part of the table
     */
    public function calculatedProcurementCost($specialityIds, $projectId, $objectId, $estimateId, $typeValues)
    {
        if (empty($specialityIds) || empty($projectId) || empty($objectId) || empty($estimateId) || empty($typeValues)) {
            throw new BadRequestHttpException(Yii::t('app', 'Something went wrong in solving the calculations!'));
        }

        ItemPriceAnalytics::getPricesForCentralizer(99999999, 2);

        if (empty($this->calculatedProcurement)) {
            $this->setProcurementDefaultData();
            $this->calculatedProcurement = $this->procurementDefaultData;
        }

        $specialityCodeLink = EstimateF2::getSpecialityCodeLink(3);

        $totals = [
            '4.5' => ['4.5.1', '4.5.2'],
            '4.3' => ['4.3.1', '4.3.2', '4.3.3', '4.3.4', '4.3.5', '4.3.6', '4.3.7'],
            'TOTAL III' => ['4.3', '4.4', '4.5', '4.6']
        ];

        foreach ($specialityIds as $specialityId) {
            $estimatesF3F4 = EstimateF3F4::find()->with('estimate')->where([
                'project_id' => $projectId,
                'object_id' => $objectId,
                'estimate_id' => $estimateId,
                'speciality_id' => $specialityId
            ])->asArray()->one();

            $quantityList = QuantityList::find()->where([
                'project_id' => $projectId,
                'object_id' => $objectId,
                'speciality_id' => $specialityId,
                'quantity_list_type' => $estimatesF3F4['estimate']['estimate_type'],
                'deleted' => 0
            ])->one();

            $quantities = [];
            if (!empty($quantityList)) {
                $quantities = EquipmentQuantity::find()->with('equipment.category', 'speciality', 'quantityList', 'estimateF3F4Locked')
                    ->where([
                        'project_id' => $projectId,
                        'object_id' => $objectId,
                        'equipment_quantity.speciality_id' => $specialityId,
                    ])->innerJoinWith(['equipment' => function ($equipment) {
                    }])->innerJoinWith(['category' => function ($category) {
                    }])->joinWith(['estimateF3F4Locked' => function ($estimateF3F4Locked) use ($estimateId) {
                        $estimateF3F4Locked->andOnCondition("`estimate_f3_f4_locked`.`estimate_id` = {$estimateId}");
                    }])->andWhere(['quantity_list_id' => $quantityList->id])
                    ->asArray()->orderBy('equipment_category.name ASC, LENGTH(equipment.code) ASC, equipment.code ASC')->all();
            }

            $centralizer = Centralizer::find()
                ->where(['estimate_id' => $estimateId, 'project_id' => $projectId, 'speciality_id' => $specialityId])->one();

            foreach ($quantities as $quantity) {
                $quantityReduced = 0;
                $unitRecapitulatedF4d = $unitRecapitulatedF4e = 0;

                if (!empty($quantity['equipment_id']) && $quantity['equipment_type_id'] == 2) {
                    $typeForms = 2;
                } else {
                    $typeForms = 4;
                }

                $centralizerAndCD = EstimateF3F4::getCentralizerAndCD($typeForms, $quantity, $centralizer, ItemPriceAnalytics::$minAvgMaxPrices);

                $unitRecapitulated = EstimateF3F4::getUnitRecapitulatedPrice($typeForms, $centralizerAndCD);

                if (!empty($quantity['equipment_id']) && $quantity['equipment_type_id'] == 1) {
                    $unitRecapitulatedF4e = EstimateF3F4::roundUpDecimal((!empty($unitRecapitulated) ? (float)$unitRecapitulated : 0), $this->decimals);
                } else {
                    $unitRecapitulatedF4d = EstimateF3F4::roundUpDecimal((!empty($unitRecapitulated) ? (float)$unitRecapitulated : 0), $this->decimals);
                }

                if (!empty($estimatesF3F4)) {
                    $quantityReduced = $quantity[$typeValues];
                    if ($typeValues === 'ptde_quantity') {
                        $quantityReduced = (float)$quantity['basic_quantity'] + (float)$quantity['necessary_quantity'];
                    }
                }

                if (!empty($quantity['equipment']) && !empty($quantity['equipment']['category'])) {
                    if ($quantity['equipment']['category']['name'] === 'PSI') {
                        $this->calculatedProcurement['4.5.1']['defaultValue'] +=
                            EstimateF3F4::roundUpDecimal((float)$quantityReduced * (float)$unitRecapitulatedF4d, $this->decimals);
                        $this->calculatedProcurement['4.5.1']['TVAValue'] +=
                            EstimateF3F4::roundUpDecimal((((float)$quantityReduced * (float)$unitRecapitulatedF4d) * $this->TVA) / 100, $this->decimals);
                    } else {
                        $this->calculatedProcurement['4.5.2']['defaultValue'] +=
                            EstimateF3F4::roundUpDecimal((float)$quantityReduced * $unitRecapitulatedF4d, $this->decimals);
                        $this->calculatedProcurement['4.5.2']['TVAValue'] +=
                            EstimateF3F4::roundUpDecimal((((float)$quantityReduced * $unitRecapitulatedF4d) * $this->TVA) / 100, $this->decimals);
                    }
                }

                if (!empty($specialityCodeLink[$quantity['speciality']['code']])) {
                    $this->calculatedProcurement[$specialityCodeLink[$quantity['speciality']['code']]]['defaultValue'] +=
                        EstimateF3F4::roundUpDecimal((float)$quantityReduced * (float)$unitRecapitulatedF4e, $this->decimals);
                    $this->calculatedProcurement[$specialityCodeLink[$quantity['speciality']['code']]]['TVAValue'] +=
                        EstimateF3F4::roundUpDecimal((((float)$quantityReduced * (float)$unitRecapitulatedF4e) * $this->TVA) / 100, $this->decimals);
                }
            }
        }

        $this->calculatedProcurement['4.5.1']['defaultValueWithTVA'] = EstimateF3F4::roundUpDecimal(
            $this->calculatedProcurement['4.5.1']['defaultValue'] + $this->calculatedProcurement['4.5.1']['TVAValue'],
            $this->decimals
        );

        $this->calculatedProcurement['4.5.2']['defaultValueWithTVA'] = EstimateF3F4::roundUpDecimal(
            $this->calculatedProcurement['4.5.2']['defaultValue'] + $this->calculatedProcurement['4.5.2']['TVAValue'],
            $this->decimals
        );

        $this->calculatedProcurement = $this->setCalculatedValues($this->calculatedProcurement, $specialityCodeLink, true);

        $this->calculatedProcurement = $this->setCalculatedValues($this->calculatedProcurement, $totals);

        $this->f2TotalValue['defaultValue'] += $this->calculatedProcurement['TOTAL III']['defaultValue'];

        $this->f2TotalValue['TVAValue'] += EstimateF3F4::roundUpDecimal(
            ($this->calculatedProcurement['TOTAL III']['defaultValue'] * $this->TVA) / 100,
            $this->decimals
        );

        $this->f2TotalValue['defaultValueWithTVA'] += EstimateF3F4::roundUpDecimal(
            (($this->calculatedProcurement['TOTAL III']['defaultValue'] * $this->TVA) / 100) + $this->calculatedProcurement['TOTAL III']['defaultValue'],
            $this->decimals
        );

        $this->f2TotalValue['name'] = Yii::t('app', 'TOTAL ESTIMATE PER ITEM');
    }

    /**
     * This function return the TVA values of the subchapters or return values for chapters with specific calculations
     */
    public function setCalculatedValues($tablePart, $specialsCalc, $onlyValueWithTva = false)
    {
        foreach ($specialsCalc as $key => $total) {
            if ($onlyValueWithTva) {
                $tablePart[$total]['defaultValueWithTVA'] = EstimateF3F4::roundUpDecimal(
                    (float)$tablePart[$total]['defaultValue'] + (float)$tablePart[$total]['TVAValue'],
                    $this->decimals
                );
            } else {
                foreach ($total as $code) {
                    $tablePart[$key]['defaultValue'] += (float)$tablePart[$code]['defaultValue'];
                }
                $tablePart[$key]['TVAValue'] = EstimateF3F4::roundUpDecimal(
                    ((float)$tablePart[$key]['defaultValue'] * (float)$this->TVA) / 100,
                    $this->decimals
                );
                $tablePart[$key]['defaultValueWithTVA'] = EstimateF3F4::roundUpDecimal(
                    (float)$tablePart[$key]['defaultValue'] + (float)$tablePart[$key]['TVAValue'],
                    $this->decimals
                );
            }
        }

        return $tablePart;
    }
}
