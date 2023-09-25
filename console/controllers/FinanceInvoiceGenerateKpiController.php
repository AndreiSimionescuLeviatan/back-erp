<?php

namespace console\controllers;

use backend\modules\finance\models\Invoice;
use backend\modules\finance\models\InvoiceBody;
use backend\modules\finance\models\InvoiceFormattedKpi;
use backend\modules\finance\models\InvoiceHeader;
use backend\modules\finance\models\InvoiceKpi;
use yii\console\Controller;
use Exception;
use Yii;
use yii\console\ExitCode;

class FinanceInvoiceGenerateKpiController extends Controller
{
    /**
     * @throws Exception
     */
    public function actionIndex()
    {
        ini_set("memory_limit", "2048M");
        set_time_limit(0);

        $invoices = Invoice::find()
            ->where(['deleted' => 0])
            ->andWhere(['IN', 'status', [Invoice::STATUS_CORRECTED]])
            ->all();

        $invoicesDetails = [];

        foreach ($invoices as $invoice) {
            $invoiceHeader = InvoiceHeader::findOneByAttributes([
                'invoice_id' => $invoice->id
            ]);

            try {
                $invoiceHeader->compareColumnsValues($invoice->number_of_records, '', 'raw');
                $invoiceHeader->compareColumnsValues($invoice->number_of_records);
            } catch (\Exception $exc) {
                echo $exc->getMessage();
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $invoiceBodies = InvoiceBody::findAllByAttributes([
                'invoice_id' => $invoice->id,
            ]);
            foreach ($invoiceBodies as $invoiceBody) {
                try {
                    $invoiceBody->compareColumnsValues($invoice->vendor_company_id, $invoice->customer_company_id, $invoice->number_of_records, $invoiceHeader->date_invoice, '', 'raw');
                    $invoiceBody->compareColumnsValues($invoice->vendor_company_id, $invoice->customer_company_id, $invoice->number_of_records, $invoiceHeader->date_invoice);
                } catch (\Exception $exc) {
                    echo $exc->getMessage();
                    return ExitCode::UNSPECIFIED_ERROR;
                }
            }

            if (!isset($invoicesDetails[$invoice->customer_company_id])) {
                $invoicesDetails[$invoice->customer_company_id] = [];
            }
            $timestamp = strtotime("{$invoiceHeader->date_invoice} 00:00:00");
            if (!$timestamp) {
                continue;
            }
            $year = date('Y', $timestamp);
            $month = date('m', $timestamp);
            if (!isset($invoicesDetails[$invoice->customer_company_id][$year])) {
                $invoicesDetails[$invoice->customer_company_id][$year] = [];
            }
            if (!isset($invoicesDetails[$invoice->customer_company_id][$year][$month])) {
                $invoicesDetails[$invoice->customer_company_id][$year][$month] = [];
            }
            if (!isset($invoicesDetails[$invoice->customer_company_id][$year][$month][$invoice->vendor_company_id])) {
                $invoicesDetails[$invoice->customer_company_id][$year][$month][$invoice->vendor_company_id] = [];
            }
            $invoicesDetails[$invoice->customer_company_id][$year][$month][$invoice->vendor_company_id][$invoice->number_of_records] = $invoice->number_of_records;
        }

        foreach ($invoicesDetails as $customerCompanyID => $customerDetails) {
            foreach ($customerDetails as $year => $yearDetails) {
                foreach ($yearDetails as $month => $monthDetails) {
                    foreach ($monthDetails as $vendorCompanyID => $vendorDetails) {
                        foreach ($vendorDetails as $numberOfRecords) {
                            $invoiceDetails = [
                                'vendor_company_id' => $vendorCompanyID,
                                'customer_company_id' => $customerCompanyID,
                                'number_of_records' => $numberOfRecords,
                                'year' => $year,
                                'month' => $month
                            ];

                            try {
                                InvoiceFormattedKpi::calculateHeaderColumnsKpi($invoiceDetails, 'raw');
                                InvoiceFormattedKpi::calculateHeaderColumnsKpi($invoiceDetails);
                            } catch (\Exception $exc) {
                                echo $exc->getMessage();
                                return ExitCode::UNSPECIFIED_ERROR;
                            }

                            try {
                                InvoiceFormattedKpi::calculateBodyColumnsKpi($invoiceDetails, 'raw');
                                InvoiceFormattedKpi::calculateBodyColumnsKpi($invoiceDetails);
                            } catch (\Exception $exc) {
                                echo $exc->getMessage();
                                return ExitCode::UNSPECIFIED_ERROR;
                            }
                        }
                    }
                }
            }
        }

        try {
            InvoiceKpi::calculateKpi('raw');
            InvoiceKpi::calculateKpi();
        } catch (\Exception $exc) {
            echo $exc->getMessage();
            return ExitCode::UNSPECIFIED_ERROR;
        }

        echo "KPI invoices successfully generated" . "\n";

        return ExitCode::OK;
    }
}
