<?php

use yii\db\Migration;

/**
 * Class m220606_123734_init_country_rbac
 * indexCountry
 * viewCountry
 * deleteCountry
 * activateCountry
 */
class m220606_123734_init_country_rbac extends Migration
{
    static $permissions = [
        'index' => ['permissionName' => 'indexCountry', 'route' => '/location/country/index'],
        'view' => ['permissionName' => 'viewCountry', 'route' => '/location/country/view'],
        'delete' => ['permissionName' => 'deleteCountry', 'route' => '/location/country/delete'],
        'activate' => ['permissionName' => 'activateCountry', 'route' => '/location/country/activate'],
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
