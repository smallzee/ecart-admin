<?php
include_once '../includes/crud.php';
$db = new Database();
$db->connect();
include_once '../includes/custom-functions.php';

$function = new custom_functions();
include_once 'midtrans.php';
$md = new Midtrans();
if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
    $order_id = $db->escapeString($function->xss_clean($_GET['order_id']));
    $transaction_status = $db->escapeString($function->xss_clean($_GET['transaction_status']));
    $fraud_status = (isset($_GET['fraud_status'])) ? $db->escapeString($function->xss_clean($_GET['fraud_status'])) : "";

    $transaction_data = $md->get_transaction_status($order_id);
    $transaction = json_decode($transaction_data['body']);
    if ($order_id != $transaction->order_id) {
        $response['error'] = true;
        $response['message'] = "Order id is not matched with transaction order id.";
        echo json_encode($response);
        return false;
    }
    $res = $function->get_data(0, 'id = "' . $order_id . '"', 'orders');
    if (!empty($res) && isset($res[0]['id']) && is_numeric($res[0]['id'])) {
        $db_order_id = $res[0]['id'];
        if ($transaction->order_id != $db_order_id) {
            $response['error'] = true;
            $response['message'] = "Order id is not placed in database.";
            file_put_contents('data.txt', "Order id is not placed in database.", FILE_APPEND);
            echo json_encode($response);
            return false;
        }
        /* cancel order */
    }
    $type = $transaction->payment_type;

    if ($transaction->transaction_status == 'capture') {
        // For credit card transaction, we need to check whether transaction is challenge by FDS or not
        if ($type == 'credit_card') {
            if ($transaction->fraud_status == 'challenge') {
                // TODO set payment status in merchant's database to 'Challenge by FDS'
                // TODO merchant should decide whether this transaction is authorized or not in MAP
                $response['error'] = false;
                $response['transaction_status'] = $transaction->fraud_status;
                $response['message'] = "Transaction order_id: " . $order_id . " is challenged by FDS";
                file_put_contents('data.txt', "Transaction order_id: " . $order_id . " is challenged by FDS", FILE_APPEND);
                echo json_encode($response);
                return false;
            } else {
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
    //  else if ($transaction->transaction_status == 'settlement') {
    //     // TODO set payment status in merchant's database to 'Settlement'

    //     echo "Transaction order_id: " . $order_id . " successfully transfered using " . $type;
    //     file_put_contents('data.txt', "Transaction order_id: " . $order_id . " successfully transfered using " . $type, FILE_APPEND);
    // } 
    else if ($transaction->transaction_status == 'pending') {
        // TODO set payment status in merchant's database to 'Pending'
        $response['error'] = false;
        $response['transaction_status'] = $transaction->transaction_status;
        $response['message'] = "Waiting customer to finish transaction order_id: " . $order_id . " using " . $type;
        file_put_contents('data.txt', "Waiting customer to finish transaction order_id: " . $order_id . " using " . $type, FILE_APPEND);
        echo json_encode($response);
        return false;
    } else if ($transaction->transaction_status == 'deny') {
        // TODO set payment status in merchant's database to 'Denied'

        $response['error'] = true;
        $response['transaction_status'] = $transaction->transaction_status;
        $response['message'] = "Payment using " . $type . " for transaction order_id: " . $order_id . " is denied. And" . $transaction->status_message;
        file_put_contents('data.txt',  "Payment using " . $type . " for transaction order_id: " . $order_id . " is denied.", FILE_APPEND);
        echo json_encode($response);
        return false;
    } else if ($transaction->transaction_status == 'expire') {
        // TODO set payment status in merchant's database to 'expire'

        $response['error'] = true;
        $response['transaction_status'] = $transaction->transaction_status;
        $response['message'] = "Payment using " . $type . " for transaction order_id: " . $order_id . " is expired.";
        file_put_contents('data.txt', "Payment using " . $type . " for transaction order_id: " . $order_id . " is expired.", FILE_APPEND);
        echo json_encode($response);
        return false;
    } else if ($transaction->transaction_status == 'cancel') {
        // TODO set payment status in merchant's database to 'Denied'

        $response['error'] = true;
        $response['transaction_status'] = $transaction->transaction_status;
        $response['message'] = "Payment using " . $type . " for transaction order_id: " . $order_id . " is canceled.";
        file_put_contents('data.txt', "Payment using " . $type . " for transaction order_id: " . $order_id . " is canceled.", FILE_APPEND);
        echo json_encode($response);
        return false;
    }
}
