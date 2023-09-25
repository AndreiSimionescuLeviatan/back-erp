<?php

use yii\db\Migration;

/**
 * Class m220224_104905_init_brand_model_rbac
 * indexModel
 * viewModel
 * updateModel
 * createModel
 * deleteModel
 * activateModel
 */
class m220224_104905_init_brand_model_rbac extends Migration
{
    static $permissions = [
        'index' => ['permissionName' => 'indexModel', 'route' => '/crm/brand-model/index'],
        'view' => ['permissionName' => 'viewModel', 'route' => '/crm/brand-model/view'],
        'update' => ['permissionName' => 'updateModel', 'route' => '/crm/brand-model/update'],
        'create' => ['permissionName' => 'createModel', 'route' => '/crm/brand-model/create'],
        'delete' => ['permissionName' => 'deleteModel', 'route' => '/crm/brand-model/delete'],
        'activate' => ['permissionName' => 'activateModel', 'route' => '/crm/brand-model/activate'],
    ];

    /**
     * {@inheritdoc}
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