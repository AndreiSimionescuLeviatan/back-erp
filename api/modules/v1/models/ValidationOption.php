<?php

namespace api\modules\v1\models;

class ValidationOption extends ValidationOptionParent
{
    const VALIDATION_OPTION_HOME_WORK_ID = 17;
    public static $validationOptionWork = [];
    public static $validationOptionAdministrative = [];

    public static function getValidationOption()
    {
        self::$validationOptionWork = [];
        self::$validationOptionAdministrative = [];

        $tableName = self::tableName();
        $sql = "SELECT id, name, validation_type FROM {$tableName} WHERE deleted = 0";
        $rows = self::queryAll($sql);
        foreach ($rows as $row) {
            if ($row['validation_type'] === '1') {
                self::$validationOptionWork[$row['id']] = $row['name'];
            } else if ($row['validation_type'] === '2') {
                self::$validationOptionAdministrative[$row['id']] = $row['name'];
            }
        }
        return self::$validationOptionAdministrative and self::$validationOptionWork;
    }

    public static function getValidationData($validations)
    {
        $dataToSend = [];

        if (count($validations) === 1) {
            $validation = self::findOneByAttributes(['id' => $validations[0]]);
            if ($validation !== null) {
                $dataToSend['type'] = $validation->validation_type;
                $dataToSend['validation_option_id'] = $validations[0];
            }
        } else {
            $values = array_count_values($validations);
            arsort($values);
            $popular = array_slice(array_keys($values), 0);

            if (!empty($popular)) {
                $validation = self::findOneByAttributes(['id' => $popular[0]]);
                if ($validation !== null) {
                    $dataToSend['type'] = $validation->validation_type;
                    $dataToSend['validation_option_id'] = $popular[0];
                }
            }
        }
        return $dataToSend;
    }
}