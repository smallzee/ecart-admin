<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
include_once('../includes/variables.php');
include_once('../includes/crud.php');
include_once('verify-token.php');
$db = new Database();
$db->connect();
include_once('../includes/custom-functions.php');
$fn = new custom_functions;
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
get-faqs.php
    accesskey:90336
    get_faqs:1
    offset:0        // {optional}
    limit:10        // {optional}
    sort:id           // {optional}
    order:DESC / ASC            // {optional}
*/

if (!verify_token()) {
	return false;
}

if (isset($_POST['accesskey']) && isset($_POST['get_faqs']) && !empty($_POST['get_faqs'])) {
    $access_key_received = isset($_POST['accesskey']) && !empty($_POST['accesskey']) ? $db->escapeString($fn->xss_clean($_POST['accesskey'])) : '';
    if ($access_key_received == $access_key) {
        $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($_POST['offset'])) : 0;
        $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($_POST['limit'])) : 10;

        $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($_POST['sort'])) : 'id';
        $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($_POST['order'])) : 'DESC';

        $sql = "SELECT count(id) as total FROM faq where `status` = 1";
        $db->sql($sql);
        $total = $db->getResult();

        $sql = "SELECT * FROM faq where `status`=1 ORDER BY `$sort` $order LIMIT $offset,$limit";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            foreach ($res as $row) {
                $tempRow['id'] = $row['id'];
                $tempRow['question'] = $row['question'];
                $tempRow['answer'] = (!empty($row['answer'])) ? $row['answer'] : '';
                $tempRow['status'] = $row['status'];
                $rows[] = $tempRow;
            }
            $response['error'] = false;
            $response['total'] = $total[0]['total'];
            $response['data'] = $rows;
        } else {
            $response['error'] = true;
            $response['message'] = 'Data not Found!';
        }
        print_r(json_encode($response));
    } else {
        die('accesskey is incorrect.');
    }
} else {
    die('accesskey is required.');
}
