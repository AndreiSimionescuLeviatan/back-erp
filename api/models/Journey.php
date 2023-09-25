<?php

namespace api\models;

use backend\modules\auto\models\Location;
use backend\modules\design\models\Project;
use Yii;

class Journey extends JourneyParent
{

    /**
     * Gets query for [[start locations]].
     * @return \yii\db\ActiveQuery
     */
    public function getStartHotspot()
    {
        return $this->hasOne(Location::className(), ['id' => 'start_hotspot_id']);
    }

    /**
     * Gets query for [[stop locations]].
     * @return \yii\db\ActiveQuery
     */
    public function getStopHotspot()
    {
        return $this->hasOne(Location::className(), ['id' => 'stop_hotspot_id']);
    }

    /**
     * Gets query for [[project Id]].
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['id' => 'project_id']);
    }

    public static function getByUserIdAndStatus($userID, $status)
    {
        $where = 'user_id = :user_id AND status = :status';
        $whereParams = [
            ':user_id' => $userID,
            ':status' => $status
        ];
        if (empty($journeys) && $status === '2') {
            $where = 'user_id = :user_id AND deleted = 1';
            $whereParams = [
                ':user_id' => $userID
            ];
        }
        return self::find()->where($where, $whereParams)->all();
    }

    public function getLocations()
    {
        $locations = [];

        $tableName = Location::tableName();
        $sql = "SELECT * FROM {$tableName} WHERE deleted = 0 AND id IN ({$this->start_hotspot_id}, {$this->stop_hotspot_id});";
        $models = Location::queryAll($sql);

        foreach ($models as $model) {
            if (empty($model['name'])) {
                continue;
            }

            if (strpos($model['name'], 'HotSpot') !== false) {
                $locations[$model['id']] = $model['address'];
                continue;
            }

            $locations[$model['id']] = $model['name'];
        }

        return $locations;
    }

}