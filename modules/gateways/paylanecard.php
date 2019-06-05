<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

include_once(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

function paylanecard_MetaData()
{
    return array(
        'DisplayName' => 'PayLane Card',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => false,
        'TokenisedStorage' => false,
    );
}

function paylanecard_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'PayLane Card',
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
        )
    );
}

function paylanecard_capture($params)
{
    $loginApi = $params['loginApi'];
    $passwordApi = $params['passwordApi'];

    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    $cardNumber = $params['cardnum'];
    $cardExpiry = $params['cardexp'];
    $cardExpirationMonth = substr($cardExpiry, 0, 2);
    $cardExpirationYear = strval((2000 + intval(substr($cardExpiry, 2))));
    $cardCvv = $params['cccvv'];

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

    $client = new PayLaneRestClient($loginApi, $passwordApi);

    $cardParams = array(
        'sale' => array(
            'amount' => $amount,
            'currency' => $currencyCode,
            'description' => $description,
        ),
        'customer' => array(
            'name' => $fullname,
            'email' => $email,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'address' => array (
                'street_house' => $address1 . ' ' . $address2,
                'city' => $city,
                'state' => $state,
                'zip' => $postcode,
                'country_code' => $country,
            ),
        ),
        'card' => array(
            "card_number" => $cardNumber,
            "expiration_month" => $cardExpirationMonth,
            "expiration_year" => $cardExpirationYear,
            "name_on_card" => $fullname,
            "card_code" => $cardCvv,
        ),
    );

    try {
        $status = $client->cardSale($cardParams);
    } catch (Exception $e) {
        return array(
            'status' => 'error',
            'rawdata' => $e,
            'transid' => null,
        );
    }

    return array(
        'status' => $client->isSuccess() ? 'success' : 'error',
        'rawdata' => $status,
        'transid' => $status['id_sale'],
    );

}

function paylanecard_refund($params)
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
