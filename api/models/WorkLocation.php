<?php

namespace api\models;

use api\components\GeometryPolyUtil;
use api\components\GeometrySphericalUtil;
use api\modules\v1\models\WorkLocationPolygon;

/**
 * This is the model class for table "work_location".
 *
 * @property HrCompany $company
 * @property EmployeeWorkLocation[] $employeeWorkLocations
 * @property WorkLocationPolygon[] $workLocationPolygons
 */
class WorkLocation extends WorkLocationParent
{
    const TYPE_WORK_LOCATION_HEAD_OFFICE = 1;
    const TYPE_WORK_LOCATION_MAIN_OFFICE = 2;
    const TYPE_WORK_LOCATION_WORKSTATION = 3;
    const PERIMETER_SHAPE_CIRCULAR = 0;
    const PERIMETER_SHAPE_POLYGON = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.work_location';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => HrCompany::className(), 'targetAttribute' => ['company_id' => 'id']],
        ];
    }

    public static function getWorkLocationByCoordinatesForEmployeeId($data)
    {
        $allLocationForEmployee = self::find()->alias('wl')
            ->select(
                "`ec`.`employee_id`, 
                `ec`.`company_id`,
                `wl`.`id`,  
                `wl`.`name`, 
                `wl`.`latitude`, 
                `wl`.`longitude`, 
                `wl`.`address`,
                `wl`.`perimeter_shape_id`, 
                `wl`.`radius`"
            )
            ->join('INNER JOIN',
                'employee_company ec',
                'wl.company_id = ec.company_id')
            ->join('INNER JOIN',
                'employee_work_location ewl',
                'ewl.employee_id = ec.employee_id AND ewl.work_location_id = wl.id')
            ->where(
                "ec.employee_id = {$data['employee_id']}
                    AND wl.deleted = 0
                    AND ec.deleted = 0
                    AND ewl.deleted = 0"
            )
            ->all();
        foreach ($allLocationForEmployee as $locationForEmployee) {
            if ($locationForEmployee['perimeter_shape_id'] === self::PERIMETER_SHAPE_CIRCULAR) {
                $calcStartCoords = GeometrySphericalUtil::calcSingleCoordDistances($data['latitude'], $data['longitude'], $locationForEmployee['radius'], 6371009);
                if (
                    self::isFloatInRange($locationForEmployee['latitude'], $calcStartCoords[2], $calcStartCoords[0])
                    && self::isFloatInRange($locationForEmployee['longitude'], $calcStartCoords[3], $calcStartCoords[1])
                ) {
                    return $locationForEmployee;
                }
            }
            if ($locationForEmployee['perimeter_shape_id'] === self::PERIMETER_SHAPE_POLYGON) {
                $employeeLocation = self::getWorkLocationPolygon([
                    'location_id' => $locationForEmployee['id'],
                    'latitude' => $locationForEmployee['latitude'],
                    'longitude' => $locationForEmployee['longitude'],
                ]);
                if (empty($employeeLocation)) {
                    continue;
                }
                return $employeeLocation;
            }
        }
        return null;
    }

    public static function isFloatInRange($value, $min, $max)
    {
        return ($value >= $min && $value <= $max);
    }

    public static function getWorkLocationPolygon($data)
    {
        $polygon = self::getPolygonByLocationId($data['location_id']);
        $contains = GeometryPolyUtil::containsLocation(array(
            'lat' => (float)$data['latitude'],
            'lng' => (float)$data['longitude']
        ), $polygon);

        if ($contains) {
            return WorkLocation::find()->where([
                'id' => $data['location_id'],
                'deleted' => 0
            ])->one();
        }
        return null;
    }

    /**
     * @param $locationId
     * @return array
     */
    public static function getPolygonByLocationId($locationId)
    {
        $polygon = [];
        $locationsPolygon = WorkLocationPolygon::find()->where([
            'work_location_id' => $locationId,
            'deleted' => 0
        ])->all();
        foreach ($locationsPolygon as $locationPolygon) {
            $polygon[] = [
                'lat' => (float)$locationPolygon->latitude,
                'lng' => (float)$locationPolygon->longitude
            ];
        }
        return $polygon;
    }

    /**
     * Gets query for [[HrCompany]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(HrCompany::className(), ['id' => 'company_id']);
    }

    /**
     * Gets query for [[EmployeeWorkLocations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeWorkLocations()
    {
        return $this->hasMany(EmployeeWorkLocation::className(), ['work_location_id' => 'id']);
    }

    /**
     * Gets query for [[WorkLocationPolygons]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkLocationPolygons()
    {
        return $this->hasMany(WorkLocationPolygon::className(), ['work_location_id' => 'id']);
    }
}
