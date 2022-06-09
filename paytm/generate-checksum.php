<?php

include_once '../includes/crud.php';
include_once '../includes/custom-functions.php';
include_once('../includes/variables.php');

$db = new Database();
$db->connect();
$fn = new custom_functions();

/* import checksum generation utility */
require_once("PaytmChecksum.php");

/* initialize an array */
$paytm_params = array();
$paramList = array();
$access_key = 90336; 
if(isset($_POST['accesskey']) && $_POST['accesskey'] == $access_key ){
    $settings = $fn->get_settings('payment_methods',true);
    $data['merchant_key'] = $settings['paytm_merchant_key'];
    $data['merchant_id'] = $settings['paytm_merchant_id'];
    /* add parameters in Array */
    
    $paytm_params["MID"] = $settings['paytm_merchant_id'];

    $paytm_params["ORDER_ID"] = $fn->xss_clean($_POST['ORDER_ID']);
    $paytm_params["CUST_ID"] = $fn->xss_clean($_POST['CUST_ID']);
    $paytm_params["INDUSTRY_TYPE_ID"] = $fn->xss_clean($_POST['INDUSTRY_TYPE_ID']);
    $paytm_params["CHANNEL_ID"] = $fn->xss_clean($_POST['CHANNEL_ID']);
    $paytm_params["TXN_AMOUNT"] = $fn->xss_clean($_POST['TXN_AMOUNT']);
    $paytm_params["WEBSITE"] = $fn->xss_clean($_POST['WEBSITE']);
    $paytm_params["CALLBACK_URL"] = "https://securegw.paytm.in/theia/paytmCallback?ORDER_ID=".$paytm_params["ORDER_ID"];

    /**
    * Generate checksum by parameters we have
    * Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
    */
    $paytm_checksum = PaytmChecksum::generateSignature($paytm_params, $settings['paytm_merchant_key']);
    // echo sprintf("generateSignature Returns: %s\n", $paytm_checksum);
    if (!empty($paytm_checksum)) {
        $response['error'] = false;
        $response['message'] = "Data Retrived Successfully...!";
        $response['order id'] = $paytm_params["ORDER_ID"];
        $response['data'] = $paytm_params;
        $response['signature'] = $paytm_checksum;
        print_r(json_encode($response));
        return false;
    }else{
        $response['error'] = true;
        $response['message'] = "Data not found!";
        print_r(json_encode($response));
        return false;
    }
}else {
    $response['error'] = true;
    $response['message'] = "Invalid Access Key";
    echo json_encode($response);
}

?>