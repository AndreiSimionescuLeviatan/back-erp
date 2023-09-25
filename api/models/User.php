<?php

namespace api\models;

use api\modules\v2\models\Employee;
use Exception;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\IdentityInterface;

/**
 * @property Car $ownedCar
 * @property Car $usedCar
 * @property Employee $employee
 *
 * @property HrCompany[] $companies
 * @property UserErpCompany[] $userCompanies
 */
class User extends UserParent implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;
    public $company_id;
    public static $noImagePath = 'images/profile-img/other.svg';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_INITIAL . '.user';
    }

    public function fields()
    {
        $fields = parent::fields();

        // remove fields that contain sensitive information
        unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token'], $fields['verification_token']);

        return ArrayHelper::merge($fields, [
            'photo' => function () {
                return User::getUserImage($this->id);
            },
            'full_name' => function () {
                return $this->fullName();
            },
            'company_id' => function () {
                return !empty($this->employee->employeeMainCompany) ? $this->employee->employeeMainCompany->company_id : null;
            },
            'employee_id' => function () {
                return !empty($this->employee) ? $this->employee->id : null;
            }
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_INACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
            [['first_name', 'last_name'], 'string', 'max' => 64],
            ['first_name', 'required'],
            ['last_name', 'required'],

            ['email', 'required'],
            ['email', 'unique', 'targetClass' => '\api\models\User', 'message' => 'This email address has already been taken.'],
            ['email', 'email'],
            ['email', 'trim'],
            ['email', 'string', 'max' => 255],

            ['username', 'trim'],
            ['username', 'unique', 'targetClass' => '\api\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],
        ];
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds an identity by the given access token.
     *
     * @param $token
     * @param $type
     * @return AccessToken|User|array|\yii\db\ActiveRecord|IdentityInterface|null
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $accessToken = AccessToken::find()->where(['token' => $token])->andWhere(['>', 'expire_at', strtotime('now')])->one();
        if (!$accessToken)
            return $accessToken;
        $accessToken->extendUserTokenExpireTime($accessToken->user_id);//on every login extend token expiration time period
        return User::findOne(['id' => $accessToken->user_id]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * @param $data
     * @return bool
     * @throws BadRequestHttpException
     */
    public static function validateLoginData($data)
    {
        if (empty($data['email'])) {
            throw new BadRequestHttpException(Yii::t('app', 'Wrong request received. No email.'), 400);
        }
        if (empty($data['password'])) {
            throw new BadRequestHttpException(Yii::t('app', 'Wrong request received. No password.'), 400);
        }
        return true;
    }

    /**
     * @param $input
     * @param $retries
     * @return User|null
     * @throws \Exception
     */
    public static function register($input, $retries = 100)
    {
        if ($retries <= 0) {
            return null;
        }

        try {
            self::validateRegisterInput($input);
        } catch (\Exception $exc) {
            throw new \Exception($exc->getMessage(), $exc->getCode());
        }

        $model = new User();
        $model->email = $input['email'];
        $model->first_name = $input['firstName'];
        $model->last_name = $input['lastName'];
        $model->password = $input['password'];
        $model->created_at = date('Y-m-d H:i:s');
        $model->last_auth = date('Y-m-d H:i:s');
        if (!$model->validate() && $model->hasErrors()) {
            foreach ($model->errors as $error) {
                throw new \Exception(Yii::t('app', $error[0]));
            }
        }
        $model->insert();

        return $model;
    }

    /**
     * @param $input
     * @return bool
     * @throws \Exception
     */
    public static function validateRegisterInput($input)
    {
        if (empty($input['email'])) {
            throw new \Exception(Yii::t('app', 'Wrong request received. No email.'), 400);
        }
        if (empty($input['firstName'])) {
            throw new \Exception(Yii::t('app', 'Wrong request received. No First Name.'), 400);
        }
        if (empty($input['lastName'])) {
            throw new \Exception(Yii::t('app', 'Wrong request received. No Last Name.'), 400);
        }
        if (empty($input['password'])) {
            throw new \Exception(Yii::t('app', 'Wrong request received. No password.'), 400);
        }
        if (empty($input['confirmPassword'])) {
            throw new \Exception(Yii::t('app', 'Wrong request received. No password confirmed.'), 400);
        }
        if ($input['password'] != $input['confirmPassword']) {
            throw new \Exception(Yii::t('app', 'Wrong request received. Wrong password confirmation.'), 400);
        }
        return true;
    }

    /**
     * @return int
     */
    public static function getAPIUserID()
    {
        //$user = User::find()->where("username = 'api'")->one();
        $userID = 999999999;
        // if ($user !== null) {
        //     $userID = $user->id;
        // }

        return $userID;
    }

    /**
     * @return string
     */
    public function fullName()
    {
        $fullName = '';
        if (!empty($this->first_name)) {
            $fullName .= $this->first_name;
        }
        if (!empty($fullName)) {
            $fullName .= ' ';
        }
        if (!empty($this->last_name)) {
            $fullName .= $this->last_name;
        }
        return $fullName;
    }

    /**
     * @param $userId
     * @param $token
     * @return User|array|\yii\db\ActiveRecord|null
     */
    public static function findUserByIdToken($userId, $token)
    {
        return User::find()
            ->where("id = :id AND auth_key = :auth_key", [':id' => $userId, ':auth_key' => $token])
            ->andWhere(['status' => self::STATUS_ACTIVE])
            ->one();
    }

    /**
     * Generates "remember me" authentication key.
     * @throws \yii\base\Exception
     */
    public function generateAuthKey()
    {
        try {
            $this->auth_key = Yii::$app->security->generateRandomString();
            AccessToken::generateAuthKey($this);
        } catch (Exception $exc) {
            /**
             * @todo save to app log the exception message
             */
            throw  new HttpException(500, Yii::t('app', 'Some errors appeared while authenticating the user. Please contact an administrator!'));
        }
    }

    /**
     * Get user by auth key
     *
     * @author Calin B.
     * @since 06.06.2022
     */
    public static function getUserByAuthKey($authKey)
    {
        $activeStatus = self::STATUS_ACTIVE;
        $user = User::find()->where("`status` = {$activeStatus} AND `auth_key` = '{$authKey}'")->one();
        if (empty($user)) {
            throw new BadRequestHttpException(Yii::t('app', 'No user find for specific token'), 400);
        }

        return $user;
    }

    /**
     * Gets query for [[Car]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOwnedCar()
    {
        return $this->hasOne(Car::class, ['holder_id' => 'id']);
    }

    /**
     * Gets query for [[Car]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsedCar()
    {
        return $this->hasOne(Car::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Employee]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::class, ['user_id' => 'id']);
    }

    /**
     * get user image url
     * @param $userId
     * @param $gender
     * @return string|void
     * @added_by Diana B.
     * @added 04.08.2022
     */
    public static function getUserImage($userId)
    {
        $user = self::find()->where('id = :user_id', [':user_id' => $userId])->one();
        if ($user === null) {
            return self::$noImagePath;
        }

        if (empty($user->photo)) {
            return self::getUserImageByGender(self::getGenderByUserId($userId));
        }

        if (!file_exists(Yii::getAlias("@backend/web/images/profile-img/{$user->photo}"))) {
            return self::getUserImageByGender(self::getGenderByUserId($userId));
        } else {
            $imgPath = "images/profile-img/{$user->photo}";
        }

        return $imgPath;
    }

    /**
     * get gender image url
     * @param $gender
     * @return string
     * @added_by Diana B.
     * @added 04.08.2022
     */
    public static function getUserImageByGender($gender)
    {
        switch ($gender) {
            case 0:
                return 'images/profile-img/other.svg';
            case 1:
                return 'images/profile-img/man.svg';
            case 2:
                return 'images/profile-img/woman.svg';
        }
        return self::$noImagePath;
    }

    /**
     * get gender by user id
     * @param $userId
     * @return int|mixed|null
     * @added_by Diana B.
     * @added 05.08.2022
     */
    public static function getGenderByUserId($userId)
    {
        $employee = Employee::find()->where('user_id = :user_id', [':user_id' => $userId])->one();
        return !empty($employee) ? $employee->gender : 0;
    }

    /**
     * Gets query for [[Companies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompanies()
    {
        return $this->hasMany(HrCompany::class, ['company_id' => 'company_id'])
            ->viaTable(UserErpCompany::tableName(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserCompanies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserCompanies()
    {
        return $this->hasMany(UserErpCompany::class, ['user_id' => 'id']);
    }
}
