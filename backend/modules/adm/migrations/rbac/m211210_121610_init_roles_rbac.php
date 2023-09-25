<?php

use yii\db\Migration;

/**
 * Class m211210_121610_init_roles_rbac
 * Creates and assigns initial application roles
 * The assignment part is only functional on some machines do to missing users
 * Created roles are:
 *  - BasicUser
 *  - ChecklistManager
 *  - ProjectManager
 *  - ChecklistReportsManager
 *  - QuantityListCreator
 *  - QuantityListAdministrator
 *  - QuantityListCloser
 *  - FinanceReportsManager
 *  - FinanceReportsAdministrator
 *  - Admin
 *  - SuperAdmin
 */
class m211210_121610_init_roles_rbac extends Migration
{
    static $roles = [
        'BasicUser',
        'ChecklistManager',
        'ProjectManager',
        'EmployeeManager',
        'ChecklistReportsManager',
        'CentralizerAdmin',
        'QuantityListCreator',
        'QuantityListAdministrator',
        'QuantityListCloser',
        'FinanceReportsManager',
        'FinanceReportsAdministrator',
        'Admin',
        'SuperAdmin',
    ];

    /**
     * @return bool|void
     * @throws \yii\base\Exception
     * @throws Exception
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        foreach (self::$roles as $role) {
            $roleObj = $auth->createRole($role);
            if ($roleObj !== null) {
                $auth->add($roleObj);
                if ($role === 'SuperAdmin') {
                    $superAdmin = $roleObj;
                    $fullAppRoute = $auth->createPermission('/*');
                    $auth->add($fullAppRoute);
                    $auth->addChild($superAdmin, $fullAppRoute);
                    $auth->assign($superAdmin, -1);
                }
                if ($role === 'BasicUser') {
                    $basicUser = $roleObj;
                    $auth->assign($basicUser, -4);
                }
            }
        }
    }

    /**
     * @return bool|void
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        foreach (self::$roles as $role) {
            $roleObj = $auth->getRole($role);
            $auth->remove($roleObj);
        }

        $remainingPermissionsList = $auth->getPermissions();
        $remainingPermissions = count($remainingPermissionsList);

        if (
            $remainingPermissions === 2
            && array_key_exists('/*', $remainingPermissionsList)
            && array_key_exists('/adm/user/update-password', $remainingPermissionsList)
        )
            foreach ($remainingPermissionsList as $item)
                $auth->remove($item);
    }
}
