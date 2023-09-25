<?php

namespace console\controllers;

use backend\modules\crm\models\Company;
use backend\modules\finance\models\Account;
use backend\modules\finance\models\AccountSupplier;
use backend\modules\finance\models\Invoice;
use Yii;
use yii\console\Controller;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;

class FinanceAddDataAccountSupplierController extends Controller
{
    /**
     * @throws \Throwable
     * @throws Exception
     * @throws StaleObjectException
     * @throws BadRequestHttpException
     */
    public function actionIndex()
    {
        echo "Script run at " . date("Y-m-d-h:m:s") . "\n";

        $sqlInvoices = "SELECT i.vendor_company_id, c.name, i.customer_company_id ";
        $sqlInvoices .= " FROM " . Invoice::tableName() . ' i';
        $sqlInvoices .= " LEFT JOIN " . Company::tableName() . ' c ON i.vendor_company_id = c.id';
        $sqlInvoices .= " WHERE i.status = 7 AND i.deleted = 0";
        $sqlInvoices .= " GROUP BY i.vendor_company_id, i.customer_company_id";
        $sqlInvoices .= " ORDER BY i.customer_company_id";

        $invoices = Invoice::queryAll($sqlInvoices);

        foreach ($invoices as $invoice) {
            $account = null;
            $invoice['name'] = str_replace("'", "\'", $invoice['name']);

            if ($invoice['name']) {
                $where = " name LIKE '%{$invoice['name']}%'";
                $where .= " AND code LIKE '%401.%'";
                $where .= " AND company_id = {$invoice['customer_company_id']}";

                $account = Account::find()->where($where)->one();
            }

            $whereAccountSupplier = " vendor_company_id = {$invoice['vendor_company_id']}";
            $whereAccountSupplier .= " AND customer_company_id = {$invoice['customer_company_id']}";

            $accountSupplier = AccountSupplier::find()->where($whereAccountSupplier)->one();

            if ($accountSupplier === null) {
                $accountSupplier = new AccountSupplier();
                $accountSupplier->customer_company_id = $invoice['customer_company_id'];
                $accountSupplier->vendor_company_id = $invoice['vendor_company_id'];
                $accountSupplier->account_vendor_company_id = $account ? $account->id : NULL;

                if (!$accountSupplier->validate()) {
                    foreach ($accountSupplier->errors as $error) {
                        throw new Exception($error[0]);
                    }
                }

                if (!$accountSupplier->save()) {
                    if ($accountSupplier->hasErrors()) {
                        throw new BadRequestHttpException(Yii::t('cmd-finance', 'Account supplier not found!'));
                    }
                    throw new BadRequestHttpException(Yii::t('cmd-finance', 'Could not save the model!'));
                }
            } else if (
                $account !== null
                && $account['id'] != $accountSupplier->account_vendor_company_id
            ) {
                $accountSupplier->account_vendor_company_id = $account['id'];
                $accountSupplier->update();
            }
        }
    }
}
