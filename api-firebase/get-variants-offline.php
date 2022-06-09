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
include_once('verify-token.php');
$db = new Database();
$db->connect();
$response = array();

$config = $fn->get_configurations();
$time_slot_config = $fn->time_slot_config();
if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}

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
/*  
get-variants-offline.php
    accesskey:90336
    get_variants_offline:1
    variant_ids:55,56 
*/

if (!verify_token()) {
    return false;
}
if ((isset($_POST['get_variants_offline']) && $_POST['get_variants_offline'] == 1) && (isset($_POST['variant_ids'])) && !empty(trim($_POST['variant_ids']))) {
    $variant_ids = $db->escapeString($fn->xss_clean($_POST['variant_ids']));
    $sql = "SELECT pv.*,p.tax_id FROM product_variant pv JOIN products p ON p.id=pv.product_id where pv.id IN ($variant_ids)";
    $db->sql($sql);
    $res = $db->getResult();
    $total_records = $db->numRows($res);
    $i = 0;
    $j = 0;
    $total_amount = "0";
    if ($total_records > 0) {
        foreach ($res as $row) {
            $sql = "select pv.*,p.name,p.image,p.other_images,pv.measurement,(select short_code from unit u where u.id=pv.measurement_unit_id) as unit from product_variant pv left join products p on p.id=pv.product_id where pv.id=" . $row['id'];
            $db->sql($sql);
            $res[$i]['item'] = $db->getResult();
            if ($row['tax_id'] == 0) {
                $res[$i]['tax_title'] = "";
                $res[$i]['tax_percentage'] = "0";
            } else {
                $t_id = $row['tax_id'];
                $sql_tax = "SELECT * from taxes where id= $t_id";
                $db->sql($sql_tax);
                $res_tax = $db->getResult();
                foreach ($res_tax as $tax) {
                    $res[$i]['tax_title'] = $tax['title'];
                    $res[$i]['tax_percentage'] = $tax['percentage'];
                }
            }

            for ($k = 0; $k < count($res[$i]['item']); $k++) {
                $res[$i]['item'][$k]['cart_count'] = "0";
                $res[$i]['item'][$k]['other_images'] = json_decode($res[$i]['item'][$k]['other_images']);
                $res[$i]['item'][$k]['other_images'] = empty($res[$i]['item'][$k]['other_images']) ? array() : $res[$i]['item'][$k]['other_images'];
                for ($l = 0; $l < count($res[$i]['item'][$k]['other_images']); $l++) {
                    $other_images = DOMAIN_URL . $res[$i]['item'][$k]['other_images'][$l];
                    $res[$i]['item'][$k]['other_images'][$l] = $other_images;
                }
            }

            for ($j = 0; $j < count($res[$i]['item']); $j++) {
                $res[$i]['item'][$j]['image'] = !empty($res[$i]['item'][$j]['image']) ? DOMAIN_URL . $res[$i]['item'][$j]['image'] : "";
            }
            $i++;
        }

        if (!empty($res)) {
            $response['error'] = false;
            $response['total'] = stripcslashes($total_records);
            $response['total_amount'] = $total_amount;
            $response['data'] = array_values($res);
        } else {
            $response['error'] = true;
            $response['message'] = "No item(s) found!";
        }
    }else{
        $response['error'] = true;
        $response['message'] = "No item(s) found!";
    }

    print_r(json_encode($response));
    return false;
}
