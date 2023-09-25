<?php

use backend\modules\adm\models\Settings;
use mdm\admin\components\Helper;
use yii\base\InvalidConfigException;
use backend\widgets\adminLteWidgets\AdminLteNav;
use yii\bootstrap4\Html;

/* @var $userProfileImage */

$erpStart = 2021;
?>
<!-- Main Sidebar Container -->
<aside class="main-sidebar main-sidebar-with-footer sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?php echo Yii::$app->homeUrl; ?>" class="brand-link d-flex flex-column text-center bg-white"
       style="padding: 8px .5rem;">
        <?php
        $identity = Settings::getIdentityImages();
        ?>
        <img style="height: 40px;" src="<?php echo $identity['left_sidebar_image']; ?>" class="icon m-auto">
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <?php
                echo Html::img($userProfileImage, [
                    'class' => 'img-circle elevation-2 ',
                    'alt' => 'User Image'
                ]); ?>
            </div>
            <div class="text-white info">
                <?php echo Yii::t('app', 'Hello'); ?>, <?php echo Yii::$app->user->identity->username ?? ''; ?>
            </div>
        </div>
        <!-- Sidebar Menu -->
        <nav class="mt-2" id="qa-sidebar-menu">
            <ul class="nav nav-pills nav-sidebar nav-child-indent nav-compact flex-column"
                data-widget="treeview" role="menu" data-accordion="true">
                <?php
                $submenuIcon = '<i class="nav-icon fas fa-caret-right"></i>';
                //APPS
                $menuLinks['app'] = [
                    'label' => '<p>' . Yii::t('app', 'APPLICATIONS') . '<i class="right fas fa-angle-left"></i></p>',
                    'url' => null,
                    'linkOptions' => ['class' => 'nav-header cursor-pointer'],
                    'visible' =>
                        Helper::checkRoute('/design/document/index')
                        || Helper::checkRoute('/design/document/create')
                        || Helper::checkRoute('/design/document/index-export')
                        || Helper::checkRoute('/checklist/quiz/start')
                        || Helper::checkRoute('/checklist/quiz/analytics')
                        || Helper::checkRoute('/build/quantity-list/index')
                        || Helper::checkRoute('/build/estimate/index')
                        || Helper::checkRoute('/build/article-beneficiary-price-history/index')
                        || Helper::checkRoute('/build/article-procurement-price-history/index')
                        || Helper::checkRoute('/build/equipment-beneficiary-price-history/index')
                        || Helper::checkRoute('/build/equipment-procurement-price-history/index')
                        || Helper::checkRoute('/finance/analytics/pnl-yearly')
                        || Helper::checkRoute('/finance/analytics/ebva-sales')
                        || Helper::checkRoute('/finance-centralizer/cashflow/index')
                        || Helper::checkRoute('/finance-centralizer/proj-expense/index')
                        || Helper::checkRoute('/procurement/offer/index')
                        || Helper::checkRoute('/procurement/contract/index')
                        || Helper::checkRoute('/provision/provision/index')
                        || Helper::checkRoute('/provision/centralizer-provision/index')
                        || Helper::checkRoute('/revit/revit-quantity-list/index')
                        || Helper::checkRoute('/provision/centralizer-provision/organizer-content')
                        || Helper::checkRoute('/fam/project-approval/index')
                        || Helper::checkRoute('/procurement/planner/index')
                        || Helper::checkRoute('/fam/fam-version/index')
                        || Helper::checkRoute('/procurement/offer/default-details')
                        || Helper::checkRoute('/build/estimate-attachment/index')
                ];
                //checklist
                $menuLinks['app']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-check-square"></i><p>' . Yii::t('app', 'Checklist') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'design/document/index',
                        'design/document/create',
                        'design/document/index-export',
                        'design/document/update',
                        'design/document/assign-activities',
                        'checklist/quiz/start',
                        'checklist/quiz/analytics',
                        'design/document/duplicate-checklists',
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Add') . '</p>',
                            'url' => ['/design/document/index', 'phaseTypes-filter' => 1],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'design/document/index',
                                'design/document/create',
                                'design/document/index-export',
                                'design/document/update',
                                'design/document/assign-activities',
                                'design/document/duplicate-checklists',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Verification') . '</p>',
                            'url' => ['/checklist/quiz/start'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'checklist/quiz/start',
                                'checklist/quiz/checklist',
                                'checklist/quiz/finish',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Analytics') . '</p>',
                            'url' => ['/checklist/quiz/analytics'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'checklist/quiz/analytics',
                            ])
                        ],
                    ],
                ];
                //qty list
                $menuLinks['app']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-clipboard-list"></i><p>' . Yii::t('app', 'Quantity List') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'build/quantity-list/index',
                        'build/quantity-list/view',
                        'build/quantity-list/update',
                        'build/quantity-list/create',
                        'build/quantity-list/import',
                        'build/quantity-list/update-status-quantity-list',
                        'build/article-quantity/index',
                        'build/equipment-quantity/index',
                        'build/estimate/index',
                        'build/estimate/view',
                        'build/estimate/update',
                        'build/estimate/duplicate',
                        'build/estimate/create',
                        'build/centralizer/index',
                        'build/centralizer/update',
                        'build/centralizer-article/index',
                        'build/centralizer-fitting/index',
                        'build/centralizer-equipment/index',
                        'build/centralizer-features/index',
                        'build/estimate-f3-f4/index',
                        'build/estimate-f3-f4/view',
                        'build/estimate-f2/index',
                        'build/estimate-f2/update',
                        'build/estimate-f2/create',
                        'build/estimate-f1/index',
                        'build/general-estimate/index',
                        'build/report-general-estimate/index',
                        'build/estimate-f3-f4/export-windoc-archive'
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Design department') . '</p>',
                            'url' => ['/build/quantity-list/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'build/quantity-list/index',
                                'build/quantity-list/view',
                                'build/quantity-list/update',
                                'build/quantity-list/create',
                                'build/quantity-list/import',
                                'build/quantity-list/update-status-quantity-list',
                                'build/article-quantity/index',
                                'build/equipment-quantity/index',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Estimates') . '</p>',
                            'url' => ['/build/estimate/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'build/estimate/index',
                                'build/estimate/view',
                                'build/estimate/update',
                                'build/estimate/duplicate',
                                'build/estimate/create',
                                'build/centralizer/index',
                                'build/centralizer/update',
                                'build/centralizer-article/index',
                                'build/centralizer-fitting/index',
                                'build/centralizer-equipment/index',
                                'build/centralizer-features/index',
                                'build/estimate-f3-f4/index',
                                'build/estimate-f3-f4/view',
                                'build/estimate-f2/index',
                                'build/estimate-f2/update',
                                'build/estimate-f2/create',
                                'build/estimate-f1/index',
                                'build/general-estimate/index',
                                'build/report-general-estimate/index',
                                'windocappestimate/article/index',
                                'windocappestimate/windoc-extract/index',
                                'build/estimate-f3-f4/export-windoc-archive',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Reports') . '</p>',
                            'url' => ['/build/quantity-list-changes/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'build/quantity-list-changes/index',
                                'build/quantity-list-changes/view',
                            ])
                        ]
                    ],
                ];
                //provision
                $menuLinks['app']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-list-ul"></i><p>' . Yii::t('app', 'Building provisions') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'provision/provision/index',
                        'provision/provision/create',
                        'provision/provision/content',
                        'provision/centralizer-provision/index',
                        'provision/centralizer-provision/view',
                        'provision/centralizer-provision/organizer-content',
                        'provision/official-provision/index',
                        'provision/official-provision/create',
                        'provision/official-provision/update',
                        'provision/official-provision/content',
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Design department') . '</p>',
                            'url' => ['/provision/provision/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'provision/provision/index',
                                'provision/provision/create',
                                'provision/provision/content',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Official') . '</p>',
                            'url' => ['/provision/official-provision/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'provision/official-provision/index',
                                'provision/official-provision/create',
                                'provision/official-provision/update',
                                'provision/official-provision/content',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Centralizer') . '</p>',
                            'url' => ['/provision/centralizer-provision/organizer-content'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'provision/centralizer-provision/organizer-content',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Technical assistance') . '</p>',
                            'url' => ['/provision/centralizer-provision/index', 'type' => 'technical_assistance'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                    'provision/centralizer-provision/index',
                                    'provision/centralizer-provision/view',
                                    'provision/centralizer-provision/update',
                                ])
                                && Yii::$app->request->get('type') === 'technical_assistance',
                            'visible' => Yii::$app->user->can('AssistanceBuildingProvision') || Yii::$app->user->can('DesignerBuildingProvision'),
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Procurement') . '</p>',
                            'url' => ['/provision/centralizer-provision/index', 'type' => 'procurement'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                    'provision/centralizer-provision/index',
                                    'provision/centralizer-provision/view',
                                ])
                                && Yii::$app->request->get('type') === 'procurement',
                            'visible' => Yii::$app->user->can('ProcurementBuildingProvision'),
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Estimates') . '</p>',
                            'url' => ['/provision/centralizer-provision/index', 'type' => 'estimate'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                    'provision/centralizer-provision/index',
                                    'provision/centralizer-provision/view',
                                    'provision/centralizer-provision/update',
                                ])
                                && Yii::$app->request->get('type') === 'estimate',
                            'visible' => Yii::$app->user->can('EstimateBuildingProvision') || Yii::$app->user->can('ContractingBuildingProvision'),
                        ],
                    ],
                ];
                //procurement
                $menuLinks['app']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-money-check-alt"></i><p>' . Yii::t('app', 'Procurement') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'procurement/offer/index',
                        'procurement/offer/create',
                        'procurement/offer/content',
                        'procurement/offer-provider/index',
                        'procurement/offer-provider/import',
                        'procurement/offer-provider/update',
                        'procurement/contract/index',
                        'procurement/contract/create',
                        'procurement/contract/create-annex',
                        'procurement/contract/view',
                        'procurement/planner/index',
                        'procurement/offer/default-details',
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Offer packages') . '</p>',
                            'url' => ['/procurement/offer/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'procurement/offer/index',
                                'procurement/offer/create',
                                'procurement/offer/content',
                                'procurement/offer/view-updates-offer-package',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Offer providers') . '</p>',
                            'url' => ['/procurement/offer-provider/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'procurement/offer-provider/index',
                                'procurement/offer-provider/import',
                                'procurement/offer-provider/view',
                                'procurement/offer-provider/update',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Comparison') . '</p>',
                            'url' => ['/procurement/comparison/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'procurement/comparison/index',
                                'procurement/comparison/view',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Planner') . '</p>',
                            'url' => ['/procurement/planner/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'procurement/planner/index',
                                'procurement/planner/create',
                                'procurement/planner/content',
                                'procurement/planner/object-content'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Contracts') . '</p>',
                            'url' => ['/procurement/contract/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'procurement/contract/index',
                                'procurement/contract/create',
                                'procurement/contract/create-annex',
                                'procurement/contract/view',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Articles providers') . '</p>',
                            'url' => ['/procurement/offer-provider/items', 'itemType' => 1],
                            'visible' => Yii::$app->user->can('indexArticlePriceProcurementOfferProvider'),
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                    'procurement/offer-provider/items',
                                ]) && Yii::$app->request->get('itemType') == 1
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Equipment providers') . '</p>',
                            'url' => ['/procurement/offer-provider/items', 'itemType' => 2],
                            'visible' => Yii::$app->user->can('indexEquipmentPriceProcurementOfferProvider'),
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                    'procurement/offer-provider/items',
                                ]) && Yii::$app->request->get('itemType') == 2
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Default details') . '</p>',
                            'url' => ['/procurement/offer/default-details'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'procurement/offer/default-details',
                            ])
                        ]
                    ],
                ];
                //Settlement
                $menuLinks['app']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-comments-dollar"></i><p>' . Yii::t('app', 'Settlement') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'build/estimate-attachment/index',
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Attachments') . '</p>',
                            'url' => ['/build/estimate-attachment/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'build/estimate-attachment/index'
                            ])
                        ]
                    ]
                ];

                //FAMs
                $menuLinks['app']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-user-cog"></i><p>' . Yii::t('app', 'FAMs') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'fam/project-approval/index',
                        'fam/project-approval/create',
                        'fam/project-approval/update',
                        'fam/project-approval/view',
                        'fam/fam-version/index',
                        'fam/fam-version/create',
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Designers') . '</p>',
                            'url' => ['/fam/project-approval/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'fam/project-approval/index',
                                'fam/project-approval/create',
                                'fam/project-approval/update',
                                'fam/project-approval/view',
                            ]),
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'FAM') . '</p>',
                            'url' => ['/fam/fam-version/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'fam/fam-version/index',
                                'fam/fam-version/create',
                                'fam/fam-version/update',
                                'fam/fam-version/view',
                                'fam/fam-version/validate',
                            ]),
                        ],
                    ],
                ];
                //LC - REVIT
                $menuLinks['app']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-clipboard-list"></i><p>' . Yii::t('app', 'QL - Revit') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'revit/revit-quantity-list/index',
                        'revit/revit-quantity-list/create',
                        'revit/csv-file/index',
                        'revit/csv-file/view'
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'Quantities Revit') . '</p>',
                            'url' => ['/revit/revit-quantity-list/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'revit/revit-quantity-list/index',
                                'revit/revit-quantity-list/create',
                                'revit/revit-quantity-list/view',
                                'revit/revit-quantity-list/update',
                                'revit/revit-quantity-list/update-status-quantity-list',
                                'revit/revit-item-quantity/index',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . '<p>' . Yii::t('app', 'CSV File') . '</p>',
                            'url' => ['/revit/csv-file/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'revit/csv-file/index',
                                'revit/csv-file/view',
                            ])
                        ]
                    ],
                ];
                //price history
                $menuLinks['app']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-dollar-sign"></i><p>' . Yii::t('app', 'History price') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'build/article-beneficiary-price-history/index',
                        'build/article-beneficiary-price-history/create',
                        'build/article-beneficiary-price-history/update',
                        'build/article-beneficiary-price-history/view',
                        'build/article-beneficiary-price-history/import',
                        'build/article-procurement-price-history/index',
                        'build/article-procurement-price-history/create',
                        'build/article-procurement-price-history/update',
                        'build/article-procurement-price-history/view',
                        'build/article-procurement-price-history/import',
                        'build/equipment-beneficiary-price-history/index',
                        'build/equipment-beneficiary-price-history/create',
                        'build/equipment-beneficiary-price-history/update',
                        'build/equipment-beneficiary-price-history/view',
                        'build/equipment-beneficiary-price-history/import',
                        'build/equipment-procurement-price-history/index',
                        'build/equipment-procurement-price-history/create',
                        'build/equipment-procurement-price-history/update',
                        'build/equipment-procurement-price-history/view',
                        'build/equipment-procurement-price-history/import'
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Projects articles'),
                            'url' => ['/build/article-beneficiary-price-history/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'build/article-beneficiary-price-history/index',
                                'build/article-beneficiary-price-history/create',
                                'build/article-beneficiary-price-history/update',
                                'build/article-beneficiary-price-history/view',
                                'build/article-beneficiary-price-history/import'
                            ])],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Procurement articles'),
                            'url' => ['/build/article-procurement-price-history/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'build/article-procurement-price-history/index',
                                'build/article-procurement-price-history/create',
                                'build/article-procurement-price-history/update',
                                'build/article-procurement-price-history/view',
                                'build/article-procurement-price-history/import'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Projects equipments'),
                            'url' => ['/build/equipment-beneficiary-price-history/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'build/equipment-beneficiary-price-history/index',
                                'build/equipment-beneficiary-price-history/create',
                                'build/equipment-beneficiary-price-history/update',
                                'build/equipment-beneficiary-price-history/view',
                                'build/equipment-beneficiary-price-history/import'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Procurement equipments'),
                            'url' => ['/build/equipment-procurement-price-history/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'build/equipment-procurement-price-history/index',
                                'build/equipment-procurement-price-history/create',
                                'build/equipment-procurement-price-history/update',
                                'build/equipment-procurement-price-history/view',
                                'build/equipment-procurement-price-history/import'
                            ])
                        ],
                    ]
                ];
                //Financial
                $menuLinks['app']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-coins"></i><p>' . ucfirst(Yii::t('app', 'financial')) . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'finance/analytics/pnl-yearly',
                        'finance/analytics/pnl-monthly',
                        'finance/analytics/ebva-sales',
                        'finance/analytics/ebva-costs',
                        'finance/analytics/spp-yearly',
                        'finance/analytics/spp-monthly',
                        'finance-centralizer/cashflow/index',
                        'finance-centralizer/cashflow/view',
                        'finance-centralizer/cashflow/update',
                        'finance-centralizer/proj-expense/index'
                    ]),
                    'items' => [
                        [
                            'label' => '<i class="nav-icon fas fa-piggy-bank"></i><p>' . Yii::t('app', 'Reports') . '<i class="right fas fa-angle-left"></i></p>',
                            'linkOptions' => ['class' => 'cursor-pointer'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'finance/analytics/pnl-yearly',
                                'finance/analytics/pnl-monthly',
                                'finance/analytics/ebva-sales',
                                'finance/analytics/ebva-costs',
                                'finance/analytics/spp-yearly',
                                'finance/analytics/spp-monthly'
                            ]),
                            'items' => [
                                [
                                    'label' => $submenuIcon . Yii::t('app', 'P&L'),
                                    'url' => ['/finance/analytics/pnl-yearly'],
                                    'active' => in_array(Yii::$app->controller->getRoute(), [
                                        'finance/analytics/pnl-monthly',
                                        'finance/analytics/pnl-yearly'
                                    ])],
                                [
                                    'label' => $submenuIcon . Yii::t('app', 'SPP'),
                                    'url' => ['/finance/analytics/spp-yearly'],
                                    'active' => in_array(Yii::$app->controller->getRoute(), [
                                        'finance/analytics/spp-yearly',
                                        'finance/analytics/spp-monthly',

                                    ])
                                ],
                                [
                                    'label' => $submenuIcon . Yii::t('app', 'EBVA'),
                                    'url' => ['/finance/analytics/ebva-sales'],
                                    'active' => in_array(Yii::$app->controller->getRoute(), [
                                        'finance/analytics/ebva-sales',
                                        'finance/analytics/ebva-costs',

                                    ])
                                ]
                            ]
                        ],
                        [
                            'label' => '<i class="nav-icon fas fa-money-bill-wave"></i><p>' . ucfirst(Yii::t('app', 'centralizers')) . '<i class="right fas fa-angle-left"></i></p>',
                            'linkOptions' => ['class' => 'cursor-pointer'],
                            'items' => [
                                [
                                    'label' => $submenuIcon . ucfirst(Yii::t('app', 'cash flow')),
                                    'url' => ['/finance-centralizer/cashflow/index'],
                                    'active' => in_array(Yii::$app->controller->getRoute(), [
                                        'finance-centralizer/cashflow/index',
                                        'finance-centralizer/cashflow/view',
                                        'finance-centralizer/cashflow/update'
                                    ])
                                ],
                                [
                                    'label' => $submenuIcon . ucfirst(Yii::t('app', 'expenses')),
                                    'url' => ['/finance-centralizer/proj-expense/index'],
                                    'active' => in_array(Yii::$app->controller->getRoute(), [
                                        'finance-centralizer/proj-expense/index',
                                    ])
                                ],
                            ]
                        ]
                    ]
                ];
                // monada
                $menuLinks['app']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-solid fa-cubes"></i><p>' . Yii::t('app', 'Monada') . '</p>',
                    'url' => ['/monada/monada/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'monada/monada/index',
                    ]),
                    'visible' => Yii::$app->user->can('SuperAdmin')
                ];

                //DESIGN
                $menuLinks['design'] = [
                    'label' => '<p>' . Yii::t('app', 'DESIGN') . '<i class="right fas fa-angle-left"></i></p>',
                    'url' => null,
                    'linkOptions' => ['class' => 'nav-header cursor-pointer'],
                    'visible' => Helper::checkRoute('/design/project/index')
                        || Helper::checkRoute('/design/building/index')
                        || Helper::checkRoute('/design/level/index')
                        || Helper::checkRoute('/design/level-room/index')
                        || Helper::checkRoute('/design/activity/index')
                        || Helper::checkRoute('/design/typology/index')
                        || Helper::checkRoute('/design/speciality/index')
                        || Helper::checkRoute('/design/stage/index')
                        || Helper::checkRoute('/design/room/index')
                        || Helper::checkRoute('/design/phase/index')
                ];
                //projects
                $menuLinks['design']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-book"></i><p>' . Yii::t('app', 'Projects') . '</p>',
                    'url' => ['/design/project/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'design/project/index',
                        'design/project/view',
                        'design/project/update',
                        'design/project/create',
                        'design/building/index',
                        'design/building/view',
                        'design/building/update',
                        'design/building/create',
                        'design/level/index',
                        'design/level/view',
                        'design/level/update',
                        'design/level/create',
                        'design/level-room/index',
                        'design/level-room/view',
                        'design/level-room/update',
                        'design/level-room/create',
                    ]),
                ];
                //activities
                $menuLinks['design']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-list-ul"></i><p>' . Yii::t('app', 'Activities') . '</p>',
                    'url' => ['/design/activity/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'design/activity/index',
                        'design/activity/view',
                        'design/activity/update',
                        'design/activity/create',
                    ]),
                ];
                //typologies
                $menuLinks['design']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-folder-open"></i><p>' . Yii::t('app', 'Typologies') . '</p>',
                    'url' => ['/design/typology/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'design/typology/index',
                        'design/typology/view',
                        'design/typology/update',
                        'design/typology/create',
                        'design/typology/assign-activities',
                    ]),
                ];
                //design others
                $menuLinks['design']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-ellipsis-h"></i><p>' . Yii::t('app', 'Others') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'design/speciality/index',
                        'design/speciality/view',
                        'design/speciality/update',
                        'design/speciality/create',
                        'design/phase/index',
                        'design/phase/view',
                        'design/phase/update',
                        'design/phase/create',
                        'design/stage/index',
                        'design/stage/view',
                        'design/stage/update',
                        'design/stage/create',
                        'design/room/index',
                        'design/room/view',
                        'design/room/update',
                        'design/room/create'
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Specialities'),
                            'url' => ['/design/speciality/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'design/speciality/index',
                                'design/speciality/view',
                                'design/speciality/update',
                                'design/speciality/create'
                            ])],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Phases'),
                            'url' => ['/design/phase/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'design/phase/index',
                                'design/phase/view',
                                'design/phase/update',
                                'design/phase/create'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Stages'),
                            'url' => ['/design/stage/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'design/stage/index',
                                'design/stage/view',
                                'design/stage/update',
                                'design/stage/create'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Rooms'),
                            'url' => ['/design/room/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'design/room/index',
                                'design/room/view',
                                'design/room/update',
                                'design/room/create'
                            ])
                        ]
                    ]
                ];

                //BUILD
                $menuLinks['build'] = [
                    'label' => '<p>' . Yii::t('app', 'EXECUTION') . '<i class="right fas fa-angle-left"></i></p>',
                    'url' => null,
                    'linkOptions' => ['class' => 'nav-header cursor-pointer'],
                    'visible' =>
                        Helper::checkRoute('/build/article/index')
                        || Helper::checkRoute('/build/article-category/index')
                        || Helper::checkRoute('/build/article-subcategory/index')
                        || Helper::checkRoute('/build/equipment/index')
                        || Helper::checkRoute('/build/equipment-category/index')
                        || Helper::checkRoute('/build/equipment-subcategory/index')
                        || Helper::checkRoute('/build/package/index')
                        || Helper::checkRoute('/build/item-validate/index')
                ];
                //build articles
                $menuLinks['build']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-ruler"></i><p>' . Yii::t('app', 'Articles') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'build/article/index',
                        'build/article/view',
                        'build/article/update',
                        'build/article/create',
                        'build/article/import',
                        'build/article/export',
                        'build/article-category/index',
                        'build/article-category/create',
                        'build/article-category/update',
                        'build/article-category/view',
                        'build/article-subcategory/index',
                        'build/article-subcategory/create',
                        'build/article-subcategory/update',
                        'build/article-subcategory/view',
                        'build/item-validate/index',
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Articles'),
                            'url' => ['/build/article/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'build/article/index',
                                'build/article/view',
                                'build/article/update',
                                'build/article/create',
                                'build/article/import',
                                'build/article/export',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Unvalidated articles'),
                            'url' => ['/build/item-validate/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'build/item-validate/index',
                                'build/item-validate/validate',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Articles categories'),
                            'url' => ['/build/article-category/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'build/article-category/index',
                                'build/article-category/create',
                                'build/article-category/update',
                                'build/article-category/view'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Articles subcategories'),
                            'url' => ['/build/article-subcategory/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'build/article-subcategory/index',
                                'build/article-subcategory/create',
                                'build/article-subcategory/update',
                                'build/article-subcategory/view'
                            ])
                        ]
                    ]
                ];
                //build equipments
                $menuLinks['build']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-sink"></i><p>' . Yii::t('app', 'Equipments') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'build/equipment/index',
                        'build/equipment/view',
                        'build/equipment/update',
                        'build/equipment/create',
                        'build/equipment/export',
                        'build/equipment-category/index',
                        'build/equipment-category/create',
                        'build/equipment-category/update',
                        'build/equipment-category/view',
                        'build/equipment-subcategory/index',
                        'build/equipment-subcategory/create',
                        'build/equipment-subcategory/update',
                        'build/equipment-subcategory/view'
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Equipments'),
                            'url' => ['/build/equipment/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'build/equipment/index',
                                'build/equipment/view',
                                'build/equipment/update',
                                'build/equipment/create',
                                'build/equipment/export',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Equipment categories'),
                            'url' => ['/build/equipment-category/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'build/equipment-category/index',
                                'build/equipment-category/create',
                                'build/equipment-category/update',
                                'build/equipment-category/view'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Equipment Subcategories'),
                            'url' => ['/build/equipment-subcategory/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'build/equipment-subcategory/index',
                                'build/equipment-subcategory/create',
                                'build/equipment-subcategory/update',
                                'build/equipment-subcategory/view'
                            ])
                        ]
                    ]
                ];
                //build others
                $menuLinks['build']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-ellipsis-h"></i><p>' . Yii::t('app', 'Others') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'build/package/index',
                        'build/package/view',
                        'build/package/update',
                        'build/package/create'
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Packages'),
                            'url' => ['/build/package/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'build/package/index',
                                'build/package/view',
                                'build/package/update',
                                'build/package/create'
                            ])
                        ]
                    ]
                ];

                //WINDOC
                $menuLinks['windoc'] = [
                    'label' => '<p>' . strtoupper(Yii::t('app', 'Windoc')) . '<i class="right fas fa-angle-left"></i></p>',
                    'url' => null,
                    'linkOptions' => ['class' => 'nav-header cursor-pointer'],
                    'visible' =>
                        Helper::checkRoute('/windoc/article/index')
                        || Helper::checkRoute('/windoc/recipe/index')
                        || Helper::checkRoute('/windoc/auction-execution/index')
                ];
                //windoc recipe
                $menuLinks['windoc']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-cogs"></i><p>' . Yii::t('app', 'Recipes') . '</p>',
                    'url' => ['/windoc/recipe/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'windoc/recipe/index',
                        'windoc/recipe/view',
                        'windoc/recipe/import',
                        'windoc/recipe/update',
                        'windoc/recipe/create',
                    ]),
                ];
                //windoc article
                $menuLinks['windoc']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-ruler"></i><p>' . Yii::t('app', 'Articles') . '</p>',
                    'url' => ['/windoc/article/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'windoc/article/index',
                        'windoc/article/view',
                        'windoc/article/update',
                        'windoc/article/create',
                    ]),
                ];
                //windoc activity
                $menuLinks['windoc']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-list-ul"></i><p>' . Yii::t('app', 'Resources') . '</p>',
                    'url' => ['/windoc/resource/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'windoc/resource/index',
                        'windoc/resource/view',
                        'windoc/resource/update',
                        'windoc/resource/create',
                    ]),
                ];
                //windoc auction execution
                $menuLinks['windoc']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-clipboard-list"></i><p>' . Yii::t('app', 'Auction execution') . '</p>',
                    'url' => ['/windoc/auction-execution/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'windoc/auction-execution/index',
                        'windoc/auction-execution/import-auction',
                        'windoc/auction-execution/content',
                        'windoc/auction-execution/pair-auction-article',
                    ]),
                ];
                //windoc estimate article
                $menuLinks['windoc']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-list-ul"></i><p>' . Yii::t('app', 'Estimate articles') . '</p>',
                    'url' => ['/windoc/estimate-article/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'windoc/estimate-article/index',
                    ]),
                ];

                //windoc estimate recipes
                $menuLinks['windoc']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-cogs"></i><p>' . Yii::t('app', 'Estimate recipes') . '</p>',
                    'url' => ['/windoc/estimate-recipe/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'windoc/estimate-recipe/index',
                        'windoc/estimate-recipe/view',
                        'windoc/estimate-recipe/update',
                        'windoc/estimate-recipe/update-default-recipes',
                    ]),
                ];

                //REVIT
                $menuLinks['revit'] = [
                    'label' => '<p>' . strtoupper(Yii::t('app', 'Revit')) . '<i class="right fas fa-angle-left"></i></p>',
                    'url' => null,
                    'linkOptions' => ['class' => 'nav-header cursor-pointer'],
                    'visible' => Helper::checkRoute('/revit/revit-family/index')
                        || Helper::checkRoute('/revit/revit-family-name/index')
                        || Helper::checkRoute('/revit/revit-project/index')
                        || Helper::checkRoute('/revit/unit-measure-associate/index')
                ];
                //revit family
                $menuLinks['revit']['items'][] = [
                    'label' => $submenuIcon . Yii::t('app', 'Revit Families'),
                    'url' => ['/revit/revit-family/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'revit/revit-family/index',
                        'revit/revit-family/view',
                        'revit/revit-family/update',
                        'revit/revit-family/create',
                        'revit/revit-family/import'
                    ]),
                ];
                //revit family name
                $menuLinks['revit']['items'][] = [
                    'label' => $submenuIcon . Yii::t('app', 'Revit Families Name'),
                    'url' => ['/revit/revit-family-name/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'revit/revit-family-name/index',
                        'revit/revit-family-name/view',
                        'revit/revit-family-name/update',
                        'revit/revit-family-name/create',
                    ]),
                ];
                //revit project
                $menuLinks['revit']['items'][] = [
                    'label' => $submenuIcon . Yii::t('app', 'Revit Projects'),
                    'url' => ['/revit/revit-project/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'revit/revit-project/index',
                        'revit/revit-project/view',
                    ]),
                ];
                //unit-measure
                $menuLinks['revit']['items'][] = [
                    'label' => $submenuIcon . Yii::t('app', 'Association U.M.'),
                    'url' => ['/revit/unit-measure-associate/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'revit/unit-measure-associate/index',
                        'revit/unit-measure-associate/view',
                        'revit/unit-measure-associate/create',
                        'revit/unit-measure-associate/update',
                    ]),
                ];

                //HR
                $menuLinks['hr'] = [
                    'label' => '<p>' . Yii::t('app', 'HR') . '<i class="right fas fa-angle-left"></i></p>',
                    'url' => null,
                    'linkOptions' => ['class' => 'nav-header cursor-pointer'],
                    'visible' =>
                        Helper::checkRoute('/hr/employee/index')
                        || Helper::checkRoute('/hr/time-off/working-days')
                        || Helper::checkRoute('/hr/time-off/employee-working-days')
                        || Helper::checkRoute('/hr/time-off/employee-permissions')
                        || Helper::checkRoute('/hr/time-off/employee-over-time')
                        || Helper::checkRoute('/hr/department/index')
                        || Helper::checkRoute('/hr/position-cor/index')
                        || Helper::checkRoute('/hr/position-internal/index')
                        || Helper::checkRoute('/hr/eval-kpi/index')
                        || Helper::checkRoute('/hr/eval-kpi-category/index')
                        || Helper::checkRoute('/hr/office/index')
                        || Helper::checkRoute('/hr/work-location/index')
                        || Helper::checkRoute('/hr/eval-kpi-position-internal/index')
                        || Helper::checkRoute('/hr/eval-evaluator-evaluated/index')
                        || Helper::checkRoute('/hr/eval-kpi-relation/index')
                        || Helper::checkRoute('/hr/employee-position-internal/index')
                        || Helper::checkRoute('/hr/holiday-type/index')
                        || Helper::checkRoute('/hr/time-off/request-records')
                        || Helper::checkRoute('/hr/shift/report')
                        || Helper::checkRoute('/hr/shift/attendance-register')
                        || Helper::checkRoute('/hr/countersignature-evaluation/index')
                        || Helper::checkRoute('/hr/template-time-off/index')
                        || Helper::checkRoute('/hr/employee-company')
                ];
                //hr employee
                $menuLinks['hr']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-user-friends"></i><p>' . Yii::t('app', 'Employees') . '</p>',
                    'url' => ['/hr/employee/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'hr/employee/index',
                        'hr/employee/view',
                        'hr/employee/update',
                        'hr/employee/create',
                        'hr/employee-company/create',
                        'hr/employee-company/update',
                        'hr/employee-company/view',
                    ]),
                ];
                //hr work dates
                $menuLinks['hr']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-calendar-alt"></i><p>' . Yii::t('app', 'Holidays / Permissions') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'hr/time-off/working-days',
                        'hr/time-off/employee-working-days',
                        'hr/time-off/employee-permissions',
                        'hr/time-off/employee-over-time',
                        'hr/redmine-office-spent-time/index',
                        'hr/holiday-type/index',
                        'hr/holiday-type/create',
                        'hr/holiday-type/update',
                        'hr/holiday-type/delete',
                        'hr/time-off/request-records',
                        'hr/template-time-off/index',
                        'hr/template-time-off/view',
                        'hr/template-time-off/update',
                        'hr/template-time-off/create'
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Working days'),
                            'url' => ['/hr/time-off/working-days'],
                            'active' => Yii::$app->controller->getRoute() == 'hr/time-off/working-days',
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Records of holidays'),
                            'url' => ['/hr/time-off/employee-working-days'],
                            'active' => Yii::$app->controller->getRoute() == 'hr/time-off/employee-working-days'
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Records of permissions'),
                            'url' => ['/hr/time-off/employee-permissions'],
                            'active' => Yii::$app->controller->getRoute() == 'hr/time-off/employee-permissions'
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Records of overtime'),
                            'url' => ['/hr/time-off/employee-over-time'],
                            'active' => Yii::$app->controller->getRoute() == 'hr/time-off/employee-over-time'
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Holiday type'),
                            'url' => ['/hr/holiday-type/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'hr/holiday-type/index',
                                'hr/holiday-type/view',
                                'hr/holiday-type/update',
                                'hr/holiday-type/create'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Requests of holidays and permissions'),
                            'url' => ['/hr/time-off/request-records'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'hr/time-off/request-records',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Template time off'),
                            'url' => ['/hr/template-time-off/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'hr/template-time-off/index',
                                'hr/template-time-off/view',
                                'hr/template-time-off/update',
                                'hr/template-time-off/create'
                            ])
                        ]
                    ]
                ];
                //hr evaluations
                $menuLinks['hr']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-user-graduate"></i><p>' . Yii::t('app', 'Evaluations') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'hr/eval-kpi/index',
                        'hr/eval-kpi/view',
                        'hr/eval-kpi/update',
                        'hr/eval-kpi/create',
                        'hr/eval-kpi/import',
                        'hr/eval-kpi-category/index',
                        'hr/eval-kpi-category/view',
                        'hr/eval-kpi-category/update',
                        'hr/eval-kpi-category/create',
                        'hr/countersignature-evaluation/index',
                        'hr/countersignature-evaluation/view',
                        'hr/countersignature-evaluation/update',
                        'hr/eval-kpi-position-internal/index',
                        'hr/eval-kpi-position-internal/import',
                        'hr/employee-position-internal/index',
                        'hr/eval-evaluator-evaluated/index',
                        'hr/eval-kpi-relation/index'
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Countersignature evaluations'),
                            'url' => ['/hr/countersignature-evaluation/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'hr/countersignature-evaluation/index',
                                'hr/countersignature-evaluation/view',
                                'hr/countersignature-evaluation/update'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'KPIs Categories'),
                            'url' => ['/hr/eval-kpi-category/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'hr/eval-kpi-category/index',
                                'hr/eval-kpi-category/view',
                                'hr/eval-kpi-category/update',
                                'hr/eval-kpi-category/create'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'KPIs'),
                            'url' => ['/hr/eval-kpi/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'hr/eval-kpi/index',
                                'hr/eval-kpi/view',
                                'hr/eval-kpi/update',
                                'hr/eval-kpi/create',
                                'hr/eval-kpi/import'
                            ])
                        ],
                        [
                            'label' => '<i class="nav-icon fas fa-sitemap"></i><p>' . Yii::t('app', 'Associations') . '<i class="right fas fa-angle-left"></i></p>',
                            'linkOptions' => ['class' => 'cursor-pointer'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'hr/eval-kpi-position-internal/index',
                                'hr/eval-kpi-position-internal/import',
                                'hr/employee-position-internal/index',
                                'hr/eval-evaluator-evaluated/index',
                                'hr/eval-kpi-relation/index'
                            ]),
                            'items' => [
                                [
                                    'label' => $submenuIcon . Yii::t('app', 'KPIs to internal position'),
                                    'url' => ['/hr/eval-kpi-position-internal/index'],
                                    'active' => in_array(Yii::$app->controller->getRoute(), [
                                        'hr/eval-kpi-position-internal/index',
                                        'hr/eval-kpi-position-internal/import',
                                    ])
                                ],
                                [
                                    'label' => $submenuIcon . Yii::t('app', 'Internal positions to employee'),
                                    'url' => ['/hr/employee-position-internal/index'],
                                    'active' => in_array(Yii::$app->controller->getRoute(), [
                                        'hr/employee-position-internal/index',
                                    ])
                                ],
                                [
                                    'label' => $submenuIcon . Yii::t('app', 'Evaluators to evaluated'),
                                    'url' => ['/hr/eval-evaluator-evaluated/index'],
                                    'active' => in_array(Yii::$app->controller->getRoute(), [
                                        'hr/eval-evaluator-evaluated/index'
                                    ])
                                ],
                                [
                                    'label' => $submenuIcon . Yii::t('app', 'KPIs to relations'),
                                    'url' => ['/hr/eval-kpi-relation/index'],
                                    'active' => in_array(Yii::$app->controller->getRoute(), [
                                        'hr/eval-kpi-relation/index'
                                    ])
                                ]
                            ]
                        ],
                    ]
                ];

                //hr reports
                $menuLinks['hr']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-clipboard-list"></i><p>' . Yii::t('app', 'Reports') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'hr/shift/report',
                        'hr/shift/attendance-register',
                        'hr/evaluation/report',
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Daily attendance'),
                            'url' => ['/hr/shift/attendance-register'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'hr/shift/attendance-register'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Shift report'),
                            'url' => ['/hr/shift/report'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'hr/shift/report'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Evaluation report'),
                            'url' => ['/hr/evaluation/report'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'hr/evaluation/report',
                            ])
                        ],
                    ],
                ];

                //hr others
                $menuLinks['hr']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-ellipsis-h"></i><p>' . Yii::t('app', 'Others') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'hr/department/index',
                        'hr/department/view',
                        'hr/department/update',
                        'hr/department/create',
                        'hr/position-cor/index',
                        'hr/position-cor/view',
                        'hr/position-cor/update',
                        'hr/position-cor/create',
                        'hr/position-internal/index',
                        'hr/position-internal/view',
                        'hr/position-internal/update',
                        'hr/position-internal/create',
                        'hr/office/index',
                        'hr/office/view',
                        'hr/office/update',
                        'hr/office/create',
                        'hr/work-location/index',
                        'hr/work-location/view',
                        'hr/work-location/update',
                        'hr/work-location/create'
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Departments'),
                            'url' => ['/hr/department/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'hr/department/index',
                                'hr/department/view',
                                'hr/department/update',
                                'hr/department/create'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Offices'),
                            'url' => ['/hr/office/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'hr/office/index',
                                'hr/office/view',
                                'hr/office/update',
                                'hr/office/create'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Positions') . ' COR',
                            'url' => ['/hr/position-cor/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'hr/position-cor/index',
                                'hr/position-cor/view',
                                'hr/position-cor/update',
                                'hr/position-cor/create'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Internal positions'),
                            'url' => ['/hr/position-internal/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'hr/position-internal/index',
                                'hr/position-internal/view',
                                'hr/position-internal/update',
                                'hr/position-internal/create'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Work locations'),
                            'url' => ['/hr/work-location/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'hr/work-location/index',
                                'hr/work-location/view',
                                'hr/work-location/update',
                                'hr/work-location/create'
                            ])
                        ],
                    ]
                ];

                //FINANCE
                $menuLinks['fin'] = [
                    'label' => '<p>' . Yii::t('app', 'FINANCE') . '<i class="right fas fa-angle-left"></i></p>',
                    'url' => null,
                    'linkOptions' => ['class' => 'nav-header cursor-pointer'],
                    'visible' =>
                        Helper::checkRoute('/finance/account/index')
                        || Helper::checkRoute('/finance/account-category-general/index')
                        || Helper::checkRoute('/finance/account-category-specific/index')
                        || Helper::checkRoute('/finance/cost-center/index')
                        || Helper::checkRoute('/finance/invoice/index')
                        || Helper::checkRoute('/finance/invoice/reports-kpi')
                        || Helper::checkRoute('/finance/invoice-header-raw/index')
                        || Helper::checkRoute('/finance/invoice-body-raw/index')
                        || Helper::checkRoute('/finance/accounting-journal/index')
                        || Helper::checkRoute('/finance/balance/index')
                        || Helper::checkRoute('/finance/credit-line-financiers/index')
                ];
                //fin invoices
                $menuLinks['fin']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-copy"></i><p>' . Yii::t('app', 'Invoices') . '</p>',
                    'url' => ['/finance/invoice/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'finance/invoice/index',
                        'finance/invoice/view',
                        'finance/invoice/import',
                        'finance/invoice/add-invoice',
                        'finance/invoice/reports',
                        'finance/invoice/classify-files'
                    ]),
                ];
                //fin accounting
                $menuLinks['fin']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-align-justify"></i><p>' . Yii::t('app', 'Accounting Accounts') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'finance/account/index',
                        'finance/account/view',
                        'finance/account/import',
                        'finance/account-category-general/index',
                        'finance/account-category-general/create',
                        'finance/account-category-general/create-subcategory',
                        'finance/account-category-general/update-subcategory',
                        'finance/account-category-general/import',
                        'finance/account-category-specific/index',
                        'finance/account-category-specific/create',
                        'finance/account-category-specific/create-subcategory',
                        'finance/account-category-specific/update-subcategory',
                        'finance/account-category-specific/import',
                    ]),
                    'items' => [
                        ['label' => $submenuIcon . Yii::t('app', 'Accounts'),
                            'url' => ['/finance/account/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'finance/account/index',
                                'finance/account/view',
                                'finance/account/create',
                                'finance/account/update',
                                'finance/account/import'
                            ])],
                        ['label' => $submenuIcon . Yii::t('app', 'Categories general'),
                            'url' => ['/finance/account-category-general/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'finance/account-category-general/index',
                                'finance/account-category-general/create',
                                'finance/account-category-general/create-subcategory',
                                'finance/account-category-general/update',
                                'finance/account-category-general/update-subcategory',
                                'finance/account-category-general/import',
                            ])],
                        ['label' => $submenuIcon . Yii::t('app', 'Categories specific'),
                            'url' => ['/finance/account-category-specific/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'finance/account-category-specific/index',
                                'finance/account-category-specific/create',
                                'finance/account-category-specific/create-subcategory',
                                'finance/account-category-specific/update',
                                'finance/account-category-specific/update-subcategory',
                                'finance/account-category-specific/import',
                            ])],
                    ],
                ];
                //cost centers
                $menuLinks['fin']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-money-check"></i><p>' . Yii::t('app', 'Cost Centers') . '</p>',
                    'url' => ['/finance/cost-center/index', 'status' => 'new'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'finance/cost-center/index',
                        'finance/cost-center/view',
                        'finance/cost-center/create',
                        'finance/cost-center/update',
                        'finance/cost-center/import',
                    ]),
                ];
                //accounting journal
                $menuLinks['fin']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-book"></i> <p>' . Yii::t('app', 'Accounting journal') . '</p>',
                    'url' => ['/finance/accounting-journal/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'finance/accounting-journal/index',
                        'finance/accounting-journal/view',
                        'finance/accounting-journal/import'
                    ])
                ];
                //balance
                $menuLinks['fin']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-balance-scale"></i> <p>' . Yii::t('app', 'Balances') . '</p>',
                    'url' => ['/finance/balance/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'finance/balance/index',
                        'finance/balance/view',
                        'finance/balance/import'
                    ])
                ];

                //fin others
                $menuLinks['fin']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-ellipsis-h"></i><p>' . Yii::t('app', 'Others') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'finance/acquisition/index',
                        'finance/acquisition/view',
                        'finance/acquisition/update',
                        'finance/acquisition/create',
                        'finance/vendor-model/index',
                        'finance/customers-companies/index',
                        'finance/customers-companies/create'
                    ]),
                    'items' => [
                        //fin acquisition
                        [
                            'label' => '<i class="nav-icon fas fa-credit-card"></i> <p>' . Yii::t('app', 'Acquisitions') . '</p>',
                            'url' => ['/finance/acquisition/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'finance/acquisition/index',
                                'finance/acquisition/view',
                                'finance/acquisition/update',
                                'finance/acquisition/create',
                            ])
                        ],
                        [
                            'label' => '<i class="nav-icon fas fa-users"></i> <p>' . ucfirst(Yii::t('app', 'customers')) . '</p>',
                            'url' => ['/finance/customers-companies/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'finance/customers-companies/index',
                                'finance/customers-companies/create'
                            ])
                        ],
                        [
                            'label' => '<i class="nav-icon fas fa-dollar-sign"></i> <p>' . ucfirst(Yii::t('app', 'financiers')) . '</p>',
                            'url' => ['/finance/credit-line-financiers/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'finance/credit-line-financiers/index',
                                'finance/credit-line-financiers/create'
                            ])
                        ],
                        [
                            'label' => '<i class="nav-icon fas fa-industry"></i> <p>' . Yii::t('app', 'Vendors - models/flows') . '</p>',
                            'url' => ['/finance/vendor-model/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'finance/vendor-model/index'
                            ])
                        ]
                    ]
                ];

                //CRM
                $menuLinks['crm'] = [
                    'label' => '<p>' . Yii::t('app', 'CRM') . '<i class="right fas fa-angle-left"></i></p>',
                    'url' => null,
                    'linkOptions' => ['class' => 'nav-header cursor-pointer'],
                    'visible' =>
                        Helper::checkRoute('/crm/company/index')
                        || Helper::checkRoute('/crm/brand/index')
                        || Helper::checkRoute('/crm/brand-model/index')
                        || Helper::checkRoute('/crm/contract-offer/index')
                ];
                //crm company
                $menuLinks['crm']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-city"></i><p>' . Yii::t('app', 'Companies') . '</p>',
                    'url' => ['/crm/company/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'crm/company/index',
                        'crm/company/view',
                        'crm/company/update',
                        'crm/company/create',
                        'crm/company/import',
                    ]),
                ];
                //crm others
                $menuLinks['crm']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-ellipsis-h"></i><p>' . Yii::t('app', 'Others') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'crm/brand/index',
                        'crm/brand/view',
                        'crm/brand/update',
                        'crm/brand/create',
                        'crm/brand-model/index',
                        'crm/brand-model/view',
                        'crm/brand-model/update',
                        'crm/brand-model/create',
                        'crm/contract-offer/index',
                        'crm/contract-offer/view',
                        'crm/contract-offer/update',
                        'crm/contract-offer/create',
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Brands'),
                            'url' => ['/crm/brand/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'crm/brand/index',
                                'crm/brand/view',
                                'crm/brand/update',
                                'crm/brand/create',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Models'),
                            'url' => ['/crm/brand-model/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'crm/brand-model/index',
                                'crm/brand-model/view',
                                'crm/brand-model/update',
                                'crm/brand-model/create',
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Contract-Offers'),
                            'url' => ['/crm/contract-offer/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'crm/contract-offer/index',
                                'crm/contract-offer/view',
                                'crm/contract-offer/update',
                                'crm/contract-offer/create',
                            ])
                        ],
                    ]
                ];

                //LOCATION
                $menuLinks['location'] = [
                    'label' => '<p>' . Yii::t('app', 'LOCATION') . '<i class="right fas fa-angle-left"></i></p>',
                    'url' => null,
                    'linkOptions' => ['class' => 'nav-header cursor-pointer'],
                    'visible' =>
                        Helper::checkRoute('/location/country/index')
                ];
                // location countries
                $menuLinks['location']['items'][] = [
                    'label' => '<i class="nav-icon fa fa-solid fa-globe-europe"></i><p>' . Yii::t('app', 'Countries') . '</p>',
                    'url' => ['/location/country/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'location/country/index',
                        'location/country/view',
                        'location/country/update',
                        'location/country/create',
                        'location/country/delete',
                    ]),
                ];
                // location counties
                $menuLinks['location']['items'][] = [
                    'label' => '<i class="nav-icon fa fa-solid fa-map-marked"></i><p>' . Yii::t('app', 'Counties') . '</p>',
                    'url' => ['/location/state/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'location/state/index',
                        'location/state/view',
                        'location/state/update',
                        'location/state/create',
                        'location/state/delete',
                    ]),
                ];
                // location cities
                $menuLinks['location']['items'][] = [
                    'label' => '<i class="nav-icon fa fa-solid fa-city"></i><p>' . Yii::t('app', 'Cities') . '</p>',
                    'url' => ['/location/city/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'location/city/index',
                        'location/city/view',
                        'location/city/update',
                        'location/city/create',
                        'location/city/delete',
                    ]),
                ];

                //ADMIN
                $menuLinks['adm'] = [
                    'label' => '<p>' . Yii::t('app', 'ADMINISTRATION') . '<i class="right fas fa-angle-left"></i></p>',
                    'url' => null,
                    'linkOptions' => ['class' => 'nav-header cursor-pointer'],
                    'visible' => Helper::checkRoute('adm/user/index')
                ];
                //adm users
                $menuLinks['adm']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-users"></i><p>' . Yii::t('app', 'Users') . '</p>',
                    'url' => ['/adm/user/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'adm/user/index',
                        'adm/user/view',
                        'adm/user/update',
                        'adm/user/create',
                    ]),
                ];
                //adm measure units
                $menuLinks['adm']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-balance-scale"></i><p>' . Yii::t('app', 'Measure Units') . '</p>',
                    'url' => ['/build/measure-unit/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'build/measure-unit/index',
                        'build/measure-unit/view',
                        'build/measure-unit/update',
                        'build/measure-unit/create',
                    ]),
                ];
                //adm erp companies
                $menuLinks['adm']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-building"></i><p>' . Yii::t('app', 'Erp companies') . '</p>',
                    'url' => ['/adm/erp-company/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'adm/erp-company/index',
                        'adm/erp-company/view',
                        'adm/erp-company/update',
                        'adm/erp-company/create',
                    ]),
                ];

                //PMP
                $menuLinks['pmp'] = [
                    'label' => '<p>' . Yii::t('app', 'PMP') . '<i class="right fas fa-angle-left"></i></p>',
                    'url' => null,
                    'linkOptions' => ['class' => 'nav-header cursor-pointer'],
                    'visible' =>
                        Helper::checkRoute('/pmp/device/index')
                        || Helper::checkRoute('/pmp/project/index')
                        || Helper::checkRoute('/pmp/product/index')
                        || Helper::checkRoute('/pmp/product-component/index')
                        || Helper::checkRoute('/pmp/page/index')
                        || Helper::checkRoute('/pmp/feature/index')
                        || Helper::checkRoute('/pmp/employee/index')
                        || Helper::checkRoute('/pmp/system/index')
                        || Helper::checkRoute('/pmp/domain/index')
                        || Helper::checkRoute('/pmp/position/index')
                ];
                $menuLinks['pmp']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-desktop"></i><p>' . Yii::t('app', 'Devices') . '</p>',
                    'url' => ['/pmp/device/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'pmp/device/index',
                        'pmp/device/view',
                        'pmp/device/update',
                    ]),
                ];
                $menuLinks['pmp']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-book"></i><p>' . Yii::t('app', 'Projects') . '</p>',
                    'url' => ['/pmp/project/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'pmp/project/index',
                        'pmp/project/view',
                        'pmp/project/update',
                        'pmp/project/create',
                    ]),
                ];
                $menuLinks['pmp']['items'][] = [
                    'label' => '<i class="nav-icon fab fa-product-hunt"></i><p>' . Yii::t('app', 'Products') . '</p>',
                    'url' => ['/pmp/product/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'pmp/product/index',
                        'pmp/product/view',
                        'pmp/product/update',
                        'pmp/product/create',
                        'pmp/product-history/index',
                        'pmp/product-history/create',
                        'pmp/changelog/index',
                        'pmp/changelog/create',
                        'pmp/changelog/update',
                        'pmp/test-scenario/index',
                        'pmp/test-scenario/view',
                        'pmp/test-scenario/create',
                        'pmp/test-scenario/update',
                        'pmp/test-case/index',
                        'pmp/test-case/view',
                        'pmp/test-case/create',
                        'pmp/test-case/update',
                        'pmp/test-case-output/index',
                        'pmp/test-case-output/create',
                        'pmp/test-case-output/view',
                        'pmp/test-case-output/update',
                    ]),
                ];
                $menuLinks['pmp']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-cogs"></i><p>' . Yii::t('app', 'Components') . '</p>',
                    'url' => ['/pmp/product-component/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'pmp/product-component/index',
                        'pmp/product-component/view',
                        'pmp/product-component/update',
                        'pmp/product-component/create',
                    ]),
                ];
                $menuLinks['pmp']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-book-open"></i><p>' . Yii::t('app', 'Pages') . '</p>',
                    'url' => ['/pmp/page/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'pmp/page/index',
                        'pmp/page/view',
                        'pmp/page/update',
                        'pmp/page/create',
                    ]),
                ];
                $menuLinks['pmp']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-search-plus"></i><p>' . Yii::t('app', 'Functionalities') . '</p>',
                    'url' => ['/pmp/feature/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'pmp/feature/index',
                        'pmp/feature/view',
                        'pmp/feature/update',
                        'pmp/feature/create',
                    ]),
                ];
                $menuLinks['pmp']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-user-friends"></i><p>' . Yii::t('app', 'Employees') . '</p>',
                    'url' => ['/pmp/employee/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'pmp/employee/index',
                        'pmp/employee/view',
                        'pmp/employee/update',
                        'pmp/employee/create',
                    ]),
                ];
                //pmp QA
                $menuLinks['pmp']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-ellipsis-h"></i><p>' . Yii::t('app', 'QA') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'pmp/test-case-history/index',
                        'pmp/test-case-history/view',
                        'pmp/test-case-history/update',
                        'pmp/test-case-history/create',
                        'pmp/test-case-history-output/index',
                        'pmp/test-case-history-output/view',
                        'pmp/test-case-history-output/update',
                        'pmp/test-case-history-output/create'
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Test results'),
                            'url' => ['/pmp/test-case-history/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'pmp/test-case-history/index',
                                'pmp/test-case-history/view',
                                'pmp/test-case-history/update',
                                'pmp/test-case-history/create',
                                'pmp/test-case-history-output/index',
                                'pmp/test-case-history-output/view',
                                'pmp/test-case-history-output/update',
                                'pmp/test-case-history-output/create'
                            ])
                        ]
                    ]
                ];
                //pmp others
                $menuLinks['pmp']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-ellipsis-h"></i><p>' . Yii::t('app', 'Others') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'pmp/system/index',
                        'pmp/system/view',
                        'pmp/system/update',
                        'pmp/system/create',
                        'pmp/domain/index',
                        'pmp/domain/view',
                        'pmp/domain/update',
                        'pmp/domain/create',
                        'pmp/position/index',
                        'pmp/position/view',
                        'pmp/position/update',
                        'pmp/position/create',
                        'pmp/feature-level/index',
                        'pmp/feature-level/view',
                        'pmp/feature-level/update',
                        'pmp/feature-level/create',
                        'pmp/feature-type/index',
                        'pmp/feature-type/view',
                        'pmp/feature-type/update',
                        'pmp/feature-type/create',
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Systems'),
                            'url' => ['/pmp/system/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'pmp/system/index',
                                'pmp/system/view',
                                'pmp/system/update',
                                'pmp/system/create'
                            ])
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Domains'),
                            'url' => ['/pmp/domain/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'pmp/domain/index',
                                'pmp/domain/view',
                                'pmp/domain/update',
                                'pmp/domain/create'
                            ])
                        ],
                        $menuLinks['pmp']['items']['nextItems'][] = [
                            'label' => '<i class="nav-icon fas fa-ellipsis-h"></i><p>' . Yii::t('app', 'Employees') . '<i class="right fas fa-angle-left"></i></p>',
                            'linkOptions' => ['class' => 'cursor-pointer'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'pmp/position/index',
                                'pmp/position/view',
                                'pmp/position/update',
                                'pmp/position/create',
                            ]),
                            'items' => [
                                [
                                    'label' => $submenuIcon . Yii::t('app', 'Positions'),
                                    'url' => ['/pmp/position/index'],
                                    'active' => in_array(Yii::$app->controller->getRoute(), [
                                        'pmp/position/index',
                                        'pmp/position/view',
                                        'pmp/position/update',
                                        'pmp/position/create'
                                    ])
                                ],
                            ]
                        ],
                        $menuLinks['pmp']['items']['nextItems'][] = [
                            'label' => '<i class="nav-icon fas fa-ellipsis-h"></i><p>' . Yii::t('app', 'Functionalities') . '<i class="right fas fa-angle-left"></i></p>',
                            'linkOptions' => ['class' => 'cursor-pointer'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'pmp/feature-level/index',
                                'pmp/feature-level/view',
                                'pmp/feature-level/update',
                                'pmp/feature-level/create',
                                'pmp/feature-type/index',
                                'pmp/feature-type/view',
                                'pmp/feature-type/update',
                                'pmp/feature-type/create',
                            ]),
                            'items' => [
                                [
                                    'label' => $submenuIcon . Yii::t('app', 'Levels'),
                                    'url' => ['/pmp/feature-level/index'],
                                    'active' => in_array(Yii::$app->controller->getRoute(), [
                                        'pmp/feature-level/index',
                                        'pmp/feature-level/view',
                                        'pmp/feature-level/update',
                                        'pmp/feature-level/create'
                                    ])
                                ],
                                [
                                    'label' => $submenuIcon . Yii::t('app', 'Types'),
                                    'url' => ['/pmp/feature-type/index'],
                                    'active' => in_array(Yii::$app->controller->getRoute(), [
                                        'pmp/feature-type/index',
                                        'pmp/feature-type/view',
                                        'pmp/feature-type/update',
                                        'pmp/feature-type/create'
                                    ])
                                ],
                            ],
                        ],
                    ]
                ];

                //AUTO
                $menuLinks['auto'] = [
                    'label' => '<p>' . Yii::t('app', 'AUTO') . '<i class="right fas fa-angle-left"></i></p>',
                    'url' => null,
                    'linkOptions' => ['class' => 'nav-header cursor-pointer'],
                    'visible' =>
                        Helper::checkRoute('/auto/car/index')
                        || Helper::checkRoute('/auto/fuel/index')
                        || Helper::checkRoute('/auto/car-document/document')
                        || Helper::checkRoute('/auto/car-detail/detail')
                        || Helper::checkRoute('/auto/location-type/index')
                        || Helper::checkRoute('/auto/location/index')
                        || Helper::checkRoute('/auto/journey/index')
                        || Helper::checkRoute('/auto/fuel-station/index')
                        || Helper::checkRoute('/auto/tax-receipt/index')
                        || Helper::checkRoute('/auto/roadmap/index')

                ];
                $menuLinks['auto']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-car"></i><p>' . Yii::t('app', 'Cars') . '</p>',
                    'url' => ['/auto/car/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'auto/car/index',
                        'auto/car/view',
                        'auto/car/update',
                        'auto/car/create',
                        'auto/car-document/document',
                        'auto/car-detail/detail',
                    ]),
                    'visible' => !Yii::$app->user->can('CarsAdministrator')
                ];
                $menuLinks['auto']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-map-marker-alt"></i><p>' . Yii::t('app', 'Locations') . '</p>',
                    'url' => ['/auto/location/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'auto/location/index',
                        'auto/location/view',
                        'auto/location/update',
                        'auto/location/create',
                    ]),
                    'visible' => !Yii::$app->user->can('CarsAdministrator')
                ];
                $menuLinks['auto']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-map-marked-alt"></i><p>' . Yii::t('app', 'Journeys') . '</p>',
                    'url' => ['/auto/journey/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'auto/journey/index',
                        'auto/journey/view',
                        'auto/journey/update',
                    ]),
                    'visible' => !Yii::$app->user->can('CarsAdministrator')
                ];
                $menuLinks['auto']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-gas-pump"></i><p>' . Yii::t('app', 'Fuel stations') . '</p>',
                    'url' => ['/auto/fuel-station/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'auto/fuel-station/index',
                        'auto/fuel-station/view',
                        'auto/fuel-station/update',
                        'auto/fuel-station/create',
                    ]),
                    'visible' => !Yii::$app->user->can('CarsAdministrator')
                ];
                $menuLinks['auto']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-receipt"></i><p>' . Yii::t('app', 'Tax receipt') . '</p>',
                    'url' => ['/auto/tax-receipt/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'auto/tax-receipt/index',
                        'auto/tax-receipt/view',
                        'auto/tax-receipt/update',
                        'auto/tax-receipt/create',
                    ]),
                    'visible' => !Yii::$app->user->can('CarsAdministrator')
                ];
                $menuLinks['auto']['items'][] = [
                    'label' => '<i class="nav-icon fab fa-readme"></i><p>' . Yii::t('app', 'Roadmaps') . '</p>',
                    'url' => ['/auto/roadmap/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'auto/roadmap/index',
                        'auto/roadmap/view',
                        'auto/roadmap/update',
                        'auto/roadmap/create',
                    ]),
                    'visible' => !Yii::$app->user->can('CarsAdministrator')
                ];
                //auto others
                $menuLinks['auto']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-ellipsis-h"></i><p>' . Yii::t('app', 'Others') . '<i class="right fas fa-angle-left"></i></p>',
                    'linkOptions' => ['class' => 'cursor-pointer'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'auto/fuel/index',
                        'auto/fuel/view',
                        'auto/fuel/update',
                        'auto/fuel/create',
                        'auto/location-type/index',
                        'auto/location-type/view',
                        'auto/location-type/update',
                        'auto/location-type/create'
                    ]),
                    'items' => [
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Fuel'),
                            'url' => ['/auto/fuel/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'auto/fuel/index',
                                'auto/fuel/view',
                                'auto/fuel/update',
                                'auto/fuel/create'
                            ]),
                            'visible' => !Yii::$app->user->can('CarsAdministrator')
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Types of locations'),
                            'url' => ['/auto/location-type/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'auto/location-type/index',
                                'auto/location-type/view',
                                'auto/location-type/update',
                                'auto/location-type/create'
                            ]),
                            'visible' => !Yii::$app->user->can('CarsAdministrator')
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Car accessory'),
                            'url' => ['/auto/accessory/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'auto/accessory/index',
                                'auto/accessory/view',
                                'auto/accessory/update',
                                'auto/accessory/create'
                            ]),
                            'visible' => !Yii::$app->user->can('CarsAdministrator')
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Fuel ring'),
                            'url' => ['/auto/fuel-ring/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'auto/fuel-ring/index',
                                'auto/fuel-ring/view',
                                'auto/fuel-ring/update',
                                'auto/fuel-ring/create'
                            ]),
                            'visible' => !Yii::$app->user->can('CarsAdministrator')
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Fuel card'),
                            'url' => ['/auto/fuel-card/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'auto/fuel-card/index',
                                'auto/fuel-card/view',
                                'auto/fuel-card/update',
                                'auto/fuel-card/create'
                            ]),
                            'visible' => !Yii::$app->user->can('CarsAdministrator')
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Project'),
                            'url' => ['/auto/project/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'auto/project/index',
                                'auto/project/view',
                                'auto/project/update',
                                'auto/project/create'
                            ]),
                            'visible' => !Yii::$app->user->can('CarsAdministrator')
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Validation options'),
                            'url' => ['/auto/validation-option/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'auto/validation-option/index',
                                'auto/validation-option/view',
                                'auto/validation-option/update',
                                'auto/validation-option/create'
                            ]),
                            'visible' => !Yii::$app->user->can('CarsAdministrator')
                        ],
                        [
                            'label' => $submenuIcon . Yii::t('app', 'Dashboard'),
                            'url' => ['/auto/report/index'],
                            'active' => in_array(Yii::$app->controller->getRoute(), [
                                'auto/report/index',
                            ])
                        ],
                    ]
                ];

                //LOGISTIC
                $menuLinks['logistic'] = [
                    'label' => '<p>' . Yii::t('app', 'LOGISTIC') . '<i class="right fas fa-angle-left"></i></p>',
                    'url' => null,
                    'linkOptions' => ['class' => 'nav-header cursor-pointer'],
                    'visible' =>
                        Helper::checkRoute('/logistic/meeting-room/index') ||
                        Helper::checkRoute('/logistic/room-reservation/index')
                ];
                // logistic
                $menuLinks['logistic']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-chalkboard-teacher"></i><p>' . Yii::t('app', 'Meeting Rooms') . '</p>',
                    'url' => ['/logistic/meeting-room/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'logistic/meeting-room/index',
                        'logistic/meeting-room/view',
                        'logistic/meeting-room/update',
                        'logistic/meeting-room/create',
                        'logistic/room-reservation/index',
                        'logistic/room-reservation/view',
                        'logistic/room-reservation/update-single',
                        'logistic/room-reservation/update-multiple',
                        'logistic/room-reservation/create'
                    ])
                ];

                //NOTIFICATION
                $menuLinks['notification'] = [
                    'label' => '<p>' . Yii::t('app', 'NOTIFICATION') . '<i class="right fas fa-angle-left"></i></p>',
                    'url' => null,
                    'linkOptions' => ['class' => 'nav-header cursor-pointer'],
                    'visible' =>
                        Helper::checkRoute('/notification/fuel/index')
                ];
                // notification triggers
                $menuLinks['notification']['items'][] = [
                    'label' => '<i class="nav-icon fa fa-solid fa-bullhorn"></i><p>' . Yii::t('app', 'Triggers') . '</p>',
                    'url' => ['/notification/trigger/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'notification/trigger/index',
                        'notification/trigger/view',
                        'notification/trigger/update',
                        'notification/trigger/create',
                        'notification/trigger/delete',
                    ]),
                ];
                // notification communication channels
                $menuLinks['notification']['items'][] = [
                    'label' => '<i class="nav-icon fa fa-solid fa-envelope-open"></i><p>' . Yii::t('app', 'Comm. channels') . '</p>',
                    'url' => ['/notification/communication-channel/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'notification/communication-channel/index',
                        'notification/communication-channel/view',
                        'notification/communication-channel/update',
                        'notification/communication-channel/create',
                        'notification/communication-channel/delete',
                    ]),
                ];
                // notification notification
                $menuLinks['notification']['items'][] = [
                    'label' => '<i class="nav-icon fa fa-solid fa-bell"></i><p>' . Yii::t('app', 'Notification') . '</p>',
                    'url' => ['/notification/notification/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'notification/notification/index',
                        'notification/notification/view',
                        'notification/notification/update',
                        'notification/notification/create',
                        'notification/notification/delete',
                    ]),
                ];

                //GITHUB
                $menuLinks['github'] = [
                    'label' => '<p>' . Yii::t('app', 'GITHUB') . '<i class="right fas fa-angle-left"></i></p>',
                    'url' => null,
                    'linkOptions' => ['class' => 'nav-header cursor-pointer'],
                    'visible' =>
                        Helper::checkRoute('/github/repo/index')
                ];
                // github
                $menuLinks['github']['items'][] = [
                    'label' => '<i class="nav-icon fas fa-bookmark"></i><p>' . Yii::t('app', 'GitHub Repos') . '</p>',
                    'url' => ['/github/repo/index'],
                    'active' => in_array(Yii::$app->controller->getRoute(), [
                        'github/repo/index'
                    ])
                ];

                $item = new AdminLteNav();
                $item->encodeLabels = false;
                $item->activateParents = true;
                $menuLinks = Helper::filter($menuLinks);
                $item->items = $menuLinks;
                $itemsHtml = '';
                /**
                 * se pare ca atunci cand folosim # in url unele meniuri nu sunt vizibile, poate putem implementa
                 * asta pentru a ascunde numele categoriei meniului atunci cand nu avem submeniuri
                 */
                foreach ($menuLinks as $key => $menuLink) {
                    if (empty($menuLink['items']) && (isset($menuLink['visible']) && $menuLink['visible'])) {
                        $itemsHtml .= '<li class="nav-header">' . $menuLink['label'] . '</li>';
                        continue;
                    }
                    try {
                        $itemsHtml .= $item->renderItem($menuLink);
                    } catch (InvalidConfigException $e) {
                        Yii::error($e->getMessage());
                        continue;
                    }
                }
                ?>
                <?php echo $itemsHtml; ?>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
    <div class="sidebar-footer d-flex justify-content-around align-items-center">
        <div class="hide-on-sidebar-mini">Designed & Built by</div>
        <div>
            <a href="//econfaire.ro" class="d-inline-block" target="_blank">Econfaire ID</a>
        </div>
    </div>
    <!-- /.sidebar-custom -->
</aside>