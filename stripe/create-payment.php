<?php
header('Access-Control-Allow-Origin: *');
include_once '../includes/crud.php';
$db = new Database();
$db->connect();
include_once 'stripe.php';
$st = new Stripe();
include_once '../includes/custom-functions.php';
$function = new custom_functions();

$credentials = $st->get_credentials();
$request_body = file_get_contents('php://input');
$post_data = json_decode($request_body, true);

/* 
    accesskey:90336
    name:username
    address_line1:jubeli_circle {optional}
    postal_code:12345
    city:bhuj
    amount:123456
    order_id:12345
*/

$access_key = 90336;
if (isset($_POST['accesskey']) && $_POST['accesskey'] == $access_key) {
    if (empty($_POST['name']) || empty($_POST['postal_code']) || empty($_POST['city']) || empty($_POST['amount']) || empty($_POST['order_id'])) {
        $response['error'] = true;
        $response['message'] = "Some data is missing";
        echo json_encode($response);
        return false;
    }

    $order_id = $db->escapeString($function->xss_clean($_POST['order_id']));
    $name = $db->escapeString($function->xss_clean($_POST['name']));
    $line1 = (isset($_POST['address_line1']) && $_POST['address_line1'] != '') ? $db->escapeString($function->xss_clean($_POST['address_line1'])) : "address";
    $postal_code = $db->escapeString($function->xss_clean($_POST['postal_code']));
    $city = $db->escapeString($function->xss_clean($_POST['city']));
    $amount = $db->escapeString($function->xss_clean($_POST['amount']));
} else {
    $response['error'] = true;
    $response['message'] = "Invalid Access Key";
    echo json_encode($response);
}

// $name = $db->escapeString($function->xss_clean($post_data['name']));
// $line1 =  (isset($_POST['address_line1']) && $_POST['address_line1'] != '') ? $db->escapeString($function->xss_clean($_POST['address_line1'])) : "address";
// $postal_code = $db->escapeString($function->xss_clean($post_data['postal_code']));
// $city = $db->escapeString($function->xss_clean($post_data['city']));

// $amount = $db->escapeString($function->xss_clean($post_data['amount']));

$data = array('name' => $name, 'line1' => $line1, 'postal_code' => $postal_code, 'city' => $city);
$customer = $st->create_customer($data);
$c_data = array('customer' => $customer['id'], 'amount' => $amount*100,"metadata" => ["order_id" => $order_id]);
$payment_intent = $st->create_payment_intent($c_data);
$output = [
    'publishableKey' => $credentials['publishable_key'],
    'clientSecret' => $payment_intent['client_secret']
];
if($payment_intent['client_secret'] == ""){
    echo json_encode($payment_intent);
}else{
    echo json_encode($output);
}
