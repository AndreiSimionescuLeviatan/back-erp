<?php

namespace api\modules\v1\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "user_signature".
 *
 * @property User $user
 */
class UserSignature extends UserSignatureParent
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_INITIAL . '.user_signature';
    }


    public function rules()
    {
        $rules = parent::rules();
        return ArrayHelper::merge($rules, [
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ]);
    }

    /**
     * @param $userID
     * @return string|null
     */
    public static function getSignature($userID)
    {
        $signature = UserSignature::find()
            ->where(['user_id' => $userID])
            ->andWhere(['deleted' => 0])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        if ($signature) {
            $signatureDir = Yii::getAlias('@backend/web/images/signatures');
            $signatureFile = $signatureDir . '/' . $signature->signature;
            if (file_exists($signatureFile)) {
                $signature->signature = file_get_contents($signatureFile);
                $signature->signature = base64_encode($signature->signature);
                $signature = 'data:image/png;base64,' . $signature->signature;
                return $signature;
            }
        }
        return null;
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
