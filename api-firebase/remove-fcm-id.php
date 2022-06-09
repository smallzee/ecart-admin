<?php
header('Access-Control-Allow-Origin: *');
session_start();
include '../includes/crud.php';
include '../includes/custom-functions.php';
$fn = new custom_functions;

include '../includes/variables.php';
include_once('verify-token.php');
$db = new Database();
$db->connect();
$fn = new custom_functions();
$settings = $fn->get_settings('system_timezone', true);
$app_name = $settings['app_name'];
include 'send-email.php';
include 'send-sms.php';

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
$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));

if ($access_key != $accesskey) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey";
    print_r(json_encode($response));
    return false;
}
if (isset($_POST['remove_fcm_id']) && $_POST['remove_fcm_id'] == 1 && isset($_POST['user_id']) && $_POST['user_id'] != '') {
    if (!verify_token()) {
        return false;
    }
    $user_id  = $db->escapeString($fn->xss_clean($_POST['user_id']));
    $sql = "select `id` from `users` where `id`='" . $user_id . "'";
    $db->sql($sql);
    $result = $db->getResult();
    if ($db->numRows($result) > 0) {
        // Update the Device ID
        $sql = 'UPDATE `users` SET `fcm_id`="" WHERE `id`="' . $user_id . '"';
        if ($db->sql($sql)) {
            $response["error"]   = false;
            $response["message"] = "FCM ID removed successfully";
        }
    } else {
        $response["error"]   = true;
        $response["message"] = "User does't exists.";
    }
    echo json_encode($response);
}
