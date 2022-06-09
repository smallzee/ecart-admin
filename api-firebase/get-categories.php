<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once('../includes/crud.php');
$db = new Database();
$db->connect();
include_once('../includes/variables.php');
include_once('verify-token.php');
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

/* 
get-categories.php
    accesskey:90336 
*/
if (!verify_token()) {
    return false;
}
if (isset($_POST['accesskey'])) {
    $access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
    if ($access_key_received == $access_key) {
        // get all category data from category table
        $sql_query = "SELECT * 
			FROM category 
			ORDER BY id ASC ";
        $db->sql($sql_query);
        $res = $db->getResult();
        if (!empty($res)) {
            for ($i = 0; $i < count($res); $i++) {
                $res[$i]['image'] = (!empty($res[$i]['image'])) ? DOMAIN_URL . '' . $res[$i]['image'] : '';
                $res[$i]['web_image'] = (!empty($res[$i]['web_image'])) ? DOMAIN_URL . '' . $res[$i]['web_image'] : '';
            }
            $tmp = [];
            foreach ($res as $r) {
                $r['childs'] = [];

                $db->sql("SELECT * FROM subcategory WHERE category_id = '" . $r['id'] . "' ORDER BY id DESC");
                $childs = $db->getResult();
                if (!empty($childs)) {
                    for ($i = 0; $i < count($childs); $i++) {
                        $childs[$i]['image'] = (!empty($childs[$i]['image'])) ? DOMAIN_URL . '' . $childs[$i]['image'] : '';
                        $r['childs'][$childs[$i]['slug']] = (array)$childs[$i];
                    }
                }
                $tmp[] = $r;
            }
            $res = $tmp;
            $response['error'] = "false";
            $response['data'] = $res;
        } else {
            $response['error'] = "true";
            $response['message'] = "No data found!";
        }
        print_r(json_encode($response));
    } else {
        die('accesskey is incorrect.');
    }
} else {
    die('accesskey is require.');
}
$db->disconnect();
