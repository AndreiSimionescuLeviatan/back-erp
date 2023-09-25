<?php

namespace backend\modules\entity\models;

use backend\modules\adm\models\User;
use Yii;

class EntityAction extends EntityActionParent
{
    public static $filtersOptions = [
        'added_by' => [],
        'updated_by' => []
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_ENTITY . '.entity_action';
    }

    public static function validatePostReplace($post)
    {
        if (empty($post['entity_id'])) {
            return false;
        }

        if (empty($post['new_entity'])) {
            return false;
        }

        if (empty($post['old_entities']) || count($post['old_entities']) == 0) {
            return false;
        }

        if (!isset($post['selection']) || count($post['selection']) == 0) {
            return false;
        }

        return true;
    }

    public static function setFilterOptions($type = '')
    {
        if (empty($type)) {
            return;
        }

        self::$filtersOptions[$type] = [];
        if (empty(User::$users)) {
            User::setUsers(true);
        }

        $models = self::find()->distinct($type)->all();
        foreach ($models as $model) {
            if (empty(User::$users[$model->$type])) {
                continue;
            }
            self::$filtersOptions[$type][$model->$type] = User::$users[$model->$type];
        }
    }

    public static function getSqlAction($where = '')
    {
        $tblEntityAction = EntityAction::tableName();
        $tblEntity = Entity::tableName();
        $tblDomain = Domain::tableName();
        $tblUser = User::tableName();

        return "SELECT `a`.*, `e`.`name` as `entity_name`, `e`.`description` as `entity_description`, `d`.`name` AS `domain_name`, 
                       `d`.`description` AS `domain_description`, `a`.`added`, CONCAT(IFNULL(`u`.`first_name`, ''), ' ', 
                        IFNULL(`u`.`last_name`,'')) AS `added_by`,`e`.`domain_id`
                FROM {$tblEntityAction} `a`
                INNER JOIN {$tblEntity} `e` ON `e`.`id`=`a`.`entity_id`
                INNER JOIN {$tblDomain} `d` ON `d`.`id`=`e`.`domain_id`
                INNER JOIN {$tblUser} `u` ON `u`.`id`=`a`.`added_by`
                {$where}";
    }

    public static function setDomainNames($conditions = [['self.deleted' => 0]])
    {
        $tblEntity = Entity::tableName();
        $relatedTables[] = [
            'name' => $tblEntity,
            'column' => 'domain_id',
            'on_column' => "self.id",
        ];

        $relatedTables[] = [
            'name' => self::tableName(),
            'column' => 'entity_id',
            'on_column' => "{$tblEntity}.id",
        ];

        Domain::$names = Domain::getList(
            $conditions,
            ['key' => 'id', 'value' => 'description', 'group_by' => 'self.id'],
            $relatedTables
        );
    }

    public static function setEntityNames($conditions = [['self.deleted' => 0]])
    {
        $relatedTables[] = [
            'name' => self::tableName(),
            'column' => 'entity_id',
            'on_column' => "self.id",
        ];

        Entity::$names = Entity::getList(
            $conditions,
            ['key' => 'id', 'value' => 'description', 'group_by' => 'self.id'],
            $relatedTables
        );
    }
}
