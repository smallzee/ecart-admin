<?php
session_start();
include '../includes/crud.php';
include_once('../includes/variables.php');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Access-Control-Allow-Origin: *');
// date_default_timezone_set('Asia/Kolkata');

include_once('verify-token.php');
$db = new Database();
$db->connect();
include_once('../includes/custom-functions.php');
$fn = new custom_functions;
$config = $fn->get_configurations();
$time_slot_config = $fn->time_slot_config();
if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}
$response = array();


if (isset($_POST['ajaxCall']) && !empty($_POST['ajaxCall'])) {
    $accesskey = "90336";
} else {
    if (!isset($_POST['accesskey'])) {
        $response['error'] = true;
        $response['message'] = "Access key is invalid or not passed!";
        print_r(json_encode($response));
        return false;
    }
    $accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));
    if ($access_key != $accesskey) {
        $response['error'] = true;
        $response['message'] = "invalid accesskey!";
        print_r(json_encode($response));
        return false;
    }
}



/*
get-user-transactions.php
    get_user_transactions:1
    user_id:3
    type:transactions/wallet_transactions
    offset:0 {optional}
    limit:5 {optional}
*/
if ((isset($_POST['get_user_transactions'])) && ($_POST['get_user_transactions'] == 1)) {
    if (!verify_token()) {
        return false;
    }
    $user_id  = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";
    $type  = (isset($_POST['type']) && !empty($_POST['type'])) ? $db->escapeString($fn->xss_clean($_POST['type'])) : "";
    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : 10;
    $offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) ? $db->escapeString($fn->xss_clean($_POST['offset'])) : 0;

    if (!empty($user_id) && !empty($type)) {
        $sql = "SELECT count(id) as total from $type where user_id=" . $user_id;
        $db->sql($sql);
        $total = $db->getResult();
        $sql = "select * from $type where user_id=" . $user_id . " ORDER BY date_created DESC LIMIT $offset,$limit";
        $db->sql($sql);
        $res = $db->getResult();
        $data = array();
        if (!empty($res)) {
            $response['error'] = false;
            $response['total'] = $total[0]['total'];
            if ($type == 'transactions') {
                $response['data'] = $res;
            } else {
                $response['data'] = $res;
                for ($i = 0; $i < count($response['data']); $i++) {
                    $response['data'][$i]['status'] = $response['data'][$i]['type'];
                    $response['data'][$i]['message'] = $response['data'][$i]['message'] == 'Used against Order Placement' ? 'Order Successfully Placed' : $response['data'][$i]['message'];
                }
            }
        } else {
            $response['error'] = true;
            $response['message'] = "No data found!";
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Please pass all the fields!';
    }

    print_r(json_encode($response));
    return false;
}


/*
add_wallet_balance
    add_wallet_balance:1
    user_id:3
    amount:100
    type:credit
    message: transaction by user {optional}
*/

if (isset($_POST['add_wallet_balance']) && isset($_POST['add_wallet_balance'])) {
    if (isset($_POST['user_id']) && !empty($_POST['user_id']) && is_numeric($_POST['user_id'])) {
        $user_id = $db->escapeString($fn->xss_clean($_POST['user_id']));
        $order_id = (isset($_POST['order_id']) && !empty($_POST['order_id'])) ? $db->escapeString($fn->xss_clean($_POST['order_id'])) : "";
        $amount = $db->escapeString($fn->xss_clean($_POST['amount']));
        $type = $db->escapeString($fn->xss_clean($_POST['type']));
        $message = !empty(trim($_POST['message'])) ? $db->escapeString(trim($fn->xss_clean($_POST['message']))) : 'Transaction by user';
        $sql_exist = "SELECT id from users where id = $user_id";
        $db->sql($sql_exist);
        $user_data = $db->getResult();
        if (!empty($user_data)) {
            $balance = $fn->get_wallet_balance($user_id);
            $new_balance = ($type == 'credit') ? $balance + $amount : $balance - $amount;
            $fn->update_wallet_balance($new_balance, $user_id);
            if ($fn->add_wallet_transaction($order_id, $user_id, $type, $amount, $message)) {
                $n_balance = $fn->get_wallet_balance($user_id);
                $sql = "select * from wallet_transactions where user_id=" . $user_id . " ORDER BY date_created DESC";
                $db->sql($sql);
                $res1 = $db->getResult();

                $response['error'] = false;
                $response['message'] = "Wallet recharged successfully!";
                $response['new_balance'] = $n_balance;
                $response['data'] = $res1[0];
            } else {
                $response['error'] = true;
                $response['message'] = "Wallet recharged failed!";
            }
        } else {
            $response['error'] = true;
            $response['message'] = "User does not exist";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Invalid User Id";
    }
    print_r(json_encode($response));
    return false;
}
