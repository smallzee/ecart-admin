<?php


include_once '../includes/crud.php';
$db = new Database();
$db->connect();
include_once 'stripe.php';
$st = new Stripe();
include_once '../includes/custom-functions.php';

$function = new custom_functions();
$credentials = $st->get_credentials();
$request_body = file_get_contents('php://input');
$event = json_decode($request_body, FALSE);

if (!empty($event->data->object->metadata)) {
    $order_id = $event->data->object->metadata->order_id;
    $amount = $event->data->object->amount;
    $balance_transaction = $event->data->object->balance_transaction;
    
} else {
    $order_id = 0;
    $amount = 0;
    $balance_transaction = 0;
}
if (strpos($order_id, "wallet-refill-user") !== false) {
    $data1 = explode("-", $order_id);
    if (isset($data1[3]) && is_numeric($data1[3]) && !empty($data1[3] && $data1[3] != '')) {
        $user_id = $data1[3];
    }else{
        $user_id = 0;
    }
}


$result = $st->construct_event($request_body, $_SERVER['HTTP_STRIPE_SIGNATURE'], $credentials['webhook_key']);

if ($result == "Matched") {
    if ($event->type == 'charge.succeeded') {
        if (strpos($order_id, "wallet-refill-user") !== false) {
                $wallet_result = $st->add_wallet_balance($order_id, $user_id, $amount/100, "credit", "Wallet refill successful");

                $response['error'] = false;
                $response['transaction_status'] = $event->type;
                $response['message'] = "Wallet recharged successfully!";
                file_put_contents('webhook_log.txt', "Transaction successfully done ", FILE_APPEND);
                echo json_encode($response);
                return false;
        } else {
            $order_status_result = $function->update_order_status($order_id, 'received', 0);
            $response['error'] = false;
            $response['transaction_status'] = $event->type;
            $response['message'] = "Transaction successfully done";
            file_put_contents('webhook_log.txt', "Transaction successfully done ", FILE_APPEND);
            echo json_encode($response);
            return false;
        }
    }
    if ($event->type == 'charge.failed') {
        $response['error'] = true;
        $response['transaction_status'] = $event->type;
        $response['message'] = "Transaction is failed. ";
        file_put_contents('webhook_log.txt', "Transaction is failed. ", FILE_APPEND);
        echo json_encode($response);
        return false;
    }
    if ($event->type == 'charge.pending') {
        $response['error'] = false;
        $response['transaction_status'] = $event->type;
        $response['message'] = "Waiting customer to finish transaction ";
        file_put_contents('webhook_log.txt', "Waiting customer to finish transaction ", FILE_APPEND);
        echo json_encode($response);
        return false;
    }
    if ($event->type == 'charge.expired') {
        $response['error'] = true;
        $response['transaction_status'] = $event->type;
        $response['message'] = "Transaction is expired.";
        file_put_contents('webhook_log.txt', "Transaction is expired.", FILE_APPEND);
        echo json_encode($response);
        return false;
    }
    if ($event->type == 'charge.refunded') {
        $response['error'] = true;
        $response['transaction_status'] = $event->type;
        $response['message'] = "Transaction is refunded.";
        file_put_contents('webhook_log.txt', "Transaction is refunded.", FILE_APPEND);
        echo json_encode($response);
        return false;
    }
} else {
    print_r(json_encode($result));
    file_put_contents('webhook_log.txt', "not done", FILE_APPEND);
    return false;
}
