<?php
header('Access-Control-Allow-Origin: *');
include_once('../includes/crud.php');
include_once('../includes/custom-functions.php');
include_once('../includes/variables.php');
include_once('verify-token.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES utf8");
$function = new custom_functions();
$config = $function->get_configurations();

$config = $function->get_configurations();
$time_slot_config = $function->time_slot_config();
if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}
/*
	 accesskey:90336
	 payment_request:1
	 user_id:5
	 payment_type:mobile {OR} bank
	 payment_address:9876543210 {OR} [["account_holder_name","girish"],["account_number","13062019010"],["ifsc_code","BOBDUD"],["bank","BOB"]]
	 amount_requested:100

 */

$response = array();
$accesskey = $db->escapeString($function->xss_clean($_POST['accesskey']));
if (!isset($_POST['accesskey']) || $access_key != $accesskey) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey";
    print_r(json_encode($response));
    return false;
}
if (!verify_token()) {
    return false;
}

if (isset($_POST['get_payment_requests']) && isset($_POST['user_id'])) {
    $user_id = $db->escapeString($function->xss_clean($_POST['user_id']));
    $sql = "SELECT p.*,u.name,u.email FROM payment_requests p JOIN users u ON u.id=p.user_id where p.user_id=" . $user_id . " order by id DESC";
    $db->sql($sql);
    $res = $db->getResult();
    $payment_request = $response = array();
    if (!empty($res)) {

        foreach ($res as $row) {
            $payment_request['id'] = $row['id'];
            $payment_request['user_id'] = $row['user_id'];
            $payment_request['payment_type'] = $row['payment_type'];
            if ($row['payment_type'] == 'bank') {
                $payment_request['payment_address'] = json_decode($row['payment_address']);
            } else {
                $payment_request['payment_address'] = $row['payment_address'];
            }
            $payment_request['amount_requested'] = $row['amount_requested'];
            $payment_request['remarks'] = $row['remarks'];
            $payment_request['name'] = $row['name'];
            $payment_request['email'] = $row['email'];
            $payment_request['status'] = $row['status'];
            $payment_request['date_created'] = $row['date_created'];
            $payment_requests[] = $payment_request;
        }
        $response['error'] = false;
        $response['data'] = $payment_requests;

        print_r(json_encode($response));
    } else {
        $payment_request['error'] = true;
        $payment_request['message'] = "No payment requests found!";
        print_r(json_encode($payment_request));
    }
}
if (isset($_POST['payment_request']) && isset($_POST['user_id'])) {
    $id = $db->escapeString($function->xss_clean($_POST['user_id']));
    $payment_type = $db->escapeString($function->xss_clean($_POST['payment_type']));
    $payment_address = $db->escapeString($function->xss_clean($_POST['payment_address']));
    if ($payment_address == 'bank') {
        $payment_address = json_encode($payment_address);
    } else {
        $payment_address = $db->escapeString($function->xss_clean($_POST['payment_address']));
    }
    $amount_requested = $db->escapeString($function->xss_clean($_POST['amount_requested']));
    $sql = "SELECT balance FROM `users` WHERE id=" . $id;
    $db->sql($sql);
    $res_user = $db->getResult();
    if (!empty($res_user)) {
        $balance = $res_user[0]['balance'];
        if ($amount_requested <= $balance) {
            if ($amount_requested >= $config['minimum-withdrawal-amount']) {
                $sql = "INSERT INTO payment_requests (user_id,payment_type,payment_address,amount_requested)
                        VALUES('$id', '$payment_type', '$payment_address', '$amount_requested')";
                $db->sql($sql);
                $sql = "UPDATE users SET balance = balance - $amount_requested WHERE id=" . $id;
                $db->sql($sql);
                $response['error'] = false;
                $response['message'] = "Payment Request Sent Successfully!";
                print_r(json_encode($response));
            } else {
                $response['error'] = true;
                $response['message'] = "Minimum withdrawal amount is " . $config['minimum-withdrawal-amount'];
                print_r(json_encode($response));
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Insufficient balance!";
            print_r(json_encode($response));
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Sorry user does't exists";
        print_r(json_encode($response));
    }
}


if (isset($_POST['get_wallet_transactions']) && isset($_POST['user_id'])) {
    $user_id = $db->escapeString($function->xss_clean($_POST['user_id']));
    $offset = (isset($_POST['offset']) && !empty($_POST['offset'])) ? $db->escapeString($function->xss_clean($_POST['offset'])) : 0;
    $limit = (isset($_POST['limit']) && !empty($_POST['limit'])) ? $db->escapeString($function->xss_clean($_POST['limit'])) : 20;
    $sql = "SELECT w.*,u.name,u.email FROM wallet_transactions w JOIN users u ON u.id=w.user_id where w.user_id=" . $user_id . " order by id DESC LIMIT $offset,$limit ";
    $db->sql($sql);
    $res = $db->getResult();
    $wallet_transaction = $response = array();
    if (!empty($res)) {
        foreach ($res as $row) {
            $wallet_transaction['id'] = $row['id'];
            $wallet_transaction['user_id'] = $row['user_id'];
            $wallet_transaction['name'] = $row['name'];
            $wallet_transaction['email'] = $row['email'];
            $wallet_transaction['type'] = $row['type'];
            $wallet_transaction['amount'] = $row['amount'];
            $wallet_transaction['message'] = $row['message'];
            $wallet_transaction['status'] = $row['status'];
            $wallet_transactions[] = $wallet_transaction;
        }
        $response['error'] = false;
        $response['data'] = $wallet_transactions;
        print_r(json_encode($response));
    } else {
        $payment_request['error'] = true;
        $payment_request['message'] = "No wallet transactions found!";
        print_r(json_encode($payment_request));
    }
}

if (isset($_POST['verify_paystack_transaction']) && isset($_POST['reference'])) {
    $reference = $db->escapeString($function->xss_clean($_POST['reference']));
    $email = $db->escapeString($function->xss_clean($_POST['email']));
    $amount = $db->escapeString($function->xss_clean($_POST['amount']));
    if (empty($reference) || empty($email) || empty($amount) || !is_numeric($amount)) {
        $response['error'] = true;
        $response['message'] = "Invalid data supplied please pass all the fields.";
        $response['status'] = "failed";
        print_r(json_encode($response));
        return false;
    }
    $response = $function->verify_paystack_transaction($reference, $email, $amount);
    print_r(json_encode($response));
}
