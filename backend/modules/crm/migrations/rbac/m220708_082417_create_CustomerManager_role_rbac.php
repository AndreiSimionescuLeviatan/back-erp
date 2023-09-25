<?php

use yii\db\Migration;

/**
 * Class m220708_082417_create_CustomerManager_role_rbac
 */
class m220708_082417_create_CustomerManager_role_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $roleObj = $auth->getRole('CustomerManager');
        if ($roleObj === null) {
            $roleObj = $auth->createRole('CustomerManager');
            $roleObj->description = "Users with this role have access to almost all crm functionalities!";
            $auth->add($roleObj);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220708_082417_create_CustomerManager_role_rbac cannot be reverted.\n";

        return false;
    }
}
