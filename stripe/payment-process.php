<?php
include_once '../includes/crud.php';
$db = new Database();
$db->connect();
include_once '../includes/custom-functions.php';

$function = new custom_functions();
$json_data = substr_replace($_COOKIE['client1'], "", strpos($_COOKIE['client1'], "&order_id"));
$order_id = substr($_COOKIE['client1'], strpos($_COOKIE['client1'], "&order_id="));
$data = json_decode($json_data, true);
$order_id1 = substr($order_id, 10);
if ($data['status'] == "succeeded") {
    if ($data['payment_method_types'][0] == "card") {
        $order_status_result = $function->update_order_status(substr($order_id, 10), 'received', 0);
        $response['error'] = false;
        $response['transaction_status'] = $data['status'];
        $response['message'] = "Transaction successfully done";
        $response['order_id'] = substr($order_id, 10);
        file_put_contents('webhook_log.txt', "Transaction successfully done ", FILE_APPEND);
        echo json_encode($response);
        return false;
    }
}
if ($data['status'] == "pending") {
    $response['error'] = false;
    $response['transaction_status'] = $data['status'];
    $response['message'] = "Waiting customer to finish transaction ";
    $response['order_id'] = substr($order_id, 10);
    file_put_contents('webhook_log.txt', "Waiting customer to finish transaction ", FILE_APPEND);
    echo json_encode($response);
    return false;
}
if ($data['status'] == "failed") {
    $response['error'] = true;
    $response['transaction_status'] = $data['status'];
    $response['message'] = "Transaction is failed.";
    $response['order_id'] = substr($order_id, 10);
    file_put_contents('webhook_log.txt', "Transaction is failed.", FILE_APPEND);
    echo json_encode($response);
    return false;
}
if ($data['status'] == 'expired') {
    $response['error'] = true;
    $response['transaction_status'] = $data['status'];
    $response['message'] = "Transaction is expired.";
    $response['order_id'] = substr($order_id, 10);
    file_put_contents('webhook_log.txt', "Transaction is expired.", FILE_APPEND);
    echo json_encode($response);
    return false;
}
if ($data['status'] == 'refunded') {
    $response['error'] = true;
    $response['transaction_status'] = $data['status'];
    $response['message'] = "Transaction is refunded.";
    $response['order_id'] = substr($order_id, 10);
    file_put_contents('webhook_log.txt', "Transaction is refunded.", FILE_APPEND);
    echo json_encode($response);
    return false;
}
// setcookie("client1", "", time() - 3600);