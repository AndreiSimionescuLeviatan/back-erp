<?php

use yii\db\Migration;

/**
 * Class m220607_122902_create_LocationAdministrator_role_rbac
 */
class m220607_122902_create_LocationAdministrator_role_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $roleObj = $auth->getRole('LocationAdministrator');
        if ($roleObj === null) {
            $roleObj = $auth->createRole('LocationAdministrator');
            $roleObj->description = "Users with this role have access to almost all locations functionalities!";
            $auth->add($roleObj);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220607_122902_create_LocationAdministrator_role_rbac cannot be reverted.\n";

        return false;
    }
}
