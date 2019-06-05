<?php
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

$gatewayModuleName = basename(__FILE__, '.php');

$gatewayParams = getGatewayVariables($gatewayModuleName);

if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

$success = $status === 'PERFORMED';

$status = $_POST['status'];
$description = $_POST['description'];
preg_match('/^P([0-9]+)/',$description ,$matches);
$invoiceId = intval($matches[1]);
$amount = $_POST['amount'];
$paymentFee = null;
$currency = $_POST['currency'];
$hash = $_POST['hash'];
$idAuthorization = $_POST['id_authorization'];
$transactionId = $_POST["id_sale"];
$idError = $_POST['id_error'];
$errorCode = $_POST['error_code'];
$errorText = $_POST['error_text'];
$fraudScore = $_POST['fraud_score'];
$avsResult = $_POST['avs_result'];


$transactionStatus = $success ? 'Success' : 'Failure';

$hashSalt = $gatewayParams['hashSalt'];

if ($hash != SHA1($hashSalt . "|" . $status . "|" . $description . "|" . $amount . "|" . $currency . "|" . $transactionId)) {
    $transactionStatus = 'Hash Verification Failure';
    $success = false;
}

$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

checkCbTransID($transactionId);

logTransaction($gatewayParams['name'], $_POST, $transactionStatus);

if ($success) {
    addInvoicePayment(
        $invoiceId,
        $transactionId,
        $amount,
        $paymentFee,
        $gatewayModuleName
    );
}

if ($_GET['returnUrl']) {
    header('Location: ' . urldecode($_GET['returnUrl']));
}
