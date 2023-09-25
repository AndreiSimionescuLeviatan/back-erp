<?php

use yii\db\Migration;

/**
 * Class m221020_093234_ecf_entity_manageFindReplace_permission
 */
class m221020_093234_ecf_entity_manageFindReplace_permission extends Migration
{

    static $permissions = [
        'manageFindReplace' => [
            'permissionName' => 'manageFindReplace',
            'route' => '/entity/entity-action-log/index',
            'depends' => [
                '/entity/generic-entity-action/index',
                '/entity/generic-entity-action/get-entities',
                '/entity/generic-entity-action/get-entities-value-replace',
            ]
        ],
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

            //if permission has dependencies iterate over to add all of them
            if (array_key_exists('depends', $permission) && is_array($permission['depends'])) {
                foreach ($permission['depends'] as $depend) {
                    $dependantRoute = $auth->createPermission($depend);
                    $auth->add($dependantRoute);
                    //add dependant route permissions as child to main permission
                    $auth->addChild($permissionObj, $dependantRoute);
                }
            }
        }

        /**
         * add all(using inheritance) created permissions to superAdmin role
         */
        if ($permissionObj != null) {
            $auth->addChild($auth->getRole('SuperAdmin'), $permissionObj);
        }
    }

    /**
     * @return false|mixed|void
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        foreach (self::$permissions as $permission) {
            foreach ($permission as $value) {
                if (is_array($value)) {//if permission has dependencies iterate over to remove all of them
                    foreach ($value as $item) {
                        $_permObj = $auth->getPermission($item);
                        $auth->remove($_permObj);
                    }
                } else {
                    $permObj = $auth->getPermission($value);
                    $auth->remove($permObj);
                }
            }
        }
    }
}
