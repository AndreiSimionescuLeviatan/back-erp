<?php

namespace console\models;

class GithubCollaborator extends \backend\modules\github\models\Collaborator
{

    public static $cache = [];
    public static function getCollaboratorIDByUsername($username = '')
    {
        if (empty($username)) {
            return 0;
        }

        $collaborator = null;
        if (!empty(self::$cache[$username])) {
            $collaborator = self::$cache[$username];
        } else {
            try {
                $collaborator = self::getByAttributes(
                    [
                        'username' => $username
                    ],
                    [
                        'username' => $username
                    ]
                );
            } catch (\Exception $exc) {
                return 0;
            }

            self::$cache[$username] = $collaborator;
        }

        if ($collaborator === null) {
            return 0;
        }
        return $collaborator->id;
    }
}