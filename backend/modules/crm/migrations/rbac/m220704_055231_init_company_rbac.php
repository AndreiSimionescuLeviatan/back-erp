<?php

use yii\db\Migration;

/**
 * Class m220704_055231_init_company_rbac
 */
class m220704_055231_init_company_rbac extends Migration
{
    static $permissions = [
        'index' => ['permissionName' => 'indexCompany', 'route' => '/crm/company/index'],
        'view' => ['permissionName' => 'viewCompany', 'route' => '/crm/company/view'],
        'update' => ['permissionName' => 'updateCompany', 'route' => '/crm/company/update'],
        'create' => ['permissionName' => 'createCompany', 'route' => '/crm/company/create'],
        'delete' => ['permissionName' => 'deleteCompany', 'route' => '/crm/company/delete'],
        'activate' => ['permissionName' => 'activateCompany', 'route' => '/crm/company/activate'],
    ];

    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $permissionObj = null;
        $prevPermission = null;

        foreach (self::$permissions as $key => $permission) {
            // add "permissionName" permission
            if ($auth->getPermission($permission['permissionName']) !== null) {
                $permissionObj = $auth->getPermission($permission['permissionName']);
                $assignToSuperAdmin = false;
            } else {
                $assignToSuperAdmin = true;
                $permissionObj = $auth->createPermission($permission['permissionName']);
                $permissionObj->description = "User can view {$key} page.";
                // create route permission, this should be in the form '/module/controller/action'
                $permissionObjRoute = $auth->createPermission(self::$permissions[$key]['route']);
                $auth->add($permissionObj);
                $auth->add($permissionObjRoute);
                //add previous created permission as child to current one except index
                if ($prevPermission != null)
                    $auth->addChild($permissionObj, $prevPermission);
                //add route permissions as child to main permission
                $auth->addChild($permissionObj, $permissionObjRoute);
                $prevPermission = $permissionObj;
            }
        }

        /**
         * add all(using inheritance) created permissions to superAdmin role
         */
        if ($permissionObj != null && $assignToSuperAdmin)
            $auth->addChild($auth->getRole('SuperAdmin'), $permissionObj);
    }

    /**
     * @return false|mixed|void
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        foreach (self::$permissions as $permission) {
            foreach ($permission as $value) {
                $permObj = $auth->getPermission($value);
                $auth->remove($permObj);
            }
        }
    }
}
