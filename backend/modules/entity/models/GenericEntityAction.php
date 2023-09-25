<?php

namespace backend\modules\entity\models;

use Yii;
use Exception;

class GenericEntityAction
{
    private $aliasDb = 'ecf_';
    private $entityId;
    private $tableName;
    private $primaryKeyEntity;
    private $newEntityID;
    private $oldEntityIDs = [];
    private $categoryIDs = "-1";
    private $sqlActionsExe = '';
    private $newEntity;

    public function setNewEntityID($entityID)
    {
        $this->newEntityID = $entityID;
    }

    public function setOldEntityIDs(array $oldEntityIDs)
    {
        $this->oldEntityIDs = $oldEntityIDs;
    }

    public function setCategoryIDs($categoryIds)
    {
        $this->categoryIDs = $categoryIds;
    }

    private function validateElementsID(): bool
    {
        return !empty($this->entityId) && !empty($this->newEntityID) && count($this->oldEntityIDs) > 0;
    }


    /**
     * @throws Exception
     */
    public function setEntity($nameDomain, $nameEntity)
    {
        $entity = Entity::getEntityByDomainAndEntityName($nameDomain, $nameEntity);
        $this->validateEntity($entity);
        $this->setEntityValue($entity);
    }

    /**
     * @throws Exception
     */
    public function setEntityByID($entityID)
    {
        $entity = Entity::getEntityByID($entityID);
        $this->validateEntity($entity);
        $this->setEntityValue($entity);
    }


    /**
     * @throws Exception
     */
    private function validateEntity($entity)
    {
        if ($entity === null) {
            throw new Exception(Yii::t('entity', 'The requested entity does not exist') . '!');
        }
    }

    /**
     * @throws Exception
     */
    private function setEntityValue($entity)
    {
        $this->entityId = $entity['id'];
        $this->tableName = "`{$this->aliasDb}{$entity['name_domain']}`.`{$entity['name']}`";

        $sql = "SHOW KEYS FROM {$this->tableName} WHERE Key_name = 'PRIMARY'";
        $row = EntityActiveRecord::queryOne($sql);
        if ($row === null) {
            throw new Exception(Yii::t('entity', 'The entity does not have primary_key') . '!');
        }
        $this->primaryKeyEntity = $row['Column_name'];
    }

    /**
     * @throws Exception
     */
    public function executeEntityAction()
    {
        if (!$this->validateElementsID()) {
            throw new Exception(Yii::t('entity', 'New entity or old entities are not set') . '!');
        }

        $this->setEntityActions();
        if (empty(trim($this->sqlActionsExe))) {
            throw new Exception(Yii::t('entity', 'After validating the entity does not have operations') . '!');
        }

        $transaction = EntityActiveRecord::getDb()->beginTransaction();
        try {
            $attribute = [
                'entity_id' => $this->entityId,
                'entity_new_id' => $this->newEntity[$this->primaryKeyEntity],
                'added_by' => Yii::$app->user->id
            ];
            $action = EntityAction::createByAttributes($attribute);
            if ($action === null) {
                throw new Exception(Yii::t('entity', 'Failed to update') . '!');
            }
            $commandExe = str_replace('{idAction}', $action->id, trim($this->sqlActionsExe));
            EntityActiveRecord::execute($commandExe);
            $transaction->commit();
        } catch (Exception|\Throwable $e) {
            $transaction->rollBack();
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    private function getValueEntity($id)
    {
        $sql = "SELECT * FROM {$this->tableName} AS `tbl` WHERE  `tbl`.`{$this->primaryKeyEntity}`={$id}";
        return EntityActiveRecord::queryOne($sql);
    }


    /**
     * @throws Exception
     */
    private function setEntityActions()
    {
        Entity::setEntities();
        $this->sqlActionsExe = '';

        $condition = "WHERE `eaop`.`deleted` = 0 AND `eac`.`entity_id`= {$this->entityId} 
                            AND `eaop`.`action_category_id` IN ($this->categoryIDs)
                      ORDER BY `eaop`.`order_by` ASC";
        $sql = EntityActionOperation::getSqlEntityActionOperation($condition);
        $operations = EntityActiveRecord::queryAll($sql);
        if (count($operations) == 0) {
            throw new Exception(Yii::t('entity', 'The entity does not have operations') . '!');
        }

        $this->newEntity = $this->getValueEntity($this->newEntityID);
        foreach ($this->oldEntityIDs as $oldEntityID) {
            $oldEntity = $this->getValueEntity($oldEntityID);
            if ($oldEntity === null || $this->newEntityID == $oldEntityID) {
                continue;
            }

            foreach ($operations as $operation) {
                $type = strtoupper(trim($operation['action_sql']));
                $operation['action_sql'] = $type;
                switch ($type) {
                    case 'UPDATE':
                        $this->createUpdateCommand($operation, $oldEntity);
                        break;
                    case 'DELETE':
                        $this->createDeleteCommand($operation, $oldEntity);
                        break;
                    default:
                        switch ((int)$operation['id']) {
                            case EntityActionOperation::BUILD_PACKAGE_REPLACE_PROVISION_NEW_ELEMENT:
                                $this->replaceBuildPackageInProcurementOfferPackage($operation, $oldEntity);
                                break;
                            default:
                                break;
                        }
                        break;
                }
            }
        }
    }

    private function createUpdateCommand($operation, $oldEntity)
    {
        if (!self::isValidColumnsUpdate($operation)) {
            return;
        }
        $oldValue = '';
        $columnSource = self::getColumnSource($operation);
        if (
            self::isSetColumnSource($operation)
            && isset($this->newEntity[$columnSource])
            && !self::isSetDefaultValue($operation)
        ) {
            $valueSet = self::formatValueSQL("{$this->newEntity[$columnSource]}");
            $oldValue = self::formatValueSQL("{$oldEntity[$columnSource]}");
        }

        if (self::isSetDefaultValue($operation)) {
            $valueSet = self::formatValueSQL(self::getDefaultValue($operation));
            if (self::isSetColumnSource($operation) && isset($oldEntity[$columnSource])) {
                $oldValue = self::formatValueSQL("{$oldEntity[$columnSource]}");
            }
        }

        if (empty($valueSet)) {
            return;
        }

        $tableChange = $this->getTableNameChange($operation);
        $whereSQL = self::getWhereSQL($operation, $oldEntity);
        $columnChange = self::getColumnChange($operation);

        if (!self::existsRowInTable($tableChange, $whereSQL . " AND {$columnChange}<>{$valueSet}")) {
            return;
        }

        $setSQL = "SET {$columnChange}={$valueSet}";
        $commandUpdate = "UPDATE {$tableChange} {$setSQL} WHERE {$whereSQL};";

        $metadata = [
            'tableChange' => $tableChange,
            'whereSQL' => $whereSQL,
            'actionID' => $operation['id'],
            'oldEntityID' => $oldEntity[$this->primaryKeyEntity],
            'oldValue' => $oldValue,
            'newValue' => $valueSet,
        ];
        $this->addActionLogFromSelect($metadata);
        $this->addActionExecute($commandUpdate);
    }

    public static function formatValueSQL($value)
    {
        if (!is_numeric($value)) {
            $value = "'{$value}'";
        }
        return $value;
    }

    private function createDeleteCommand($operation, $oldEntity)
    {
        if (!self::isSetConditionSql($operation)) {
            return;
        }

        $tableChange = $this->getTableNameChange($operation);
        $whereSQL = self::getWhereSQL($operation, $oldEntity);

        if (!self::existsRowInTable($tableChange, $whereSQL)) {
            return;
        }

        $commandDelete = "DELETE FROM  {$tableChange} WHERE {$whereSQL};";

        $this->addActionExecute($commandDelete);

        $metadata = [
            'actionID' => $operation['id'],
            'oldEntityID' => $oldEntity[$this->primaryKeyEntity],
            'oldValue' => $whereSQL,
        ];
        $this->addActionLog($metadata);
    }

    private function replaceBuildPackageInProcurementOfferPackage($operation, $oldEntity)
    {
        if (
            !self::isValidColumnsUpdate($operation)
            || !isset(Entity::$entities[Entity::PROCUREMENT_OFFER_PACKAGE_CONTENT_ID])
            || !isset(Entity::$entities[Entity::PROCUREMENT_OFFER_ID])
            || !isset(Entity::$entities[Entity::PROCUREMENT_ITEM_MERGE_ID])
        ) {
            return;
        }

        $oldValue = '';
        $columnSource = self::getColumnSource($operation);
        if (
            self::isSetColumnSource($operation)
            && isset($this->newEntity[$columnSource])
        ) {
            $valueSet = self::formatValueSQL("{$this->newEntity[$columnSource]}");
            $oldValue = self::formatValueSQL("{$oldEntity[$columnSource]}");
        }

        if (empty($valueSet)) {
            return;
        }

        $entitiesReplace = [Entity::$entities[Entity::PROCUREMENT_OFFER_PACKAGE_CONTENT_ID], Entity::$entities[Entity::PROCUREMENT_ITEM_MERGE_ID]];
        $tableChange = $this->getTableNameChange($operation);
        $whereSQL = self::getWhereSQL($operation, $oldEntity);
        $columnChange = self::getColumnChange($operation);

        $sqlOfferPackageOld = self::getSQLProcurementOfferPackage($tableChange, $whereSQL);
        $oldOfferPackages = EntityActiveRecord::queryAll($sqlOfferPackageOld);
        foreach ($oldOfferPackages as $oldOfferPackage) {
            $metadata = [
                'actionID' => $operation['id'],
                'oldEntityID' => $oldEntity[$this->primaryKeyEntity],
                'oldValue' => $oldValue,
                'newValue' => $valueSet,
            ];

            $condition = "{$columnChange} = {$valueSet} AND `project_id` = {$oldOfferPackage['project_id']}
                           AND `object_id` = {$oldOfferPackage['object_id']} AND `type` = {$oldOfferPackage['type']}";
            $sqlOfferPackageNew = self::getSQLProcurementOfferPackage($tableChange, $condition);
            $newOfferPackage = EntityActiveRecord::queryOne($sqlOfferPackageNew);
            if ($newOfferPackage) {
                foreach ($entitiesReplace as $entityReplace) {
                    $commandUpdate = "UPDATE {$entityReplace} SET `offer_package_id` = {$newOfferPackage['id']} WHERE `offer_package_id` = {$oldOfferPackage['id']};";
                    $this->addActionExecute($commandUpdate);
                }

                $commandDelete = "DELETE FROM {$tableChange} WHERE `id` = {$oldOfferPackage['id']};";
                $this->addActionExecute($commandDelete);

                $metadata += ['affectedId' => $newOfferPackage['id']];
            } else {
                $commandUpdate = "UPDATE {$tableChange} SET {$columnChange}={$valueSet} WHERE `id` = {$oldOfferPackage['id']};";
                $this->addActionExecute($commandUpdate);

                $metadata += ['affectedId' => $oldOfferPackage['id']];
            }

            $this->addActionLog($metadata);
        }
    }

    private static function getSQLProcurementOfferPackage($tableChange, $whereSQL)
    {
        $tblProcurementOffer = Entity::$entities[Entity::PROCUREMENT_OFFER_ID];

        return "SELECT op.id, `op`.`package_id`, `op`.`offer_id`,`o`.`project_id`,`o`.`object_id`, `o`.`type`
                FROM {$tableChange} op
                INNER JOIN {$tblProcurementOffer} o ON o.`id`=op.`offer_id`
                WHERE {$whereSQL}";
    }

    public static function existsRowInTable($tableName, $condition)
    {
        $sql = "SELECT COUNT(*) AS `count` FROM {$tableName} WHERE {$condition}";
        return EntityActiveRecord::queryScalar($sql) > 0;
    }

    private function getWhereSQL($action, $oldEntity)
    {
        $condition = "{$action['condition_sql']}";
        $condition = str_replace("{entity_new_id}", "{$this->newEntity[$this->primaryKeyEntity]}", $condition);
        $condition = str_replace("{entity_old_id}", "{$oldEntity[$this->primaryKeyEntity]}", $condition);

        return $condition;
    }

    private function addActionExecute($command)
    {
        if (empty($command)) {
            exit;
        }
        $this->sqlActionsExe .= "\n" . $command;
    }

    private function addActionLog($metadata)
    {
        $actionID = $metadata['actionID'] ?? 0;
        $oldEntityID = $metadata['oldEntityID'] ?? 0;
        $oldValue = $metadata['oldValue'] ?? '';
        $newValue = $metadata['newValue'] ?? '';
        $affectedId = $metadata['affectedId'] ?? 'NULL';

        $userId = Yii::$app->user->id;
        $tblActionLog = EntityActionLog::tableName();

        $commandInsert = "INSERT INTO {$tblActionLog} (`entity_action_id`, `entity_operation_id`, `entity_old_id`, `affected_id`, `old_value`, `new_value`, `added`, `added_by`)" . "\n \t" .
            "VALUES ({idAction}, {$actionID}, {$oldEntityID}, {$affectedId}, '{$oldValue}', '{$newValue}', NOW(), {$userId});";
        $this->addActionExecute($commandInsert);
    }

    private function addActionLogFromSelect($metadata)
    {
        $tableChange = $metadata['tableChange'] ?? '';
        $whereSQL = $metadata['whereSQL'] ?? ' 1=0 ';
        $actionID = $metadata['actionID'] ?? 0;
        $oldEntityID = $metadata['oldEntityID'] ?? 0;
        $oldValue = $metadata['oldValue'] ?? '';
        $newValue = $metadata['newValue'] ?? '';

        $tblActionLog = EntityActionLog::tableName();
        $userId = Yii::$app->user->id;

        $commandInsert = "INSERT INTO {$tblActionLog}";
        $commandInsert .= "(`entity_action_id`, `entity_operation_id`, `entity_old_id`, `affected_id`, `old_value`, `new_value`, `added`, `added_by`)";
        $commandInsert .= "\n \t";
        $commandInsert .= "SELECT {idAction}, {$actionID}, {$oldEntityID}, `id`, {$oldValue}, {$newValue}, NOW(), {$userId} ";
        $commandInsert .= "FROM {$tableChange} WHERE {$whereSQL};";

        $this->addActionExecute($commandInsert);
    }

    private function getTableNameChange($action)
    {
        return "`{$this->aliasDb}{$action['domain_change']}`.`{$action['entity_change']}`";
    }

    private static function getColumnChange($action)
    {
        return "`{$action['name_column_change']}`";
    }

    private static function getColumnSource($action)
    {
        return $action['name_column_source'];
    }

    private static function getDefaultValue($action)
    {
        return trim($action['default_value']);
    }

    private static function isValidColumnsUpdate($action)
    {
        return self::isSetColumnChange($action)
            && (self::isSetColumnSource($action) || self::isSetDefaultValue($action))
            && self::isSetConditionSql($action);
    }

    private static function isSetColumnChange($action)
    {
        return !empty($action['name_column_change']);
    }

    private static function isSetColumnSource($action)
    {
        return !empty($action['name_column_source']);
    }

    private static function isSetDefaultValue($action)
    {
        return !empty(trim($action['default_value']));
    }

    private static function isSetConditionSql($action)
    {
        return !empty(trim($action['condition_sql']));
    }

    public static function getAdditionalConditionsByEntityId($entityId, $params)
    {
        if (empty($params)) {
            return '';
        }

        switch ((int)$entityId) {
            case Entity::BUILD_ARTICLE_ID:
                if (empty($params['speciality_id'])) {
                    break;
                }
                return " AND `speciality_id` = {$params['speciality_id']}";
            case Entity::BUILD_PACKAGE_ID:
                if (
                    empty($params['speciality_id'])
                    || !isset(Entity::$entities[Entity::BUILD_PACKAGE_SPECIALITY_ID])
                ) {
                    break;
                }
                $tblPackageSpeciality = Entity::$entities[Entity::BUILD_PACKAGE_SPECIALITY_ID];
                return " AND `id` IN (SELECT `package_id` FROM {$tblPackageSpeciality} WHERE `speciality_id`= {$params['speciality_id']})";
        }

        return '';
    }

}