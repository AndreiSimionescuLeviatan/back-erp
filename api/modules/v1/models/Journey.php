<?php

namespace api\modules\v1\models;

use api\models\Project;
use common\components\DateTimeHelper;
use Yii;

class Journey extends \api\models\Journey
{
    const STATUS_FOR_DELETED = 2;
    const STATUS_FOR_INVALID = 0;
    const STATUS_FOR_VALID = 1;
    const SORT_DESC = 3;
    const SORT_ASC = 4;
    const DELETED_YES = 1;
    const DELETED_NO = 0;

    /**
     * @param $projectID
     * @param $type
     * @return mixed|string
     */
    public static function getScope($projectID = null, $type = null, $optionId)
    {
        if (!empty($projectID)) {
            return self::getScopeByProjectID($projectID);
        }
        if (!empty($type)) {
            return self::getScopeByType($type, $optionId);
        }
        return Yii::t('app', 'Interest is not set');
    }

    /**
     * @param $id
     * @return mixed|string
     */
    public static function getScopeByProjectID($id)
    {
        if (empty(Project::$namesAuto)) {
            Project::setNamesAuto();
        }

        return Project::$namesAuto[$id] ?? 'Unknown project';
    }

    /**
     * @param $type
     * @return string
     */
    public static function getScopeByType($type, $optionId)
    {
        if (empty(ValidationOption::$validationOptionWork) || empty(ValidationOption::$validationOptionAdministrative)) {
            ValidationOption::getValidationOption();
        }
        if ($type == 1) {
            if ($optionId != null) {
                $scope = ValidationOption::$validationOptionWork[$optionId];
                return !empty($scope) ? Yii::t('auto', 'Service') . ", {$scope}" : Yii::t('auto', 'Service');
            }
            return Yii::t('auto', 'Service');
        }
        if ($type == 2) {
            if ($optionId != null) {
                $scope = ValidationOption::$validationOptionAdministrative[$optionId];
                return !empty($scope) ? Yii::t('auto', 'Administrative') . ", {$scope}" : Yii::t('auto', 'Administrative');
            }
            return Yii::t('auto', 'Administrative');
        }
        return Yii::t('auto', 'Unknown type');
    }

    public static function countByAttributes($attributes)
    {
        $className = get_called_class();
        $model = new $className();
        $where = '';
        $bind = [];
        foreach ($attributes as $attribute => $value) {
            if (!$model->hasAttribute($attribute)) {
                continue;
            }
            if (!empty($where)) {
                $where .= ' AND ';
            }
            $where .= "{$attribute} = :{$attribute}";
            $bind[":{$attribute}"] = $value;
        }

        return self::find()->where($where, $bind)->count();
    }

    public static function buildJourneyData($journeyModel, $skipValues)
    {
        $journey = [];
        foreach ($journeyModel as $key => $value) {
            if (in_array($key, $skipValues) || is_array($value))
                continue;
            $journey[$key] = $value;
        }
        $journey['started'] = Journey::formatDate($journey['started'], true);
        $journey['stopped'] = Journey::formatDate($journey['stopped'], true);
        $journey['interest'] = Journey::getScope($journey['project_id'], $journey['type'], $journey['validation_option_id']);
        $journey['duration'] = DateTimeHelper::getDuration($journeyModel['time']);

        foreach ($journeyModel['car'] as $key => $carData) {
            if (in_array($key, $skipValues) || is_array($carData))
                continue;
            $journey['car'][$key] = $carData;
        }

        foreach ($journeyModel['car']['brand'] as $key => $carBrandData) {
            if (in_array($key, $skipValues))
                continue;
            $journey['car']['brand'][$key] = $carBrandData;
        }

        foreach ($journeyModel['car']['brandModel'] as $key => $carModelData) {
            if (in_array($key, $skipValues))
                continue;
            $journey['car']['brandModel'][$key] = $carModelData;
        }

        foreach ($journeyModel['startHotspot'] as $key => $journeyStartHotspot) {
            if (in_array($key, $skipValues))
                continue;
            $journey['startHotspot'][$key] = $journeyStartHotspot;
        }
        $journey['startHotspot']['new_name'] = LocationName::getLocationName($journey['startHotspot']['id']);

        foreach ($journeyModel['stopHotspot'] as $key => $journeyStopHotspot) {
            if (in_array($key, $skipValues))
                continue;
            $journey['stopHotspot'][$key] = $journeyStopHotspot;
        }
        $journey['stopHotspot']['new_name'] = LocationName::getLocationName($journey['stopHotspot']['id']);
        $journey['start_hotspot'] = $journey['startHotspot']['name'];
        $journey['stop_hotspot'] = $journey['stopHotspot']['name'];

        return $journey;
    }
}