<?php

use yii\db\Migration;

/**
 * Class m220607_123109_create_LocationManager_role_rbac
 */
class m220607_123109_create_LocationManager_role_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $roleObj = $auth->getRole('LocationManager');
        if ($roleObj === null) {
            $roleObj = $auth->createRole('LocationManager');
            $roleObj->description = "Users with this role have access to view locations!";
            $auth->add($roleObj);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220607_123109_create_LocationManager_role_rbac cannot be reverted.\n";

        return false;
    }
}
