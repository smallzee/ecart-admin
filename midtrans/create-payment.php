<?php
include_once '../includes/crud.php';
$db = new Database();
$db->connect();
include_once 'midtrans.php';
$md = new Midtrans();
include_once '../includes/custom-functions.php';

$function = new custom_functions();
$access_key = 90336;
if (isset($_POST['accesskey']) && $_POST['accesskey'] == $access_key) {
    $order_id = $db->escapeString($function->xss_clean($_POST['order_id']));
    $gross_amount = $db->escapeString($function->xss_clean($_POST['gross_amount']));
    $result = $md->create_transaction($order_id, $gross_amount);
    $result1 = json_decode($result['body'], true);
    if ($result['http_code'] != 201) {
       
        $response['error'] = true;
        $response['message'] = $result1['error_messages'][0];
        print_r(json_encode($response));
    } else {
        $array = json_decode($result['body']);
        if (array_key_exists("redirect_url", $array)) {
            include_once 'payment-process.php';
            $response['error'] = false;
            $response['message'] = "Redirect url fetched successfully.";
            $response['data'] = $result1;
            print_r(json_encode($response));
        } else {
            $response['error'] = false;
            $response['message'] = "Something went wrong.";
            print_r(json_encode($response));
        }
    }
} else {
    $response['error'] = true;
    $response['message'] = "Invalid Access Key";
    echo json_encode($response);
}
