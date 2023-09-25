<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "access_token".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $token
 * @property string $last_used_at also known as `last_seen`
 * @property int|null $expire_at
 * @property string $added
 * @property string|null $updated
 *
 * @property User $user
 */
class AccessTokenParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'access_token';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_adm_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'expire_at'], 'integer'],
            [['token', 'last_used_at', 'added'], 'required'],
            [['last_used_at', 'added', 'updated'], 'safe'],
            [['token'], 'string', 'max' => 32],
            [['token'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'token' => Yii::t('app', 'Token'),
            'last_used_at' => Yii::t('app', 'Last Used At'),
            'expire_at' => Yii::t('app', 'Expire At'),
            'added' => Yii::t('app', 'Added'),
            'updated' => Yii::t('app', 'Updated'),
        ];
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
