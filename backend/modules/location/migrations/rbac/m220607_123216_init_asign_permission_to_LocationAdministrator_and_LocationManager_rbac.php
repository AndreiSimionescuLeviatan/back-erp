<?php

use yii\db\Migration;

/**
 * Class m220607_123216_init_asign_permission_to_LocationAdministrator_and_LocationManager_rbac
 */
class m220607_123216_init_asign_permission_to_LocationAdministrator_and_LocationManager_rbac extends Migration
{
    static $roleList = [
        'LocationAdministrator' => [
            'desc' => 'Utilizatorul poate vizualiza, sterge si activa o tara, un judet sau un oras',
            'permissions' => [
                'activateCountry',
                'activateCounty',
                'activateCity'
            ]
        ],
        'LocationManager' => [
            'desc' => 'Utilizatorul poate vizualiza tarile, judetele si orasele',
            'permissions' => [
                'indexCountry',
                'indexCounty',
                'indexCity'
            ]
        ],
    ];

    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     * @throws Exception
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        foreach (self::$roleList as $key => $role) {
            if (empty($role))
                continue;

            $roleObj = $auth->getRole($key);
            if ($roleObj === null)
                continue;

            if (array_key_exists('desc', $role)) {
                $roleObj->description = $role['desc'];
                $auth->update($key, $roleObj);
            }

            //if role has roles iterate over to add all of them as child
            if (array_key_exists('roles', $role) && is_array($role['roles'])) {
                foreach ($role['roles'] as $_role) {
                    $childRoleObj = $auth->getRole($_role);
                    if ($childRoleObj === null)
                        continue;
                    $auth->addChild($roleObj, $childRoleObj);
                }
            }
            //if role has permissions iterate over to add all of them as child
            if (array_key_exists('permissions', $role) && is_array($role['permissions'])) {
                foreach ($role['permissions'] as $permission) {
                    $childPermissionObj = $auth->getPermission($permission);
                    if ($childPermissionObj === null)
                        continue;
                    $auth->addChild($roleObj, $childPermissionObj);
                }
            }
            //if role has permissions iterate over to add all of them as child
            if (array_key_exists('routes', $role) && is_array($role['routes'])) {
                foreach ($role['routes'] as $route) {
                    //check if route already exists
                    $routeObj = $auth->getPermission($route);
                    if ($routeObj === null) {//if not exists create new route
                        // create route permission, this should be in the form '/module/controller/action'
                        $routeObj = $auth->createPermission($route);
                        $auth->add($routeObj);
                    }
                    $auth->addChild($roleObj, $routeObj);
                }

            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220607_123216_init_asign_permission_to_LocationAdministrator_and_LocationManager_rbac cannot be reverted.\n";

        return false;
    }
}
