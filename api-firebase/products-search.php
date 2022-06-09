<?php
header('Access-Control-Allow-Origin: *');
include_once('../includes/crud.php');
include_once('../includes/variables.php');
include_once('verify-token.php');
$db = new Database();
$db->connect();
date_default_timezone_set('Asia/Kolkata');
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
products-search.php
    accesskey:90336
	type:products-search
	search:Himalaya Baby Powder
    id:227
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
// data of 'PRODUCTS' table goes here
if (isset($_POST['type']) && $_POST['type'] == 'products-search') {
    $offset = 0;
    $limit = 10;
    $sort = 'id';
    $order = 'DESC';
    $where = '';
    if (isset($_POST['offset']))
        $offset = $db->escapeString($fn->xss_clean($_POST['offset']));
    if (isset($_POST['limit']))
        $limit = $db->escapeString($fn->xss_clean($_POST['limit']));

    if (isset($_POST['sort']))
        $sort = $db->escapeString($fn->xss_clean($_POST['sort']));
    if (isset($_POST['order']))
        $order = $db->escapeString($fn->xss_clean($_POST['order']));

    if (isset($_POST['search']) && $_POST['search'] != '') {
        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        $where = "Where status=1 and (`id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `image` like '%" . $search . "%' OR `subcategory_id` like '%" . $search . "%' OR `slug` like '%" . $search . "%' OR `description` like '%" . $search . "%')";
    }
    $user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";
    $sql = "SELECT COUNT(id) as total FROM `products` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row) {
        $total = $row['total'];
    }
    $sql = "SELECT * FROM `products` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    $product = array();
    $i = 0;
    foreach ($res as $row) {
        $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=" . $row['id'] . " ORDER BY serve_for ASC";
        $db->sql($sql);
        $variants = $db->getResult();
        if (!empty($user_id)) {
            $sql = "SELECT id from favorites where product_id = " . $row['id'] . " AND user_id = " . $user_id;
            $db->sql($sql);
            $result = $db->getResult();
            if (!empty($result)) {
                $row['is_favorite'] = true;
            } else {
                $row['is_favorite'] = false;
            }
        } else {
            $row['is_favorite'] = false;
        }
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
        $row['image'] = DOMAIN_URL . $row['image'];
        $product[$i] = $row;
        for ($k = 0; $k < count($variants); $k++) {
            if (!empty($user_id)) {
                $sql = "SELECT qty as cart_count FROM cart where product_variant_id= " . $variants[$k]['id'] . " AND user_id=" . $user_id;
                $db->sql($sql);
                $res = $db->getResult();
                if (!empty($res)) {
                    foreach ($res as $row1) {
                        $variants[$k]['cart_count'] = $row1['cart_count'];
                    }
                } else {
                    $variants[$k]['cart_count'] = "0";
                }
            } else {
                $variants[$k]['cart_count'] = "0";
            }
        }

        $product[$i]['variants'] = $variants;
        $i++;
    }
    if (empty($product)) {
        $bulkData['error'] = true;
        $bulkData['message'] = 'No Products';
        print_r(json_encode($bulkData));
    } else {
        $bulkData['error'] = false;
        $bulkData['data'] = array_values($product);
        print_r(json_encode($bulkData));
    }
}
function isJSON($string)
{
    return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
}
