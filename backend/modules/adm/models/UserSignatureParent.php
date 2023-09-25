<?php

namespace backend\modules\adm\models;

use Yii;

/**
 * This is the model class for table "user_signature".
 *
 * @property int $id
 * @property int $user_id
 * @property string $signature
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class UserSignatureParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_signature';
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
            [['user_id', 'signature', 'added', 'added_by'], 'required'],
            [['user_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['signature'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('adm', 'ID'),
            'user_id' => Yii::t('adm', 'User ID'),
            'signature' => Yii::t('adm', 'Signature'),
            'deleted' => Yii::t('adm', 'Deleted'),
            'added' => Yii::t('adm', 'Added'),
            'added_by' => Yii::t('adm', 'Added By'),
            'updated' => Yii::t('adm', 'Updated'),
            'updated_by' => Yii::t('adm', 'Updated By'),
        ];
    }
}
