<?php
include_once '../includes/crud.php';
$db = new Database();
$db->connect();
include_once '../includes/custom-functions.php';

$function = new custom_functions();
include_once 'midtrans.php';
$md = new Midtrans();
$notification = json_decode(file_get_contents("php://input"), true);
$order_id = (isset($notification['order_id'])) ? $notification['order_id'] : "";
$status_code = (isset($notification['status_code'])) ? $notification['status_code'] : "";
$gross_amount = (isset($notification['gross_amount'])) ? $notification['gross_amount'] : "";
$transaction_status =  (isset($notification['transaction_status'])) ? $notification['transaction_status'] : "";
$fraud_status = (isset($notification['fraud_status'])) ? $notification['fraud_status'] : "";
$key = $md->get_credentials();
$server_key = $key['server_key'];
if (!empty($order_id) && !empty($status_code) && !empty($gross_amount) && !empty($server_key)) {
    $hashed = hash("sha512", $order_id . $status_code . $gross_amount . $server_key);
    if ($notification['signature_key'] != $hashed) {
        $response['error'] = true;
        $response['message'] = "Signature key is not matched.";
        echo json_encode($response);
        return false;
        exit();
    }
}
if (strpos($order_id, "wallet-refill-user") !== false) {
    $data1 = explode("-", $order_id);
    if (isset($data1[3]) && is_numeric($data1[3]) && !empty($data1[3] && $data1[3] != '')) {
        $user_id = $data1[3];
    } else {
        $user_id = 0;
    }
} else {
    $res = $function->get_data(0, 'id = "' . $order_id . '"', 'orders');
    if (!empty($res) && isset($res[0]['order_id']) && is_numeric($res[0]['order_id'])) {
        $db_order_id = $res[0]['order_id'];
        if ($notification['order_id'] != $db_order_id) {
            $response['error'] = true;
            $response['message'] = "Order id is not placed in database.";
            file_put_contents('data.txt', "Order id is not placed in database.", FILE_APPEND);
            echo json_encode($response);
            return false;
        }
    }
}

$type = $notification['payment_type'];

if ($transaction_status == 'capture') {
    if ($type == 'credit_card') {
        if ($fraud_status == 'challenge') {
            $response['error'] = false;
            $response['transaction_status'] = $transaction->fraud_status;
            $response['message'] = "Transaction order_id: " . $order_id . " is challenged by FDS";
            file_put_contents('data.txt', "Transaction order_id: " . $order_id . " is challenged by FDS", FILE_APPEND);
            echo json_encode($response);
            return false;
        } else {
            if (strpos($order_id, "wallet-refill-user") !== false) {
                $wallet_result = $md->add_wallet_balance($order_id ,$user_id, $gross_amount, "credit", "Wallet refill successful");
                $response['error'] = false;
                $response['transaction_status'] = $transaction->transaction_status;
                $response['message'] = "Transaction successfully done using " . $type;
                file_put_contents('data.txt', "Transaction successfully done using " . $type, FILE_APPEND);
                echo json_encode($response);
                return false;
            }else{
                $order_status_result = $function->update_order_status($order_id, 'received', 0);
                $response['error'] = false;
                $response['transaction_status'] = $transaction->transaction_status;
                $response['message'] = "Transaction successfully done using " . $type;
                file_put_contents('data.txt', "Transaction successfully done using " . $type, FILE_APPEND);
                echo json_encode($response);
                return false;
            }
        }
    }
}
//  else if ($transaction->transaction_status == 'settlement') {
//     // TODO set payment status in merchant's database to 'Settlement'

//     echo "Transaction order_id: " . $order_id . " successfully transfered using " . $type;
//     file_put_contents('data.txt', "Transaction order_id: " . $order_id . " successfully transfered using " . $type, FILE_APPEND);
// } 
else if ($transaction_status == 'pending') {
    // TODO set payment status in merchant's database to 'Pending'
    $response['error'] = false;
    $response['message'] = "Waiting customer to finish transaction using " . $type;
    $response['transaction_status'] = $transaction->transaction_status;
    file_put_contents('data.txt', "Waiting customer to finish transaction order_id: using " . $type, FILE_APPEND);
    echo json_encode($response);
    return false;
} else if ($transaction_status == 'deny') {
    // TODO set payment status in merchant's database to 'Denied'
    $response['error'] = true;
    $response['message'] = "Payment using " . $type . " for transaction order_id: " . $order_id . " is denied. And" . $notification['status_message'];
    $response['transaction_status'] = $transaction->transaction_status;
    file_put_contents('data.txt',  "Payment using " . $type . " for transaction order_id: " . $order_id . " is denied.", FILE_APPEND);
    echo json_encode($response);
    return false;
} else if ($transaction_status == 'expire') {
    // TODO set payment status in merchant's database to 'expire'
    $response['error'] = true;
    $response['message'] = "Payment using " . $type . " for transaction order_id: " . $order_id . " is expired.";
    $response['transaction_status'] = $transaction->transaction_status;
    file_put_contents('data.txt', "Payment using " . $type . " for transaction order_id: " . $order_id . " is expired.", FILE_APPEND);
    echo json_encode($response);
    return false;
} else if ($transaction_status == 'cancel') {
    // TODO set payment status in merchant's database to 'Denied'
    $response['error'] = true;
    $response['transaction_status'] = $transaction->transaction_status;
    $response['message'] = "Payment using " . $type . " for transaction order_id: " . $order_id . " is canceled.";
    file_put_contents('data.txt', "Payment using " . $type . " for transaction order_id: " . $order_id . " is canceled.", FILE_APPEND);
    echo json_encode($response);
    return false;
}
