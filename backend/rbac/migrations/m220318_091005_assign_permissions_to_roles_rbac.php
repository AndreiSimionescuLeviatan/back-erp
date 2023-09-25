<?php

use yii\db\Migration;

/**
 * Class m220318_091005_assign_permissions_to_roles_rbac
 */
class m220318_091005_assign_permissions_to_roles_rbac extends Migration
{
    static $roleList = [
        'BasicUser' => [
            'routes' => [
                '/adm/user/update-password'
            ]
        ],
        'ChecklistManager' => [
            'roles' => [
                'BasicUser'
            ],
            'permissions' => [
                'appChecklist',
                'activateDocument',
                'viewActivity',
                'viewPhase',
                'viewSpeciality',
                'viewStage',
                'viewTypology',
            ]
        ],
        'ProjectManager' => [
            'roles' => [
                'BasicUser',
                'ChecklistManager',
            ],
            'permissions' => [
                'viewChecklistReports',
                'createObject',
                'createProject'
            ]
        ],
        'EmployeeManager' => [
            'desc' => 'Utilizatorii care au acest rol pot administra anagajati, departamentele, functiile, birourile si locatiile de munca, de asemenea pot adauga din aplicatia "Angajati" useri pentru platforma ERP.',
            'roles' => [
                'BasicUser',
            ],
            'permissions' => [
                'activateEmployee',
                'activateDepartment',
                'activateManagementPosition',
                'activateOffice',
                'activateWorkLocation',
            ]
        ],
        'ChecklistReportsManager' => [
            'roles' => [
                'BasicUser',
            ],
            'permissions' => [
                'viewChecklistReports',
            ]
        ],
        'CentralizerAdmin' => [
            'roles' => [
                'BasicUser',
            ],
            'permissions' => [
                'indexCentralizer',
            ]
        ],
        'QuantityListCreator' => [
            'desc' => 'Utilizatorii care au acest rol pot adauga liste de cantitati, pot completa formularele din cadrul listelor, dar NU pot administra articole si echipamente',
            'roles' => [
                'BasicUser',
            ],
            'permissions' => [
                'quantityListManager',
                'viewArticle',
                'viewArticleCategory',
                'viewArticleSubcategory',
                'viewEquipment',
                'viewEquipmentCategory',
                'viewEquipmentSubcategory',
                'viewBrand',
                'viewMeasureUnit',
            ]
        ],
        'QuantityListAdministrator' => [
            'desc' => 'Utilizatorii care au acest rol pot administra liste de cantitati, pot completa formularele din cadrul listelor, pot administra articole si echipamente',
            'roles' => [
                'BasicUser',
            ],
            'permissions' => [
                'quantityListManager',
                'createArticle',
                'createArticleCategory',
                'createArticleSubcategory',
                'createEquipment',
                'createEquipmentCategory',
                'createEquipmentSubcategory',
                'createBrand',
                'createMeasureUnit',
            ]
        ],
        'QuantityListCloser' => [
            'desc' => 'Utilizatorii care au acest rol pot administra liste de cantitati, pot completa formularele din cadrul listelor, pot administra articole si echipamente, pot seta pe "inchis" o lista de cantitati',
            'roles' => [
                'BasicUser',
                'QuantityListAdministrator',
            ]
        ],
        'Admin' => [
            'roles' => [
                'BasicUser',
            ],
        ],
        'SuperAdmin' => [],
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
        echo "m220217_122850_add_BasicUser_role_permissions_rbac cannot be reverted.\n";

        return true;
    }
}
