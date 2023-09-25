<?php

namespace backend\components;

use Yii;

/**
 *
 */
class GeneralEstimate
{
    /**
     * structure of the general estimate
     */
    public $generalEstimateStructure;

    /**
     * this function contains an array with the structure of the general estimate
     */
    public function setChapters()
    {
        $this->generalEstimateStructure = [
            'chapter_1' => [
                'chapter' => [
                    'name' => Yii::t('app', 'Chapter 1 - Expenses for obtaining and arranging the land'),
                    'class' => 'general-estimate-chapter',
                    'button' => true,
                ],
                'subsections' => [
                    '1.1' => [
                        'code' => '1.1',
                        'name' => Yii::t('app', 'Obtaining land'),
                        'input' => true,
                        'button' => false
                    ],
                    '1.2' => [
                        'code' => '1.2',
                        'name' => Yii::t('app', 'Land planning'),
                        'input' => false,
                        'button' => true
                    ],
                    '1.3' => [
                        'code' => '1.3',
                        'name' => Yii::t('app', 'Arrangements for environmental protection and bringing the land to its original state'),
                        'input' => false,
                        'button' => true
                    ],
                    '1.4' => [
                        'code' => '1.4',
                        'name' => Yii::t('app', 'Expenses for relocation / protection of utilities'),
                        'input' => false,
                        'button' => true
                    ],
                    'Total' => [
                        'name' => Yii::t('app', 'TOTAL CHAPTER 1'),
                        'input' => false,
                        'button' => true,
                    ],
                ]
            ],
            'chapter_2' => [
                'chapter' => [
                    'name' => Yii::t('app', 'Chapter 2 - Expenses for providing the necessary utilities for the investment objective'),
                    'class' => 'general-estimate-chapter',
                    'button' => false,
                ],
                'subsections' => [
                    '2.1' => [
                        'code' => '2.1',
                        'name' => Yii::t('app', 'Expenses for providing the necessary utilities for the investment objective'),
                        'input' => false,
                        'button' => true
                    ],
                    'Total' => [
                        'name' => Yii::t('app', 'TOTAL CHAPTER 2'),
                        'input' => false,
                        'button' => true,
                    ],
                ]
            ],
            'chapter_3' => [
                'chapter' => [
                    'name' => Yii::t('app', 'Chapter 3 - Expenses for design and technical assistance'),
                    'class' => 'general-estimate-chapter',
                    'button' => true,
                ],
                'subsections' => [
                    '3.1' => [
                        'code' => '3.1',
                        'name' => Yii::t('app', 'Studies'),
                        'input' => false,
                        'button' => false,
                        'classSubSection' => 'calculated-values-study-section'
                    ],
                    '3.1.1' => [
                        'code' => '3.1.1',
                        'name' => Yii::t('app', 'Field studies'),
                        'input' => true,
                        'button' => false,
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-study-section'
                    ],
                    '3.1.2' => [
                        'code' => '3.1.2',
                        'name' => Yii::t('app', 'Environmental impact report'),
                        'input' => true,
                        'button' => false,
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-study-section'
                    ],
                    '3.1.3' => [
                        'code' => '3.1.3',
                        'name' => Yii::t('app', 'Other specific studies'),
                        'input' => true,
                        'button' => false,
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-study-section'
                    ],
                    '3.2' => [
                        'code' => '3.2',
                        'name' => Yii::t('app', 'Support documentation and expenses for obtaining permits, agreements and authorizations'),
                        'input' => true,
                        'button' => false
                    ],
                    '3.3' => [
                        'code' => '3.3',
                        'name' => Yii::t('app', 'Technical expertise'),
                        'input' => true,
                        'button' => false
                    ],
                    '3.4' => [
                        'code' => '3.4',
                        'name' => Yii::t('app', 'Certification of energy performance and energy audit of buildings'),
                        'input' => true,
                        'button' => false
                    ],
                    '3.5' => [
                        'code' => '3.5',
                        'name' => Yii::t('app', 'Design'),
                        'input' => false,
                        'button' => false,
                        'classSubSection' => 'calculated-values-design-section'
                    ],
                    '3.5.1' => [
                        'code' => '3.5.1',
                        'name' => Yii::t('app', 'Design theme'),
                        'input' => true,
                        'button' => false,
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-design-section'
                    ],
                    '3.5.2' => [
                        'code' => '3.5.2',
                        'name' => Yii::t('app', 'Feasibility study'),
                        'input' => true,
                        'button' => false,
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-design-section'
                    ],
                    '3.5.3' => [
                        'code' => '3.5.3',
                        'name' => Yii::t('app', 'Feasibility study / documentation for approving the intervention works and general estimate'),
                        'input' => true,
                        'button' => false,
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-design-section'
                    ],
                    '3.5.4' => [
                        'code' => '3.5.4',
                        'name' => Yii::t('app', 'Technical documentation required to obtain approvals / agreements / authorizations'),
                        'input' => true,
                        'button' => false,
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-design-section'
                    ],
                    '3.5.5' => [
                        'code' => '3.5.5',
                        'name' => Yii::t('app', 'Technical verification of the quality of the technical project and of the execution details'),
                        'input' => true,
                        'button' => false,
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-design-section'
                    ],
                    '3.5.6' => [
                        'code' => '3.5.6',
                        'name' => Yii::t('app', 'Technical design and execution details'),
                        'input' => true,
                        'button' => false,
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-design-section'
                    ],
                    '3.6' => [
                        'code' => '3.6',
                        'name' => Yii::t('app', 'Organizing procurement procedures'),
                        'input' => true,
                        'button' => false
                    ],
                    '3.7' => [
                        'code' => '3.7',
                        'name' => Yii::t('app', 'Consultant'),
                        'input' => false,
                        'button' => false,
                        'classSubSection' => 'calculated-values-consultant-section'
                    ],
                    '3.7.1' => [
                        'code' => '3.7.1',
                        'name' => Yii::t('app', 'Project management for the investment objective'),
                        'input' => true,
                        'button' => false,
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-consultant-section'
                    ],
                    '3.7.2' => [
                        'code' => '3.7.2',
                        'name' => Yii::t('app', 'Financial audit'),
                        'input' => true,
                        'button' => false,
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-consultant-section'
                    ],
                    '3.8' => [
                        'code' => '3.8',
                        'name' => Yii::t('app', 'Technical support'),
                        'input' => false,
                        'button' => false,
                        'classSubSection' => 'calculated-values-technical-support-section'
                    ],
                    '3.8.1' => [
                        'code' => '3.8.1',
                        'name' => Yii::t('app', 'Technical assistance from the designer'),
                        'input' => false,
                        'button' => false,
                        'class' => 'normal-font',
                        'classSubSection' => 'calculated-values-technical-assistance-section',
                        'calculateSection' => 'to-calculate-values-technical-support-section'
                    ],
                    '3.8.1.1' => [
                        'code' => '3.8.1.1',
                        'name' => Yii::t('app', 'During the execution of the works'),
                        'input' => true,
                        'button' => false,
                        'class' => 'italic-font',
                        'calculateSection' => 'to-calculate-values-technical-assistance-section'
                    ],
                    '3.8.1.2' => [
                        'code' => '3.8.1.2',
                        'name' =>
                            Yii::t('app',
                                'For the participation of the designer in the phases included in the control program of the execution works, approved by I.S.C.'
                            ),
                        'input' => true,
                        'button' => false,
                        'class' => 'italic-font',
                        'calculateSection' => 'to-calculate-values-technical-assistance-section'
                    ],
                    '3.8.2' => [
                        'code' => '3.8.2',
                        'name' => Yii::t('app', 'Site master'),
                        'input' => true,
                        'button' => false,
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-technical-support-section'
                    ],
                    'Total' => [
                        'name' => Yii::t('app', 'TOTAL CHAPTER 3'),
                        'input' => false,
                        'button' => true,
                    ],
                ]
            ],
            'chapter_4' => [
                'chapter' => [
                    'name' => Yii::t('app', 'Chapter 4 - Basic investment expenses'),
                    'class' => 'general-estimate-chapter',
                    'button' => false,
                ],
                'subsections' => [
                    'special' => [
                        'code' => 'special',
                        'name' => '',
                        'input' => false,
                        'button' => true,
                        'class' => 'special-row',
                        'special-name' => Yii::t('app', 'TOTAL')
                    ],
                    '4.1' => [
                        'code' => '4.1',
                        'name' => Yii::t('app', 'Constructions and plumbing'),
                        'input' => false,
                        'button' => false
                    ],
                    '4.2' => [
                        'code' => '4.2',
                        'name' => Yii::t('app', 'Installation of machinery, technological and functional equipment'),
                        'input' => false,
                        'button' => false
                    ],
                    '4.3' => [
                        'code' => '4.3',
                        'name' => Yii::t('app', 'Machinery, technological and functional equipment that requires installation'),
                        'input' => false,
                        'button' => false
                    ],
                    '4.4' => [
                        'code' => '4.4',
                        'name' => Yii::t('app', 'Machinery, technological and functional equipment not requiring assembly and transport equipment'),
                        'input' => true,
                        'button' => false
                    ],
                    '4.5' => [
                        'code' => '4.5',
                        'name' => Yii::t('app', 'Features'),
                        'input' => false,
                        'button' => false
                    ],
                    '4.6' => [
                        'code' => '4.6',
                        'name' => Yii::t('app', 'Intangible assets'),
                        'input' => true,
                        'button' => false
                    ],
                    'Total' => [
                        'name' => Yii::t('app', 'TOTAL CHAPTER 4'),
                        'input' => false,
                        'button' => true,
                    ],
                ]
            ],
            'chapter_5' => [
                'chapter' => [
                    'name' => Yii::t('app', 'Chapter 5 - Other expenses'),
                    'class' => 'general-estimate-chapter',
                    'button' => true,
                ],
                'subsections' => [
                    '5.1' => [
                        'code' => '5.1',
                        'name' => Yii::t('app', 'Site organization 3%'),
                        'input' => false,
                        'button' => false,
                        'button-input' => 'exist',
                        'siteSection' => 'site-chapter-value'
                    ],
                    '5.1.1' => [
                        'code' => '5.1.1',
                        'name' => Yii::t('app', 'Construction works and installations related to the organization of the site'),
                        'input' => false,
                        'button' => false,
                        'class' => 'normal-font',
                        'siteSection' => 'seventy-percent-value'
                    ],
                    '5.1.2' => [
                        'code' => '5.1.2',
                        'name' => Yii::t('app', 'Expenses related to the organization of the site'),
                        'input' => false,
                        'button' => false,
                        'class' => 'normal-font',
                        'siteSection' => 'thirty-percent-value'
                    ],
                    '5.2' => [
                        'code' => '5.2',
                        'name' => Yii::t('app', 'Commissions, fees, taxes, cost of credit'),
                        'input' => false,
                        'button' => false,
                        'classSubSection' => 'calculated-values-fee-taxes-section'
                    ],
                    '5.2.1' => [
                        'code' => '5.2.1',
                        'name' => Yii::t('app', 'Fees and interest related to the loan of the financing bank'),
                        'input' => true,
                        'button' => false,
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-fee-taxes-section'
                    ],
                    '5.2.2' => [
                        'code' => '5.2.2',
                        'name' => Yii::t('app', 'ISC quota for quality control of construction works 0.5%'),
                        'input' => false,
                        'button' => false,
                        'button-input' => 'exist',
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-fee-taxes-section'
                    ],
                    '5.2.3' => [
                        'code' => '5.2.3',
                        'name' => Yii::t('app', 'ISC share for the control of the status in the landscaping, urbanism and for the authorization of construction works 0.1%'),
                        'input' => false,
                        'button' => false,
                        'button-input' => 'exist',
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-fee-taxes-section'
                    ],
                    '5.2.4' => [
                        'code' => '5.2.4',
                        'name' => Yii::t('app', "Share related to the Builders' Social House - CSC 0.5%"),
                        'input' => false,
                        'button' => false,
                        'button-input' => 'exist',
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-fee-taxes-section'
                    ],
                    '5.2.5' => [
                        'code' => '5.2.5',
                        'name' => Yii::t('app', 'Fees for agreements, compliant approvals and building permits'),
                        'input' => true,
                        'button' => false,
                        'class' => 'normal-font',
                        'calculateSection' => 'to-calculate-values-fee-taxes-section'
                    ],
                    '5.3' => [
                        'code' => '5.3',
                        'name' => Yii::t('app', 'Miscellaneous and unforeseen expenses'),
                        'input' => false,
                        'button' => false,
                        'button-input' => 'exist'
                    ],
                    '5.4' => [
                        'code' => '5.4',
                        'name' => Yii::t('app', 'Expenditure on information and publicity'),
                        'input' => true,
                        'button' => false,
                    ],
                    'Total' => [
                        'name' => Yii::t('app', 'TOTAL CHAPTER 5'),
                        'input' => false,
                        'button' => true,
                    ],
                ]
            ],
            'chapter_6' => [
                'chapter' => [
                    'name' => Yii::t('app', 'Chapter 6 - Expenses for technological tests and trials'),
                    'class' => 'general-estimate-chapter',
                    'button' => true,
                ],
                'subsections' => [
                    '6.1' => [
                        'code' => '6.1',
                        'name' => Yii::t('app', 'Training of operating personnel'),
                        'input' => true,
                        'button' => false
                    ],
                    '6.2' => [
                        'code' => '6.2',
                        'name' => Yii::t('app', 'Technological tests and trials'),
                        'input' => true,
                        'button' => false
                    ],
                    'Total' => [
                        'name' => Yii::t('app', 'TOTAL CHAPTER 6'),
                        'input' => false,
                        'button' => true,
                    ],
                ]
            ],
            'chapter_7' => [
                'subsections' => [
                    'Total' => [
                        'name' => Yii::t('app', 'TOTAL GENERAL INVESTMENT'),
                        'class' => 'general-estimate-chapter',
                        'input' => false,
                        'button' => true,
                    ],
                ]
            ],
            'chapter_8' => [
                'subsections' => [
                    'Total' => [
                        'name' => Yii::t('app', 'Of which C + M'),
                        'class' => 'cm-estimate-chapter',
                        'input' => false,
                        'button' => true,
                    ],
                ]
            ]
        ];
    }
}
