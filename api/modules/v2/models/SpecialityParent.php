<?php

namespace api\modules\v2\models;

use Yii;

/**
 * This is the model class for table "speciality".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Document[] $documents
 * @property SpecialityActivity[] $specialityActivities
 * @property SpecialityTypology[] $specialityTypologies
 */
class SpecialityParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'speciality';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_design_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name', 'added', 'added_by'], 'required'],
            [['deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['code', 'name'], 'string', 'max' => 255],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('design', 'ID'),
            'code' => Yii::t('design', 'Code'),
            'name' => Yii::t('design', 'Name'),
            'deleted' => Yii::t('design', 'Deleted'),
            'added' => Yii::t('design', 'Added'),
            'added_by' => Yii::t('design', 'Added By'),
            'updated' => Yii::t('design', 'Updated'),
            'updated_by' => Yii::t('design', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Documents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments()
    {
        return $this->hasMany(Document::className(), ['speciality_id' => 'id']);
    }

    /**
     * Gets query for [[SpecialityActivities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialityActivities()
    {
        return $this->hasMany(SpecialityActivity::className(), ['speciality_id' => 'id']);
    }

    /**
     * Gets query for [[SpecialityTypologies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialityTypologies()
    {
        return $this->hasMany(SpecialityTypology::className(), ['speciality_id' => 'id']);
    }
}
