<?php

use yii\db\Migration;

/**
 * Class m220720_090937_add_import_permission_to_company_rbac
 */
class m220720_090937_add_import_permission_to_company_rbac extends Migration
{
    static $permissions = [
        'import' => ['permissionName' => 'importCompany', 'route' => '/crm/company/import'],
    ];

    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $permissionObj = null;
        $prevPermission = null;
        foreach (self::$permissions as $key => $permission) {
            if ($auth->getPermission($permission['permissionName']) !== null) {
                $permissionObj = $auth->getPermission($permission['permissionName']);
                $addChild = false;
            } else {
                // add "permissionName" permission
                $addChild = true;
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
        if ($permissionObj != null && $addChild)
            $auth->addChild($auth->getRole('SuperAdmin'), $permissionObj);
    }
    /**
     * {@inheritdoc}
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
