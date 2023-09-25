<?php

namespace api\modules\v1\models;

use api\models\CarOperation;
use api\models\Company;
use backend\modules\auto\models\Car;

/**
 * This is the model class for table "user".
 */
class User extends UserParent
{
    /**
     * This method overwrites the parent one because we didn't find a
     * better way to remove only some validation rules and keep the others from parent
     * The parent ones that we need to remove are:
     * - email/username uniques tha should target the class '\api\modules\v1\models\User'
     * @return array
     * @todo maybe in the future will rewrite and improve
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
            ['email', 'unique', 'targetClass' => '\api\modules\v1\models\User', 'message' => 'This email address has already been taken.'],
            ['email', 'email'],
            ['email', 'trim'],
            ['email', 'string', 'max' => 255],

            ['username', 'trim'],
            ['username', 'unique', 'targetClass' => '\api\modules\v1\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],
        ];
    }

    public function getAutoDetails()
    {
        $details = [];

        $userCarId = null;
        $usedCarDetails = null;
        $usedCars = Car::findAllByAttributes([
            'user_id' => $this->id,
            'status' => Car::STATUS_NOT_AVAILABLE_CAR
        ]);
        foreach ($usedCars as $car) {
            $userCarId = $car->id;
            $carDetails = [
                'brand' => '',
                'model' => '',
                'check_in_date' => '',
            ];
            $carDetails['plate_number'] = $car->plate_number;

            if (
                !empty($car->brand)
                && !empty($car->brand->name)
            ) {
                $carDetails['brand'] = $car->brand->name;
            }

            if (
                !empty($car->brandModel)
                && !empty($car->brandModel->name)
            ) {
                $carDetails['model'] = $car->brandModel->name;
            }

            $lastCheckIn = CarOperation::find()
                ->where("user_id = {$this->id} AND car_id = {$userCarId} AND operation_type_id = " . CarOperation::CAR_CHECK_IN)
                ->orderBy('added DESC')
                ->one();
            if (!empty($lastCheckIn)) {
                $carDetails['check_in_date'] = $lastCheckIn->added;
            }

            $usedCarDetails[$car->id] = $carDetails;
            $details[] = $carDetails;
        }

        $invalidJourneys = Journey::findAllByAttributes([
            'status' => Journey::STATUS_FOR_INVALID,
            'deleted' => 0, 'user_id' => $this->id,
            'merged_with_id' => 0
        ]);
        $details['countInvalidJourneys'] = count($invalidJourneys);

        $companies = [];
        if ($this->employee !== null) {//first check if user is also an employee because there are some occasions when is not
            $employeeAutoFleets = EmployeeAutoFleet::find()
                ->where(['deleted' => 0, 'employee_id' => $this->employee->id])
                ->all();
            if (!empty($employeeAutoFleets)) {
                Company::setNamesAuto();
                foreach ($employeeAutoFleets as $employeeAutoFleet) {
                    $companies[] = [
                        'id' => $employeeAutoFleet->company_id,
                        'name' => !empty(Company::$auto[$employeeAutoFleet->company_id]) ? Company::$auto[$employeeAutoFleet->company_id] : '-'
                    ];
                }
            }
        }

        $details['companies'] = $companies;
        $details['car_id'] = $userCarId;
        $details['used_car_details'] = $usedCarDetails;

        return $details;
    }
}
