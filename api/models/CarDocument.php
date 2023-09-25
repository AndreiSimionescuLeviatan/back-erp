<?php

namespace api\models;

use backend\modules\auto\models\AutoActiveRecord;
use DateTime;
use Yii;

/**
 * This is the model class for table "car_document".
 *
 * @property int $id
 * @property int $car_id
 * @property int|null $rca_company_id
 * @property string|null $rca_valid_until
 * @property string|null $rca_agent
 * @property int|null $casco_company_id
 * @property string|null $casco_valid_until
 * @property string|null $itp_valid_until
 * @property string|null $vignette_valid_until
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Car $car
 */
class CarDocument extends AutoActiveRecord
{
    public static $documentTypes = [
        'rca_document_file' => [],
        'casco_document_file' => [],
        'itp_document_file' => [],
        'vignette_document_file' => []
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'car_document';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_auto_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['car_id', 'added', 'added_by'], 'required'],
            [['car_id', 'rca_company_id', 'casco_company_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['rca_valid_until', 'casco_valid_until', 'itp_valid_until', 'vignette_valid_until', 'added', 'updated'], 'safe'],
            [['rca_agent'], 'string', 'max' => 32],
            [['car_id'], 'exist', 'skipOnError' => true, 'targetClass' => Car::className(), 'targetAttribute' => ['car_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'car_id' => Yii::t('app', 'Car ID'),
            'rca_company_id' => Yii::t('app', 'Rca Company ID'),
            'rca_valid_until' => Yii::t('app', 'Rca Valid Until'),
            'rca_agent' => Yii::t('app', 'Rca Agent'),
            'casco_company_id' => Yii::t('app', 'Casco Company ID'),
            'casco_valid_until' => Yii::t('app', 'Casco Valid Until'),
            'itp_valid_until' => Yii::t('app', 'Itp Valid Until'),
            'vignette_valid_until' => Yii::t('app', 'Vignette Valid Until'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Car]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCar()
    {
        return $this->hasOne(Car::className(), ['id' => 'car_id']);
    }

    /**
     * Converts documents expiration date to years/months/days and set the card class based on expiration date
     * @param $docEndDateValidity
     * @return array
     * @throws \Exception
     */
    public static function getCarDocumentStatus($docEndDateValidity, $f7 = false)
    {
        $endDateDoc = new DateTime($docEndDateValidity);
        $currentDate = new DateTime(date('Y-m-d'));
        $differenceDays = $currentDate->diff($endDateDoc);
        $days = $differenceDays->format("%r%a");
        $dd = date_diff($currentDate, $endDateDoc);

        if (empty($docEndDateValidity)) {
            $expMsg = Yii::t('api-auto', 'The document does not exist');
            $validityStatusBgColor = '#0059a9cc';
            $validityStatusTxtColor = 'text-white';
            $bsValidityTextClass = 'text-info';
        } else {
            $expMsg = Yii::t('api-auto', 'Validity: ');
            if ($days <= 0) {
                $validityStatusBgColor = '#ff4c4ccc';
                $validityStatusTxtColor = 'text-white';
                $bsValidityTextClass = 'text-danger';
                if ($days < 0) {
                    $expMsg = Yii::t('api-auto', 'The document is no longer valid for ');
                }
            } elseif ($days < 7) {
                $validityStatusBgColor = '#ff9500cc';
                $validityStatusTxtColor = 'text-white';
                $bsValidityTextClass = 'text-warning';
            } else {
                $validityStatusBgColor = '#0fab4fcc';
                $validityStatusTxtColor = 'text-white';
                $bsValidityTextClass = 'text-success';
            }
        }
        if (!empty($docEndDateValidity)) {
            $expMsg .= Yii::t('app', '{y,plural,=0{} =1{one year, } other{# years, }}', ['y' => $dd->y]);
            $expMsg .= Yii::t('app', '{m,plural,=0{} =1{1 month and } other{# months and }}', ['m' => $dd->m]);
            $expMsg .= Yii::t('app', '{d,plural,=1{1 day} other{# days}}', ['d' => $dd->d])." ({$endDateDoc->format('d M Y')})";
        }

        return [
            'message' => $expMsg,
            'doc_bg_class' => $validityStatusBgColor,
            'doc_txt_class' => $validityStatusTxtColor,
            'bs_doc_text_class' => $bsValidityTextClass,
            'exp_date' => !empty($docEndDateValidity) ? $docEndDateValidity : ''
        ];
    }

    public static function setDocumentType()
    {
        self::$documentTypes['rca_document_file'] = [
            'field' => 'rca',
            'label' => Yii::t('api-auto', 'RCA')
        ];
        self::$documentTypes['itp_document_file'] = [
            'field' => 'itp',
            'label' => Yii::t('api-auto', 'ITP')
        ];
        self::$documentTypes['casco_document_file'] = [
            'field' => 'casco',
            'label' => Yii::t('api-auto', 'CASCO')
        ];
        self::$documentTypes['vignette_document_file'] = [
            'field' => 'vignette',
            'label' => Yii::t('api-auto', 'VIGNETTE')
        ];
    }
}