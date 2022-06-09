<?php

/*
    import checksum generation utility
    You can get this utility from https://developer.paytm.com/docs/checksum/
 */
include_once '../includes/crud.php';
include_once '../includes/custom-functions.php';
include_once('../includes/variables.php');

$db = new Database();
$db->connect();
$fn = new custom_functions();
require_once("PaytmChecksum.php");

/* initialize an array */
$paytmParams = array();

/* body parameters */
$access_key = 90336;
if (isset($_POST['accesskey']) && $_POST['accesskey'] == $access_key) {
    $settings = $fn->get_settings('payment_methods', true);
    $paytmParams["body"] = array(
        /* Find your MID in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys */
        "mid" => $settings['paytm_merchant_id'],
        /* Enter your order id which needs to be check status for */
        "orderId" => $fn->xss_clean($_POST['orderId']),
    );

    /*
        Generate checksum by parameters we have in body
        Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
    */
    $checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"]), $settings['paytm_merchant_key']);

    /* head parameters */
    $paytmParams["head"] = array(
        /* put generated checksum value here */
        "signature"    => $checksum
    );

    /* prepare JSON string for request */
    $post_data = json_encode($paytmParams);

    /* for Staging */
    if ($settings['paytm_mode'] == "sandbox") {
        $url = "https://securegw-stage.paytm.in/v3/order/status";
    }

    /* for Production */
    if ($settings['paytm_mode'] == "production") {
        $url = "https://securegw.paytm.in/v3/order/status";
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $response = curl_exec($ch);
    print_r($response);
    return false;
} else {
    $response['error'] = true;
    $response['message'] = "Invalid Access Key";
    echo json_encode($response);
}
