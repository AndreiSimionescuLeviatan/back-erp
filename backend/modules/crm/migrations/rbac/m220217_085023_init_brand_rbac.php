<?php

use yii\db\Migration;

/**
 * Class m220217_085023_init_brand_rbac
 * indexBrand
 * viewBrand
 * updateBrand
 * createBrand
 * deleteBrandt
 * activateBrand
 */
class m220217_085023_init_brand_rbac extends Migration
{
    static $permissions = [
        'index' => ['permissionName' => 'indexBrand', 'route' => '/crm/brand/index'],
        'view' => ['permissionName' => 'viewBrand', 'route' => '/crm/brand/view'],
        'update' => ['permissionName' => 'updateBrand', 'route' => '/crm/brand/update'],
        'create' => ['permissionName' => 'createBrand', 'route' => '/crm/brand/create'],
        'delete' => ['permissionName' => 'deleteBrand', 'route' => '/crm/brand/delete'],
        'activate' => ['permissionName' => 'activateBrand', 'route' => '/crm/brand/activate'],
    ];

    /**
     * @return false|mixed|void
     * @throws \yii\base\Exception
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $permissionObj = null;
        $prevPermission = null;

        foreach (self::$permissions as $key => $permission) {
            // add "permissionName" permission
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

        /**
         * add all(using inheritance) created permissions to superAdmin role
         */
        if ($permissionObj != null)
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
