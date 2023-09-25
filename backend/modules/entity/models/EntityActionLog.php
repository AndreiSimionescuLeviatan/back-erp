<?php

namespace backend\modules\entity\models;

class EntityActionLog extends EntityActionLogParent
{

    const LOG_OLD_ID = 'entity_old_id';
    const LOG_AFFECTED_ID = 'affected_id';

    const PREFIX_DB = 'ecf_';

    public static function findByActionId($id)
    {
        $condition = "WHERE `eal`.`entity_action_id`={$id} ORDER BY `ao`.`order_by` ASC";
        $sql = self::getSqlActionLogOperation($condition);
        $operations = EntityActiveRecord::queryAll($sql);
        foreach ($operations as $key => $operation) {
            if (
                !strpos($operation['name_column_source'], 'id')
                && $operation['name_column_source'] !== 'deleted'
            ) {
                continue;
            }

            $oldValue = $operation['old_value'];
            $newValue = $operation['new_value'];
            $tblSource = "{$operation['domain_source']}.{$operation['entity_source']}";
            if (
                $operation['entity_id'] === $operation['entity_change_id']
                && $operation['name_column_source'] === 'deleted'
            ) {
                $tblSource = "{$operation['domain_name']}.{$operation['entity_name']}";
                $oldValue = $operation['entity_old_id'];
                $newValue = $operation['entity_new_id'];
            }

            if (
                empty($tblSource)
                || $tblSource == '.'
            ) {
                continue;
            }

            $tblSource = self::PREFIX_DB . $tblSource;
            $operations[$key]['old_value_code'] = self::getCode($tblSource, "id = {$oldValue}");
            $operations[$key]['new_value_code'] = self::getCode($tblSource, "id = {$newValue}");
        }

        return $operations;
    }

    public static function getCode($table, $where = '', $column = 'code')
    {
        $sql = "SELECT * FROM {$table} WHERE {$where}";
        $element = EntityActiveRecord::queryOne($sql);
        if (is_array($element)) {
            if (isset($element[$column])) {
                return $element[$column];
            }
            if (isset($element['name'])) {
                return $element['name'];
            }
        }
        return '';
    }


    public static function getSqlActionLogOperation($where = '')
    {
        $tblEntityAction = EntityAction::tableName();
        $tblEntityActionLog = EntityActionLog::tableName();
        $tblEntityActionOperation = EntityActionOperation::tableName();
        $tblEntityActionCategory = EntityActionCategory::tableName();
        $tblEntity = Entity::tableName();
        $tblDomain = Domain::tableName();

        return "SELECT `eal`.*, `ea`.`entity_new_id`, `ao`.`description`, `e`.`name` as `entity_name`, `e`.`description` as `entity_description`,
                       `d`.`name` AS `domain_name`, `d`.`description` AS `domain_description`,`ds`.`name` AS `domain_source`,
                       `es`.`name` as `entity_source`,`ao`.`name_column_source`, NULL AS `old_value_code`, NULL AS `new_value_code`,
                       `eac`.`entity_id`, `ao`.`entity_change_id`, `ao`.`entity_source_id`
                FROM {$tblEntityAction} `ea`
                INNER JOIN {$tblEntityActionLog} `eal` ON `ea`.`id`=`eal`.`entity_action_id`    
                INNER JOIN {$tblEntityActionOperation} `ao` ON `ao`.`id`=`eal`.`entity_operation_id`
                INNER JOIN {$tblEntityActionCategory} AS `eac` ON eac.`id`=`ao`.`action_category_id`
                INNER JOIN {$tblEntity} `e` ON `e`.`id`=`ao`.`entity_change_id`
                INNER JOIN {$tblDomain} `d` ON `d`.`id`=`e`.`domain_id`
                LEFT JOIN {$tblEntity} `es` ON `es`.`id`=`ao`.`entity_source_id`
                LEFT JOIN {$tblDomain} `ds` ON `ds`.`id`=`es`.`domain_id`
                {$where}";
    }

    public static function getEntities($operationId, $key = self::LOG_OLD_ID)
    {
        $items = [];
        $condition = "WHERE `eal`.`entity_operation_id`= {$operationId}";
        $sql = self::getSqlActionLogOperation($condition);
        $operations = EntityActiveRecord::queryAll($sql);

        if (count($operations) == 0) {
            return $items;
        }

        foreach ($operations as $operation) {
            $nameColumn = '';
            if ((int)$operation['entity_id'] == Entity::BUILD_ARTICLE_ID) {
                $nameColumn = 'name';
            }

            $tblSource = self::PREFIX_DB . "{$operation['domain_name']}.{$operation['entity_name']}";
            if (isset($operation['entity_source'])) {
                $tblSource = self::PREFIX_DB . "{$operation['domain_source']}.{$operation['entity_source']}";
            }
            $columnFrom = 'entity_new_id';
            if ($key == self::LOG_AFFECTED_ID) {
                $columnFrom = 'entity_old_id';
            }
            $items[$operation[$key]] = [
                'id' => $operation[$columnFrom],
                'code' => self::getCode($tblSource, "id = {$operation[$columnFrom]}"),
                'added' => $operation['added'],
            ];

            if (!empty($nameColumn)) {
                $items[$operation[$key]] += ['name' => self::getCode($tblSource, "id = {$operation[$columnFrom]}", $nameColumn)];
            }
        }

        return $items;
    }

    public static function getMessageFindReplace($message, $entityFindReplace)
    {
        $code = $entityFindReplace['code'] ?? '';
        $optional = $entityFindReplace['name'] ?? '';

        return str_replace(array("\r\n", "\n", "\r", "\""), ' ', "{$message} <b>{$code}</b> <br>{$optional}");
    }
}
