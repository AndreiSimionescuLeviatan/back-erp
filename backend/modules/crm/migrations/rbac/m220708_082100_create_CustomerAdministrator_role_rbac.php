<?php

use yii\db\Migration;

/**
 * Class m220708_082100_create_CustomerAdministrator_role_rbac
 */
class m220708_082100_create_CustomerAdministrator_role_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $roleObj = $auth->getRole('CustomerAdministrator');
        if ($roleObj === null) {
            $roleObj = $auth->createRole('CustomerAdministrator');
            $roleObj->description = "Users with this role have access to almost all crm functionalities!";
            $auth->add($roleObj);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220708_082100_create_CustomerAdministrator_role_rbac cannot be reverted.\n";

        return false;
    }
}
