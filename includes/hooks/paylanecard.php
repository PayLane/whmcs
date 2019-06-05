<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use WHMCS\Database\Capsule;

add_hook('PreInvoicingGenerateInvoiceItems', 1, function($vars) {

    require_once __DIR__ . '/../../init.php';
    require_once __DIR__ . '/../../includes/gatewayfunctions.php';
    require_once __DIR__ . '/../../includes/invoicefunctions.php';
    require_once __DIR__ . '/../../modules/gateways/vendor/autoload.php';

    $date = date('Y-m-d');
    $paymentmethod = 'paylanecard';
    $status = 'Unpaid';

    $loginApi = null;
    $passwordApi = null;
    $codeCur = null;

    foreach (Capsule::table('tblinvoices')
             ->where('paymentmethod', $paymentmethod)
             ->where('duedate', $date)
             ->where('status', $status)
             ->get() as $invoice) {

        $account = Capsule::table('tblaccounts')
                 ->where('userid', $invoice->userid)
                 ->where('gateway', $paymentmethod)
                 ->orderBy('id', 'desc')
                 ->first();

        if ($account) {
            if (!$loginApi || !$passwordApi) {
                $gatewayLogin = Capsule::table('tblpaymentgateways')
                         ->where('gateway', $paymentmethod)
                         ->where('setting', 'loginApi')
                         ->first();

                $loginApi = $gatewayLogin->value;

                $gatewayPassword = Capsule::table('tblpaymentgateways')
                              ->where('gateway', $paymentmethod)
                              ->where('setting', 'passwordApi')
                              ->first();

                $passwordApi = $gatewayPassword->value;
            }

            if (!$codeCur) {
                $currencies = Capsule::table('tblcurrencies')
                                 ->where('default', 1)
                                 ->first();
                $codeCur = $currencies->code;
            }

            if ($loginApi && $passwordApi) {
                $client = new PayLaneRestClient($loginApi, $passwordApi);

                $resaleParams = array(
                    'id_sale'     => $account->transid,
                    'amount'      => $invoice->total,
                    'currency'    => $codeCur,
                    'description' => 'Recurring billing invoiceid ' . $account->invoiceid,
                );

                try {
                    $status = $client->resaleBySale($resaleParams);
                } catch (Exception $e) {
                    logModuleCall($paymentmethod, 'error', $invoice, $e, '', '');
                    logModuleCall($paymentmethod, 'error', $account, $e, '', '');
                }

                if ($client->isSuccess()) {
                    addInvoicePayment(
                        $invoice->id,
                        $status['id_sale'],
                        $invoice->total,
                        null,
                        $paymentmethod
                    );

                    logModuleCall($paymentmethod, 'ok', $resaleParams, $status, '', '');
                } else {
                    logModuleCall($paymentmethod, 'error', $resaleParams, $status, '', '');
                }
            }
        }
    }
});

