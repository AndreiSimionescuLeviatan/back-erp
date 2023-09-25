<?php

namespace api\models;


/**
 * This is the model class for table "employee".
 *
 * @property User $user
 * @property Department[] $departments
 * @property EmployeeCompany[] $employeeCompanies
 * @property EmployeeCompany[] $employeeCompanies0
 * @todo rename the following prop to mainCompany
 * @property EmployeeCompany[] $employeeMainCompany
 * @property EmployeePositionInternal[] $employeePositionInternals
 * @property EmployeeWorkLocation[] $employeeWorkLocations
 * @property Evaluation[] $evaluations
 * @property Evaluation[] $evaluations0
 * @property Office[] $offices
 * @property ShiftBreakInterval[] $shiftBreakIntervals
 * @property Shift[] $shifts
 * @property WorkingDayEmpl[] $workingDayEmpls
 * @property ApprovalHistory[] $approvalHistories
 *
 * @property EmployeeAutoFleet[] $employeeAutoFleets
 * @property PermissionDay[] $permissionDays
 * @property PermissionRecuperation[] $permissionRecuperations
 * @property RequestRecord[] $requestRecords
 * @property RequestRecord[] $asTakeOverRequestRecords
 * @property Shift $openshift
 * @property Shift[] $openedShifts
 * @property Shift[] $unvalidatedShifts
 */
class Employee extends EmployeeParent
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.employee';
    }

    /**
     * @param $userId
     * @return mixed|null
     */
    public static function getEmployeeId($userId)
    {
        $employee = self::find()->where('user_id = :user_id', [
            ':user_id' => $userId
        ])->one();
        if ($employee === null) {
            return null;
        }
        return $employee->id;
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
     * @return string
     */
    public function getIdentifiedBy()
    {
        $identifiedBy = $this->fullName();

        if ($this->gender == 0) {
            $identifiedBy .= ', identificat/ă';
        } elseif ($this->gender == 1) {
            $identifiedBy .= ', identificat';
        } else {
            $identifiedBy .= ', identificată';
        }

        $identifiedBy .= " prin CI, seria {$this->identity_card_series}";

        return $identifiedBy;
    }

    /**
     * @param $userId
     * @return array
     */
    public static function getHrDetails($userId)
    {
        $details = [];

        $employee = self::find()->where([
            'user_id' => $userId,
            'status' => self::STATUS_ACTIVE
        ])->one();
        if ($employee === null) {
            return $details;
        }

        $details['grade'] = $employee->getGradeDetails();

        $details['company_details'] = $employee->employeeMainCompany->company;

        $details['position_cor'] = $employee->employeeMainCompany->positionCor;

        $details['schedule'] = [
            'start' => $employee->start_schedule,
            'stop' => $employee->stop_schedule
        ];

        return $details;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getGradeDetails()
    {
        $lastYear = EvalEmployeeGradeMonth::find()->where('employee_id = :employee_id', [
            ':employee_id' => $this->id
        ])->max('year');
        $lastMonth = EvalEmployeeGradeMonth::find()->where('employee_id = :employee_id
        AND year = :year', [
            ':employee_id' => $this->id,
            ':year' => $lastYear
        ])->max('month');
        $lastMonthlyEval = EvalEmployeeGradeMonth::find()->where([
            'employee_id' => $this->id,
            'year' => $lastYear,
            'month' => $lastMonth
        ])->one();

        /**
         * added because for some users the $lastMonthlyEval is NULL and causes error to auto app
         * ERROR received in app:
         *      file: "/var/www/ecf-erp/api/modules/v1/models/User.php"
         *      line: 134
         *      message: "Trying to get property 'grade' of non-object"
         *      name: "PHP Notice"
         */
        $details = null;
        if ($lastMonthlyEval !== null) {
            $details = [
                'value' => !empty($lastMonthlyEval['grade']) ? $lastMonthlyEval['grade'] : (!empty($lastMonthlyEval->grade) ? $lastMonthlyEval->grade : ''),
                'accuracy' => !empty($lastMonthlyEval['accuracy']) ? $lastMonthlyEval['accuracy'] : (!empty($lastMonthlyEval->accuracy) ? $lastMonthlyEval->accuracy : ''),
            ];

            $icon = EvalEmployeeGrade::getEmployeeGeneralGradeIcon($this->id);
            if ($icon !== null) {
                $details['grade']['icon'] = $icon;
            }
        }

        return $details;
    }

    /**
     * @return array
     * @deprecated
     */
    public function getCompanyDetails()
    {
        $details = [];

        $employeeCompanies = EmployeeCompany::find()
            ->select('employee_id, company_id, main_activity')
            ->where("employee_id = {$this->id} AND deleted = 0")
            ->orderBy('id ASC')
            ->asArray()
            ->all();
        if (empty($employeeCompanies)) {
            return $details;
        }

        foreach ($employeeCompanies as $employeeCompany) {
            $existMainActivity = (int)$employeeCompany['main_activity'] === 1;
            $model = reset($employeeCompanies);
            $companyId = $model['company_id'];
            if ($existMainActivity) {
                $companyId = $employeeCompany['company_id'];
            }

            $erpCompany = HrCompany::find()->where([
                'company_id' => $companyId
            ])->one();
            if (empty($erpCompany)) {
                return $details;
            }

            $details = [
                'name' => !empty($erpCompany->company->name) ? $erpCompany->company->name : '',
                'geolocation' => [
                    'center' => [
                        'latitude' => !empty($erpCompany->latitude) ? $erpCompany->latitude : '',
                        'longitude' => !empty($erpCompany->longitude) ? $erpCompany->longitude : '',
                    ],
                    'radius' => !empty($erpCompany->radius) ? $erpCompany->radius : '',
                ]
            ];
            if ($existMainActivity) {
                return $details;
            }
        }
        return $details;
    }

    /**
     * @return array
     */
    public function getPositionCorDetails()
    {
        $details = [];

        $positionCor = PositionCor::find()->where([
            'id' => $this->position_cor_id
        ])->one();
        if ($positionCor === null) {
            return $details;
        }

        $details = [
            'id' => $positionCor->id,
            'name' => $positionCor->name,
        ];

        return $details;
    }

    /**
     * Gets query for [[Employee]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Departments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDepartments()
    {
        return $this->hasMany(Department::className(), ['head_of_department' => 'id']);
    }

    /**
     * Gets query for [[EmployeeCompanies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeCompanies()
    {
        return $this->hasMany(EmployeeCompany::className(), ['direct_manager' => 'id']);
    }

    /**
     * Gets query for [[EmployeeCompanies0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeCompanies0()
    {
        return $this->hasMany(EmployeeCompany::className(), ['employee_id' => 'id']);
    }

    /**
     * Gets employee main company.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeMainCompany()
    {
        return $this->hasOne(EmployeeCompany::className(), ['employee_id' => 'id'])
            ->where('main_activity = :main_activity', [':main_activity' => 1])
            ->with('company');
    }

    /**
     * Gets query for [[EmployeePositionInternals]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeePositionInternals()
    {
        return $this->hasMany(EmployeePositionInternal::className(), ['employee_id' => 'id']);
    }

    /**
     * Gets query for [[EmployeeWorkLocations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeWorkLocations()
    {
        return $this->hasMany(EmployeeWorkLocation::className(), ['employee_id' => 'id']);
    }

    /**
     * Gets query for [[Evaluations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEvaluations()
    {
        return $this->hasMany(Evaluation::className(), ['owner_employee_id' => 'id']);
    }

    /**
     * Gets query for [[Evaluations0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEvaluations0()
    {
        return $this->hasMany(Evaluation::className(), ['employee_id' => 'id']);
    }

    /**
     * Gets query for [[Offices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOffices()
    {
        return $this->hasMany(Office::className(), ['head_of_office' => 'id']);
    }

    /**
     * Gets query for [[ShiftBreakIntervals]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShiftBreakIntervals()
    {
        return $this->hasMany(ShiftBreakInterval::className(), ['employee_id' => 'id']);
    }

    /**
     * Gets query for [[Shifts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShifts()
    {
        return $this->hasMany(Shift::className(), ['employee_id' => 'id']);
    }

    /**
     * Gets query for [[WorkingDayEmpls]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkingDayEmpls()
    {
        return $this->hasMany(WorkingDayEmpl::className(), ['employee_id' => 'id']);
    }

    /**
     * Gets query for [[ApprovalHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getApprovalHistories()
    {
        return $this->hasMany(ApprovalHistory::className(), ['approver_id' => 'id']);
    }

    /**
     * Gets query for [[EmployeeAutoFleets]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeAutoFleets()
    {
        return $this->hasMany(EmployeeAutoFleet::className(), ['employee_id' => 'id']);
    }

    /**
     * Gets query for [[PermissionDays]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPermissionDays()
    {
        return $this->hasMany(PermissionDay::className(), ['employee_id' => 'id']);
    }

    /**
     * Gets query for [[PermissionRecuperations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPermissionRecuperations()
    {
        return $this->hasMany(PermissionRecuperation::className(), ['employee_id' => 'id']);
    }

    /**
     * Gets query for [[RequestRecords]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRequestRecords()
    {
        return $this->hasMany(RequestRecord::className(), ['employee_id' => 'id']);
    }

    /**
     * Gets query for [[RequestRecords0]] as take over user.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAsTakeOverRequestRecords()
    {
        return $this->hasMany(RequestRecord::className(), ['take_over_employee_id' => 'id']);
    }

    /**
     * Gets the employee single opened shift
     * If turn out that this is the correct way will remove  [[getOpenedShift]]
     * @return \yii\db\ActiveQuery
     */
    public function getOpenshift()
    {
        return $this->hasOne(Shift::className(), ['employee_id' => 'id'])
            ->where([
                'stop_initial' => null,
                'validated' => 0,
                'deleted' => 0,

            ])
            ->andWhere(['not', ['start_initial' => null]])
            ->orderBy('id DESC');
    }

    /**
     * Get the employee unvalidated shifts
     * @return \yii\db\ActiveQuery
     */
    public function getOpenedShifts()
    {
        return $this->hasMany(Shift::className(), ['employee_id' => 'id'])
            ->where([
                'stop_initial' => null,
                'validated' => 0,
                'deleted' => 0,

            ])
            ->andWhere(['not', ['start_initial' => null]])
            ->orderBy('id DESC');
    }

    /**
     * Get the employee unvalidated shifts
     * These shifts are the one closed, but not validated from the previous day or older,
     * the shift from the same day ar not considered unvalidated.
     * @return \yii\db\ActiveQuery
     */
    public function getUnvalidatedShifts()
    {
        return $this->hasMany(Shift::className(), ['employee_id' => 'id'])
            ->where([
                'validated' => 0,
                'deleted' => 0,
            ])
            ->andWhere(
                ['not', ['start_initial' => null]],
            )
            ->andWhere(
                ['not', ['stop_initial' => null]],
            )
            ->andWhere(
                ['<', 'stop_initial', date("Y-m-d H:i:s", strtotime("today midnight"))]
            )
            ->orderBy('id DESC');
    }
}
