<?php

namespace console\controllers;

use backend\modules\crm\models\Company;
use backend\modules\finance\models\Account;
use backend\modules\finance\models\AccountSupplier;
use backend\modules\finance\models\CostCenter;
use backend\modules\finance\models\Invoice;
use backend\modules\finance\models\InvoiceBody;
use backend\modules\finance_centralizer\models\ProjExpense;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\db\Exception;
use yii\web\BadRequestHttpException;

class FinanceAddDataProjExpenseController extends Controller
{
    const EXPLICATION = 'Val. intr. ';

    /**
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionIndex($year = null, $month = null, $invoicesIDs = null)
    {
        echo "Script run at " . date("Y-m-d-h:m:s") . "\n";

        Company::setNames();
        Account::setAccounts();
        CostCenter::setAllCostCenters();

        if (!$year && !$month && !$invoicesIDs) {
            ProjExpense::deleteAll();
        }

        if (!$year && $month > 0) {
            throw new BadRequestHttpException(Yii::t('cmd-finance', 'You must also set the year!'));
        }

        if ($month > 12) {
            throw new BadRequestHttpException(Yii::t('cmd-finance', 'Unavailable month!'));
        }

        $where = " ib.deleted = 0";
        $where .= " AND i.status = 7";

        $columns = [
            'ib.id',
            'ib.invoice_id',
            'i.date_invoice',
            'i.vendor_company_id',
            'i.number_invoice',
            'ac.code',
            'acs.account_vendor_company_id',
            'ib.value_record',
            'ac.account_type',
            'ib.cost_center_id',
            'i.status'
        ];

        if ($year && $year > 0) {
            $where .= " AND SUBSTRING_INDEX(i.date_invoice, '.', -1) = {$year}";

            if ($month && $month > 0) {
                $where .= " AND SUBSTRING_INDEX(SUBSTRING_INDEX(i.date_invoice, '.', 2), '.', -1) = {$month}";
                ProjExpense::deleteAll([
                    'MONTH(date_invoice)' => $month,
                    'YEAR(date_invoice)' => $year]);
            } else {
                ProjExpense::deleteAll(['YEAR(date_invoice)' => $year]);
            }
        }

        if ($invoicesIDs) {
            $where .= " AND ib.invoice_id IN ({$invoicesIDs})";
            $array = explode(',', $invoicesIDs);
            foreach ($array as $invoiceID) {
                ProjExpense::deleteAll(['invoice_id' => $invoiceID]);
            }
        }

        $sql = 'SELECT ' . implode(',', $columns);
        $sql .= ' FROM ' . InvoiceBody::tableName() . ' ib';
        $sql .= ' LEFT JOIN ' . Invoice::tableName() . ' i ON ib.invoice_id = i.id';
        $sql .= ' LEFT JOIN ' . Account::tableName() . ' ac ON ib.account = ac.id';
        $sql .= ' LEFT JOIN ' . AccountSupplier::tableName() . ' acs ON i.vendor_company_id = acs.vendor_company_id';
        $sql .= " WHERE {$where}";
        $sql .= ' GROUP BY ib.id';
        $sql .= ' ORDER BY ib.id DESC';

        $rows = self::queryAll($sql);

        foreach ($rows as $row) {
            $date = date_parse_from_format('d.m.Y', $row['date_invoice']);
            $formattedDate = date_format(date_create($date['year'] . '-' . $date['month'] . '-' . $date['day']), 'Y-m-d');

            $projExpense = new ProjExpense();
            $projExpense->invoice_id = $row['invoice_id'];
            $projExpense->date_invoice = $formattedDate;
            $projExpense->explication = self::EXPLICATION . Company::$names[$row['vendor_company_id']];
            $projExpense->invoice_number = $row['number_invoice'];
            $projExpense->account_debit = $row['code'];
            $projExpense->vendor_accounting_code = $row['account_vendor_company_id'] ? Account::$allAccounts[$row['account_vendor_company_id']] : '-';
            $projExpense->value_record = $row['value_record'];
            $projExpense->account_debit_type = $row['account_type'];
            $projExpense->cost_center = $row['cost_center_id'] ? CostCenter::$allCostCenters[$row['cost_center_id']] : '-';

            if (!$projExpense->validate()) {
                foreach ($projExpense->errors as $error) {
                    throw new Exception($error[0]);
                }
            }

            if (!$projExpense->save()) {
                if ($projExpense->hasErrors()) {
                    throw new BadRequestHttpException(Yii::t('cmd-finance', 'Expense not found'));
                }
                throw new BadRequestHttpException(Yii::t('cmd-finance', 'Could not save the model!'));
            }
        }
    }

    /**
     * @throws InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_finance_db');
    }

    public static function queryAll($sql)
    {
        $conn = self::getDb();
        return $conn->createCommand($sql)->queryAll();
    }
}
