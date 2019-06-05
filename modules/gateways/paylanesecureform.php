<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

include_once(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

function paylanesecureform_MetaData()
{
    return array(
        'DisplayName' => 'PayLane SecureForm',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => true,
    );
}

function paylanesecureform_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'PayLane SecureForm',
        ),
        'loginApi' => array(
            'FriendlyName' => 'Login API',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your Login API here',
        ),
        'passwordApi' => array(
            'FriendlyName' => 'Password API',
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter password key here',
        ),
        'merchantId' => array(
            'FriendlyName' => 'Merchant ID',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter merchant ID key here',
        ),
        'hashSalt' => array(
            'FriendlyName' => 'Hash salt',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter Hash salt key here',
        ),
    );
}

function paylanesecureform_link($params)
{
    $merchantId = $params['merchantId'];
    $hashSalt = $params['hashSalt'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $fullname = $params['clientdetails']['fullname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $language = $params['clientdetails']['language'];

    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleName = $params['paymentmethod'];

    $url = 'https://secure.paylane.com/order/cart.html';

    $postfields = array();
    $postfields['amount'] = $amount;
    $postfields['currency'] = $currencyCode;
    $postfields['merchant_id'] = $merchantId;
    $postfields['description'] = 'P'.$invoiceId;
    $postfields['transaction_description'] = $description;
    $postfields['transaction_type'] = 'S';
    $postfields['back_url'] = $systemUrl . '/modules/gateways/callback/' . $moduleName . '.php?returnUrl=' . urlencode($returnUrl);
    $postfields['language'] = 'en'; // TODO w takim samym jak jest www ustawione
    $postfields['hash'] = SHA1($hashSalt . "|" . $postfields['description'] . "|" . $postfields['amount'] . "|" . $postfields['currency'] . "|" . $postfields['transaction_type']);
    $postfields['customer_name'] = $fullname;
    $postfields['customer_email'] = $email;
    $postfields['customer_address'] = $address1.' '.$address2;
    $postfields['customer_zip'] = $postcode;
    $postfields['customer_city'] = $city;
    $postfields['customer_state'] = $state;
    $postfields['customer_country'] = $country;

    $htmlOutput = '<form method="post" action="' . $url . '">';
    foreach ($postfields as $k => $v) {
        $htmlOutput .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
    }
    $htmlOutput .= '<input type="submit" value="' . $langPayNow . '" />';
    $htmlOutput .= '</form>';

    return $htmlOutput;
}

function paylanesecureform_refund($params)
{
    $loginApi = $params['loginApi'];
    $passwordApi = $params['passwordApi'];

    $transactionIdToRefund = $params['transid'];
    $refundAmount = $params['amount'];
    $reason = 'refund';

    $client = new PayLaneRestClient($loginApi, $passwordApi);

    $refundParams = array(
        'id_sale' => $transactionIdToRefund,
        'amount'  => $refundAmount,
        'reason'  => $reason,
    );
    try {
        $status = $client->refund($refundParams);
    } catch (Exception $e) {
        return array(
            'status' => 'error',
            'rawdata' => $e,
            'transid' => $refundTransactionId,
        );
    }

    return array(
        'status' => $client->isSuccess() ? 'success' : 'error',
        'rawdata' => $status,
        'transid' => $transactionIdToRefund,
    );
}

