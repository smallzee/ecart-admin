<?php
header('Access-Control-Allow-Origin: *');
include_once('../includes/variables.php');
include_once('../includes/crud.php');
include_once('verify-token.php');
$db = new Database();
$db->connect();
// date_default_timezone_set('Asia/Kolkata');
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
    get-products-offline.php
        accesskey:90336
        get_products_offline:1
        product_ids:214,215 
    */

if (!verify_token()) {
    return false;
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
if ((isset($_POST['get_products_offline']) && $_POST['get_products_offline'] == 1) && (isset($_POST['product_ids'])) && !empty(trim($_POST['product_ids']))) {
    $product_ids = $db->escapeString($fn->xss_clean($_POST['product_ids']));
    $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $db->escapeString($fn->xss_clean($_POST['sort'])) : "row_order + 0 ";
    $order = (isset($_POST['order']) && !empty($_POST['order'])) ? $db->escapeString($fn->xss_clean($_POST['order'])) : "ASC";
    $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($_POST['offset'])) : 0;
    $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($_POST['limit'])) : 10;
    $sql = "SELECT * FROM products where id IN ($product_ids) ORDER BY $sort $order";
    $db->sql($sql);
    $res = $db->getResult();
    $product = array();
    $i = 0;
    foreach ($res as $row) {
        $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=" . $row['id'] . " ORDER BY serve_for ASC";
        $db->sql($sql);
        $variants = $db->getResult();
        $row['other_images'] = json_decode($row['other_images'], 1);
        $row['other_images'] = (empty($row['other_images'])) ? array() : $row['other_images'];
        for ($j = 0; $j < count($row['other_images']); $j++) {
            $row['other_images'][$j] = DOMAIN_URL . $row['other_images'][$j];
        }
        if ($row['tax_id'] == 0) {
            $row['tax_title'] = "";
            $row['tax_percentage'] = "0";
        } else {
            $t_id = $row['tax_id'];
            $sql_tax = "SELECT * from taxes where id= $t_id";
            $db->sql($sql_tax);
            $res_tax = $db->getResult();
            foreach ($res_tax as $tax) {
                $row['tax_title'] = $tax['title'];
                $row['tax_percentage'] = $tax['percentage'];
            }
        }
        for ($k = 0; $k < count($variants); $k++) {
            $variants[$k]['cart_count'] = "0";
        }
        $row['is_favorite'] = false;

        $row['image'] = DOMAIN_URL . $row['image'];
        $product[$i] = $row;
        $product[$i]['variants'] = $variants;
        $i++;
    }
    // create json output
    if (!empty($product)) {
        $output = json_encode(
            array(
                'error' => false,
                'limit' => $limit,
                'offset' => $offset,
                'sort' => $sort,
                'order' => $order,
                'message' => "Products retrieved successfully",
                'data' => $product
            )
        );
    } else {
        $output = json_encode(
            array(
                'error' => true,
                'limit' => $limit,
                'offset' => $offset,
                'sort' => $sort,
                'order' => $order,
                'message' => 'No products available',
                'data' => array()
            )
        );
    }
} else {
    die('Pass all the fields.');
}
echo $output;

$db->disconnect();
