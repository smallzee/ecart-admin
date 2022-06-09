<?php
header('Access-Control-Allow-Origin: *');
require_once '../includes/functions.php';
include_once('../includes/variables.php');
include_once('verify-token.php');
include_once('../includes/custom-functions.php');
$function = new custom_functions;
$db = new Database();
$db->connect();
$response = array();


$config = $function->get_configurations();
$time_slot_config = $function->time_slot_config();
if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}

/* accesskey:90336
    fcm_id:12345678
    user_id : 441 // {optional}
*/

$accesskey = $db->escapeString($function->xss_clean($_POST['accesskey']));

if ($access_key != $accesskey) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey";
    print_r(json_encode($response));
    return false;
}
if (!verify_token()) {
    return false;
}

if (isset($_POST['fcm_id']) && !empty($_POST['fcm_id'])) {

    $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($function->xss_clean($_POST['user_id'])) : 0;
    $fcm_id = $db->escapeString($function->xss_clean($_POST['fcm_id']));
    $fn = new functions;
    $result = $fn->registerDevice($fcm_id, $user_id);
    if ($result == 0) {
        $response['error'] = false;
        $response['fcm_id'] = $fcm_id;
        $response['message'] = 'Device registered successfully';
    } elseif ($result == 2) {
        $response['error'] = true;
        $response['message'] = 'Device already registered';
    } else {
        $response['error'] = true;
        $response['message'] = 'Device not registered';
    }
} else {
    $response['error'] = true;
    $response['message'] = 'Please pass all fields';
}

echo json_encode($response);
