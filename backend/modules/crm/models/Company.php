<?php

namespace backend\modules\crm\models;

use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use backend\modules\finance\models\Account;
use backend\modules\finance\models\AnalyticsPnlMonthly;
use backend\modules\finance\models\AnalyticsPnlYearly;
use backend\modules\finance\models\CostCenter;
use backend\modules\location\models\City;
use backend\modules\location\models\Country;
use backend\modules\location\models\State;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Exception;

/**
 * This is the model class that extends the "CompanyParent" class.
 * @property Country $country
 * @property State $state
 * @property City $city
 * @property CostCenter[] $costCenters
 * @property Account[] $accounts
 */
class Company extends CompanyParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_CRM . '.company';
    }

    public static $names = null;
    public static $auto = null;
    public static $insurance = null;
    public static $fuel = null;
    public static $gps = null;
    public static $trustedCompany = null;
    public static $statusTVA = [];
    public static $filtersOptions = [
        'added_by' => [],
        'updated_by' => []
    ];
    public static $apiKeys = [];

    public $ibans = [];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name', 'tva', 'cui', 'reg_number', 'added', 'added_by'], 'required'],
            [['country_id', 'city_id', 'deleted', 'added_by', 'updated_by', 'tva'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['code', 'name', 'short_name', 'cui', 'reg_number', 'address'], 'string', 'max' => 255],
            [['code', 'cui'], 'unique', 'filter' => ['deleted' => '0']],
            [['city_id'], 'exist', 'skipOnError' => true, 'targetClass' => City::className(), 'targetAttribute' => ['city_id' => 'id']],
            [['country_id'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['country_id' => 'id']],
        ];
    }

    /**
     * Due to this function we can extract ether all Company or just deleted Company
     */
    public static function setNames()
    {
        self::$names = [];
        $models = self::find()->where(['deleted' => 0])->orderBy('name')->all();
        foreach ($models as $model) {
            self::$names[$model->id] = strtoupper($model->name);
        }
    }

    /**
     * Due to this function we can extract ether all Company from domain Auto subdomain auto
     */
    public static function setNamesAuto()
    {
        $itemsIDs = EntityDomain::find()->where(['domain_id' => 1, 'entity_id' => 2, 'subdomain_id' => 2])->all();
        foreach ($itemsIDs as $itemsID) {
            $items[] = $itemsID->item_id;
        }
        $companyNames = [];
        if (!empty($items)) {
            $companyNames = self::find()->where(['in', 'id', $items])->all();
        }
        self::$auto = [];
        foreach ($companyNames as $companyName) {
            self::$auto[$companyName->id] = $companyName->name;
        }
    }

    /**
     * Due to this function we can extract ether all Company from domain Auto subdomain insurance
     */
    public static function setNamesInsurance()
    {
        $itemsIDs = EntityDomain::find()->where(['domain_id' => 1, 'entity_id' => 2, 'subdomain_id' => 3])->all();
        foreach ($itemsIDs as $itemsID) {
            $items[] = $itemsID->item_id;
        }
        $companyNames = [];
        if (!empty($items)) {
            $companyNames = self::find()->where(['in', 'id', $items])->all();
        }
        self::$insurance = [];
        foreach ($companyNames as $companyName) {
            self::$insurance[$companyName->id] = $companyName->name;
        }
    }

    /**
     * Due to this function we can extract ether all Company from domain Auto subdomain fuel
     */
    public static function setNamesFuel()
    {
        $itemsIDs = EntityDomain::find()->where(['domain_id' => 1, 'entity_id' => 2, 'subdomain_id' => 4])->all();
        foreach ($itemsIDs as $itemsID) {
            $items[] = $itemsID->item_id;
        }
        $companyNames = [];
        if (!empty($items)) {
            $companyNames = self::find()->where(['in', 'id', $items])->all();
        }
        self::$fuel = [];
        foreach ($companyNames as $companyName) {
            self::$fuel[$companyName->id] = $companyName->name;
        }
    }

    /**
     * Due to this function we can extract ether all Company from domain Auto subdomain gps
     */
    public static function setNamesGPS()
    {
        $itemsIDs = EntityDomain::find()->where(['domain_id' => 1, 'entity_id' => 2, 'subdomain_id' => 5])->all();
        foreach ($itemsIDs as $itemsID) {
            $items[] = $itemsID->item_id;
        }
        $companyNames = [];
        if (!empty($items)) {
            $companyNames = self::find()->where(['in', 'id', $items])->all();
        }
        self::$gps = [];
        foreach ($companyNames as $companyName) {
            self::$gps[$companyName->id] = $companyName->name;
        }
    }

    public static function getDefaultCompanyData($dataSource)
    {
        if ($dataSource === 'yearly') {
            $companyID = AnalyticsPnlYearly::getCompaniesID();
        } elseif ($dataSource === 'monthly') {
            $companyID = AnalyticsPnlMonthly::getCompaniesID();
        } else {
            throw new \Exception(Yii::t('crm', "Data source not found"), 400);
        }

        if ($companyID == 0) {
            throw  new \Exception("There are no generated reports", 400);
        }

        $company = Company::find()->where(['id' => $companyID, 'deleted' => 0])->one();

        if ($company == null) {
            do {
                $companyID++;
                if ($dataSource === 'yearly') {
                    $pnlData = AnalyticsPnlYearly::find()->where("company_id = {$companyID}")->asArray()->one();
                } elseif ($dataSource === 'monthly') {
                    $pnlData = AnalyticsPnlMonthly::find()->where("company_id = {$companyID}")->all();
                } else {
                    throw  new \Exception(Yii::t('crm', "No {$dataSource} reports generated for {$company->name}"), 400);
                }
            } while (empty($pnlData));
        }

        return Company::find()->where(['id' => $companyID, 'deleted' => 0])->one();
    }

    /**
     * Gets query for [[Country]].
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }

    /**
     * Gets query for [[City]].
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * Gets query for [[CostCenter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCostCenters()
    {
        return $this->hasMany(CostCenter::className(), ['company_id' => 'id']);
    }

    /**
     * Gets query for [[Account]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccounts()
    {
        return $this->hasMany(Account::className(), ['company_id' => 'id']);
    }

    /**
     * Validate CIF inserted by user
     */
    public static function validateCIF($cui)
    {
        $getCUI = substr(str_replace(' ', '', $cui), 0, 2);
        $cif = !empty(strtoupper($getCUI) == 'RO') ? trim(substr($cui, 2, strlen($cui) - 2)) : trim($cui);
        $message = "CUI is not valid";

        if (!is_numeric($cif)) throw new Exception(Yii::t('crm', "{$message}"));
        if (strlen($cif) < 4 || strlen($cif) > 10) throw new Exception(Yii::t('crm', "{$message}"));

        $controlNumber = substr($cif, -1);
        $cif = substr($cif, 0, -1);
        while (strlen($cif) != 9) {
            $cif = '0' . $cif;
        }
        $sum = $cif[0] * 7 + $cif[1] * 5 + $cif[2] * 3 + $cif[3] * 2 + $cif[4] * 1 + $cif[5] * 7 + $cif[6] * 5 + $cif[7] * 3 + $cif[8] * 2;
        $sum = $sum * 10;
        $rest = fmod($sum, 11);
        if ($rest == 10) $rest = 0;

        if ($rest == $controlNumber) return true;
        else return false;
    }

    /**
     * Validate CIF inserted by user
     */
    public static function setStatusTVA()
    {
        self::$statusTVA = [
            0 => Yii::t('crm', 'Yes'),
            1 => Yii::t('crm', 'No')
        ];
    }

    /**
     * Gets query for [[State]].
     */
    public function getState()
    {
        return $this->hasOne(State::className(), ['id' => 'state_id']);
    }

    /**
     * Due to this function we can extract the trusted company
     */
    public static function setNamesCompanyTrusted()
    {
        $itemsIDs = self::find()->where(['id' => [1, 2]])->all();
        foreach ($itemsIDs as $itemsID) {
            $items[] = $itemsID->id;
        }
        $companyNames = [];
        if (!empty($items)) {
            $companyNames = self::find()->where(['in', 'id', $items])->orderBy('name')->all();
        }
        self::$trustedCompany = [];
        foreach ($companyNames as $companyName) {
            self::$trustedCompany[$companyName->id] = $companyName->name;
        }
    }

    public static function setFilterOptions($type = '')
    {
        if (empty($type)) {
            return;
        }

        self::$filtersOptions[$type] = [];
        if (empty(User::$users)) {
            User::setUsers(true);
        }

        $models = self::find()->distinct($type)->all();
        foreach ($models as $model) {
            if (empty(User::$users[$model->$type])) {
                continue;
            }
            self::$filtersOptions[$type][$model->$type] = User::$users[$model->$type];
        }
    }

    public function setIbans()
    {
        $this->ibans = [];

        $ibans = IbanCompany::findAllByAttributes([
            'company_id' => $this->id,
            'deleted' => 0
        ]);
        if (empty($ibans)) {
            return false;
        }

        foreach ($ibans as $iban) {
            $this->ibans[] = $iban->iban;
        }

        return true;
    }

    public static function getApiKeyByCompanyId($companyId)
    {
        $apiKey = false;

        $company = self::findOneByAttributes(['id' => $companyId]);
        if (!empty($company)) {
            $shortName = strtoupper($company->short_name);
            if (strpos($shortName, ' ') !== false) {
                $shortName = implode('_', explode(' ', $shortName));
            }
            $setting = Settings::findOneByAttributes(['name' => "API_KEY_{$shortName}_NEXUS"]);
            if (!empty($setting)) {
                $apiKey = $setting->value;
            }
        }

        return $apiKey;
    }

    public static function getCompanyLogo($id)
    {
        $logo = '';
        $company = self::findOneByAttributes(['id' => $id]);
        if ($company !== null){
            $shortName = lcfirst($company->short_name);
            $logo = Yii::getAlias("@backend/web/images/logo-{$shortName}.png");
        }
        return $logo;
    }
}
