<?php

namespace api\modules\v1\controllers;

use Yii;
use api\modules\v1\models\Company;

class CompanyController extends RestV1Controller
{
    public $modelClass = 'api\modules\v1\models\Company';

    public function actionClients()
    {
        ini_set("memory_limit", "1024M");
        set_time_limit(0);

        self::$threadName = 'CompanyController_actionClients';

        self::debug('Getting the list of the clients...');

        $customers = [];

        $sql = 'SELECT * ';
        $sql .= 'FROM ' . MYSQL_DB_MODULE_ECF_CRM . '.company ';
        $sql .= 'WHERE id IN (';
        $sql .= 'SELECT company_id FROM ' . MYSQL_DB_MODULE_ECF_FINANCE . '.invoice_import_company)';

        $rows = Company::queryAll($sql);
        foreach ($rows as $row) {
            $customers[$row['id']] = $row;
        }

        self::debug('Clients: ' . json_encode($customers));

        $this->return['status'] = 200;
        Yii::$app->response->statusCode = $this->return['status'];
        $this->return['customers'] = $customers;

        return $this->return;
    }
    
}