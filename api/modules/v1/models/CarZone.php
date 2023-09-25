<?php

namespace api\modules\v1\models;


class CarZone extends \api\models\CarZone
{
    /**
     * Gets query for [[CarDocuments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
