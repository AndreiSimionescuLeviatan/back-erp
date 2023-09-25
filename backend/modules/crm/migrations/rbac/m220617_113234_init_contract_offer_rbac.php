<?php

use yii\db\Migration;

/**
 * Class m220617_113234_init_contract_offer_rbac
 * indexContractOffer
 * viewContractOffer
 * updateContractOffer
 * createContractOffer
 * deleteContractOffer
 * activateContractOffer
 */
class m220617_113234_init_contract_offer_rbac extends Migration
{
    static $permissions = [
        'index' => ['permissionName' => 'indexContractOffer', 'route' => '/crm/contract-offer/index'],
        'view' => ['permissionName' => 'viewContractOffer', 'route' => '/crm/contract-offer/view'],
        'update' => ['permissionName' => 'updateContractOffer', 'route' => '/crm/contract-offer/update'],
        'create' => ['permissionName' => 'createContractOffer', 'route' => '/crm/contract-offer/create'],
        'delete' => ['permissionName' => 'deleteContractOffer', 'route' => '/crm/contract-offer/delete'],
        'activate' => ['permissionName' => 'activateContractOffer', 'route' => '/crm/contract-offer/activate'],
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
