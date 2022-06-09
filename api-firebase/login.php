<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
include_once('../includes/custom-functions.php');
$fn = new custom_functions;

session_start();
include_once '../includes/crud.php';
include_once('../includes/variables.php');
include_once('verify-token.php');
$db = new Database();
$db->connect();
// date_default_timezone_set('Asia/Kolkata');
$config = $fn->get_configurations();
$time_slot_config = $fn->time_slot_config();
if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}

/* 
login.php
    accesskey:90336
    mobile:9974692496
    password:36652
    status:1   // 1 - Active & 0 Deactive
*/

$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));

if ($access_key != $accesskey) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey";
    print_r(json_encode($response));
    return false;
}
if (!verify_token()) {
    return false;
}

if (isset($_POST['mobile']) && $_POST['mobile'] != '' && isset($_POST['password']) && $_POST['password'] != '') {
    $mobile    = $db->escapeString($fn->xss_clean($_POST['mobile']));
    $password    = $db->escapeString($fn->xss_clean($_POST['password']));
    $response = array();
    if (!empty($mobile) && !empty($password)) {
        $password  = md5($password);
        $sql_query = "SELECT *,(SELECT name FROM area a WHERE a.id=u.area) as area_name,(SELECT name FROM city c WHERE c.id=u.city) as city_name FROM `users` u WHERE `mobile` = '" . $mobile . "' AND `password` ='" . $password . "'";
        $db->sql($sql_query);
        $result = $db->getResult();
        if ($db->numRows($result) > 0) {

            $fcm_id = (isset($_POST['fcm_id']) && !empty($_POST['fcm_id'])) ? $db->escapeString($fn->xss_clean($_POST['fcm_id'])) : "";
            if (!empty($fcm_id)) {
                $sql = "update users set `fcm_id` ='" . $fcm_id . "' where id = " . $result[0]['id'];
                $db->sql($sql);
            }

            foreach ($result as $row) {
                $response['error']     = false;
                $response['user_id'] = $row['id'];
                $response['name'] = $row['name'];
                $response['email'] = $row['email'];
                $response['profile'] = DOMAIN_URL . 'upload/profile/' . "" . $row['profile'];
                $response['mobile'] = $row['mobile'];
                $response['country_code'] = $row['country_code'];
                $response['dob'] = $row['dob'];
                $response['balance'] = $row['balance'];
                $response['city_id'] = !empty($row['city']) ? $row['city'] : '';
                $response['city_name'] = !empty($row['city_name']) ? $row['city_name'] : '';
                $response['area_id'] = !empty($row['area']) ? $row['area'] : '';
                $response['area_name'] = !empty($row['area_name']) ? $row['area_name'] : '';
                $response['street'] = $row['street'];
                $response['pincode'] = $row['pincode'];
                $response['referral_code'] = $row['referral_code'];
                $response['friends_code'] = $row['friends_code'];
                $response['latitude'] = (!empty($row['latitude'])) ? $row['latitude'] : '0';
                $response['longitude'] = (!empty($row['longitude'])) ? $row['longitude'] : '0';
                $response['apikey'] = $row['apikey'];
                $response['status'] = $row['status'];
                $response['created_at'] = $row['created_at'];
            }
            $response['message'] = "Successfully logged in.";
        } else {
            $response['error']     = true;
            $response['message']   = "Invalid mobile or password!";
        }
    }
    print_r(json_encode($response));
} else {
    $response['message'] = "Mobile and password should be filled";
    print_r(json_encode($response));
}
$db->disconnect();
