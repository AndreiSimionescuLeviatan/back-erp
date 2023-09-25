<?php

use yii\db\Migration;

/**
 * Class m220708_115439_add_depends_to_company_rbac
 */
class m220708_115439_add_depends_to_company_rbac extends Migration
{
    static $permissions = [
        'updateCompany' => [
            'permissionName' => 'updateCompany',
            'route' => '/crm/company/update',
            'depends' => [
                '/crm/company/generate-code',
                '/crm/company/add-ro',
                '/crm/company/add-ro-cui',
                '/location/state/get-states',
                '/location/city/get-cities',
                '/adm/domain/get-domains',
                '/adm/entity/get-entities',
                '/adm/subdomain/get-subdomains'
            ]
        ],
        'createCompany' => [
            'permissionName' => 'createCompany',
            'route' => '/crm/company/create',
            'depends' => [
                '/crm/company/generate-code',
                '/crm/company/add-ro',
                '/crm/company/add-ro-cui',
                '/location/state/get-states',
                '/location/city/get-cities',
                '/adm/domain/get-domains',
                '/adm/entity/get-entities',
                '/adm/subdomain/get-subdomains'
            ]
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        foreach (self::$permissions as $permission) {
            // add "permissionName" permission
            if ($auth->getPermission($permission['permissionName']) !== null) {
                $permissionObj = $auth->getPermission($permission['permissionName']);
            } else {
                break;
            }

            //if permission has dependencies iterate over to add all of them
            if (array_key_exists('depends', $permission) && is_array($permission['depends'])) {
                foreach ($permission['depends'] as $depend) {
                    $dependantRoute = $auth->createPermission($depend);
                    if (empty($auth->getPermission($depend))) {
                        $auth->add($dependantRoute);
                    }
                    //add dependant route permissions as child to main permission
                    if (!$auth->hasChild($permissionObj, $dependantRoute)) {
                        $auth->addChild($permissionObj, $dependantRoute);
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        foreach (self::$permissions as $permission) {
            $permissionObj = $auth->getPermission($permission['permissionName']);
            foreach ($permission as $key => $depends) {
                if ($key === 'depends') {
                    foreach ($depends as $depend) {
                        $dependantRoute = $auth->getPermission($depend);
                        if ($auth->hasChild($permissionObj, $dependantRoute)) {
                            $auth->removeChild($permissionObj, $dependantRoute);
                        }
                        if (!empty($auth->getPermission($depend))) {
                            $auth->remove($dependantRoute);
                        }
                    }
                }
            }
        }
    }
}
