<?php
session_start();
include '../includes/crud.php';
include_once('../includes/variables.php');
include_once('../includes/custom-functions.php');


header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Access-Control-Allow-Origin: *');
// date_default_timezone_set('Asia/Kolkata');
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


include_once('verify-token.php');
$db = new Database();
$db->connect();
$response = array();

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

if (!verify_token()) {
    return false;
}
/*
1.send_request
    accesskey:90336
    send_request:1
    type:user/delivery_boy
    type_id:3
    amount:1000
    message:Message {optional}
*/
if ((isset($_POST['send_request'])) && ($_POST['send_request'] == 1)) {
    $type = (isset($_POST['type']) && !empty($_POST['type'])) ? $db->escapeString($fn->xss_clean($_POST['type'])) : "";
    $type_id = (isset($_POST['type_id']) && !empty($_POST['type_id'])) ? $db->escapeString($fn->xss_clean($_POST['type_id'])) : "";
    $amount  = (isset($_POST['amount']) && !empty($_POST['amount'])) ? $db->escapeString($fn->xss_clean($_POST['amount'])) : "";
    $message = (isset($_POST['message']) && !empty($_POST['message'])) ? $db->escapeString($fn->xss_clean($_POST['message'])) : "";
    $type1 = $type == 'user' ? 'user' : 'delivery boy';
    if (!empty($type) && !empty($type_id) && !empty($amount)) {
        // check if such user or delivery boy exists or not
        if ($fn->is_user_or_dboy_exists($type, $type_id)) {
            // checking if balance is greater than amount requested or not 
            $balance = $fn->get_user_or_delivery_boy_balance($type, $type_id);
            if ($balance >= $amount) {
                // Debit amount requeted
                $new_balance =  $balance - $amount;
                if ($fn->debit_balance($type, $type_id, $new_balance)) {
                    // store wallet transaction
                    if ($type == 'delivery_boy') {
                        $sql = "INSERT INTO `fund_transfers` (`delivery_boy_id`,`type`,`amount`,`opening_balance`,`closing_balance`,`status`,`message`) VALUES ('" . $type_id . "','debit','" . $amount . "','" . $balance . "','" . $new_balance . "','SUCCESS','Balance debited against withdrawal request.')";
                        $db->sql($sql);
                    }
                    if ($type == 'user') {
                        $fn->add_wallet_transaction($order_id = "", $type_id, 'debit', $amount, 'Balance debited against withdrawal request.');
                    }
                    // store withdrawal request
                    if ($fn->store_withdrawal_request($type, $type_id, $amount, $message)) {
                        $response['error'] = false;
                        $response['message'] = 'Withdrawal request accepted successfully!please wait for confirmation.';
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'Something went wrong please try again later!';
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = 'Something went wrong please try again later!';
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'Insufficient balance';
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'No such ' . $type1 . ' exists';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Please pass all the fields!';
    }

    print_r(json_encode($response));
    return false;
}

/*
2.get_requests
    accesskey:90336
    get_requests:1
    type:user/delivery_boy
    type_id:3
    offset:0 {optional}
    limit:5 {optional}

*/

if ((isset($_POST['get_requests'])) && ($_POST['get_requests'] == 1)) {
    $type  = (isset($_POST['type']) && !empty($_POST['type'])) ? $db->escapeString($fn->xss_clean($_POST['type'])) : "";
    $type_id = (isset($_POST['type_id']) && !empty($_POST['type_id'])) ? $db->escapeString($fn->xss_clean($_POST['type_id'])) : "";
    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : 10;
    $offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) ? $db->escapeString($fn->xss_clean($_POST['offset'])) : 0;
    if (!empty($type) && !empty($type_id)) {
        $result = $fn->is_records_exists($type, $type_id, $offset, $limit);
        if (!empty($result)) {
            /* if records found return data */
            $sql = "SELECT count(id) as total from withdrawal_requests where `type` = '" . $type . "' AND `type_id` = " . $type_id;
            $db->sql($sql);
            $total = $db->getResult();
            $response['error'] = false;
            $response['total'] = $total[0]['total'];
            $response['data'] = array_values($result);
        } else {
            $response['error'] = true;
            $response['message'] = "Data does't exists!";
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Please pass all the fields!';
    }

    print_r(json_encode($response));
    return false;
}
