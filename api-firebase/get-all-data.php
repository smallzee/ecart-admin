<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Access-Control-Allow-Origin: *');

include_once('send-email.php');
include_once('send-sms.php');
include_once('../includes/crud.php');
include_once('../includes/custom-functions.php');
include_once('../includes/variables.php');
include_once('verify-token.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES utf8");
$fn = new custom_functions();

$config = $fn->get_configurations();
$time_slot_config = $fn->time_slot_config();
if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}

if (!verify_token()) {
    return false;
}

if (!isset($_POST['accesskey'])  || trim($_POST['accesskey']) != $access_key) {
    $response['error'] = true;
    $response['message'] = "No Accsess key found!";
    print_r(json_encode($response));
    return false;
    exit();
}

/* 
get-all-data.php
	accesskey:90336
	user_id:413 {optional}
	
*/

$user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";

//categories
$sql = "SELECT * FROM category ORDER BY row_order ASC ";
$db->sql($sql);
$res_categories = $db->getResult();

for ($i = 0; $i < count($res_categories); $i++) {
    $res_categories[$i]['image'] = (!empty($res_categories[$i]['image'])) ? DOMAIN_URL . '' . $res_categories[$i]['image'] : '';
}
// slider images
$sql = 'SELECT * from slider order by id DESC';
$db->sql($sql);
$res_slider_image = $db->getResult();
$temp = $slider_images = array();
if (!empty($res_slider_image)) {
    $response['error'] = false;
    foreach ($res_slider_image as $row) {
        $name = "";
        if ($row['type'] == 'category') {
            $sql = 'select `name` from category where id = ' . $row['type_id'] . ' order by id desc';
            $db->sql($sql);
            $result1 = $db->getResult();
            $name = (!empty($result1[0]['name'])) ? $result1[0]['name'] : "";
        }
        if ($row['type'] == 'product') {
            $sql = 'select `name` from products where id = ' . $row['type_id'] . ' order by id desc';
            $db->sql($sql);
            $result1 = $db->getResult();
            $name = (!empty($result1[0]['name'])) ? $result1[0]['name'] : "";
        }

        $temp['type'] = $row['type'];
        $temp['type_id'] = $row['type_id'];
        $temp['name'] = $name;
        $temp['image'] = DOMAIN_URL . $row['image'];
        $slider_images[] = $temp;
    }
}

// featured sections
$sql = 'select * from `sections` order by id desc';
$db->sql($sql);
$result = $db->getResult();
$response = $product_ids = $section = $variations = $featured_sections = array();
foreach ($result as $row) {
    $product_ids = explode(',', $row['product_ids']);

    $section['id'] = $row['id'];
    $section['title'] = $row['title'];
    $section['short_description'] = $row['short_description'];
    $section['style'] = $row['style'];
    $section['product_ids'] = array_map('trim', $product_ids);
    $product_ids = $section['product_ids'];

    $product_ids = implode(',', $product_ids);

    $sql = 'SELECT * FROM `products` WHERE `status` = 1 AND id IN (' . $product_ids . ')';
    $db->sql($sql);
    $result1 = $db->getResult();
    $product = array();
    $i = 0;
    foreach ($result1 as $row) {
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
            if ($variants[$k]['stock'] <= 0) {
                $variants[$k]['serve_for'] = 'Sold Out';
            } else {
                $variants[$k]['serve_for'] = 'Available';
            }
            if (!empty($user_id)) {
                $sql = "SELECT qty as cart_count FROM cart where product_variant_id = " . $variants[$k]['id'] . " AND user_id = " . $user_id;
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
            if (!empty($user_id)) {
                $sql = "SELECT id from favorites where product_id = " . $row['id'] . " AND user_id = " . $user_id;
                $db->sql($sql);
                $favorite = $db->getResult();
                if (!empty($favorite)) {
                    $row['is_favorite'] = true;
                } else {
                    $row['is_favorite'] = false;
                }
            } else {
                $row['is_favorite'] = false;
            }
        }
        $row['image'] = DOMAIN_URL . $row['image'];
        $product[$i] = $row;
        $product[$i]['variants'] = $variants;
        $i++;
    }
    $section['products'] = $product;
    $featured_sections[] = $section;
    unset($section['products']);
}
// offer images
$sql = 'SELECT * from offers order by id desc';
$db->sql($sql);
$res_offer_images = $db->getResult();
$response = $temp = $offer_images = array();
foreach ($res_offer_images as $row) {
    $temp['image'] = DOMAIN_URL . $row['image'];
    $offer_images[] = $temp;
}
$data = $fn->get_settings('categories_settings', true);

// if (!empty($data1)) {
//     $data['style'] =  $data1['cat_style'];
//     $data['visible_count'] = $data1['max_visible_categories'];
//     $data['column_count'] = ($data1['cat_style'] == "style_2") ? 0 : $data1['max_col_in_single_row'];

// } else {
//     $data['style'] =  "";
//     $data['visible_count'] =0;
//     $data['column_count'] = 0;   
// }
// $res_categories[count($res_categories)] = $data;


$response['error'] = false;
$response['message'] = "Data fetched successfully";
if (!empty($data)) {
    $response['style'] =  $data['cat_style'];
    $response['visible_count'] = $data['max_visible_categories'];
    $response['column_count'] = ($data['cat_style'] == "style_2") ? 0 : $data['max_col_in_single_row'];
} else {
    $response['style'] =  "";
    $response['visible_count'] = 0;
    $response['column_count'] = 0;
}
$response['categories'] = (!empty($res_categories)) ? $res_categories : [];
$response['slider_images'] = (!empty($slider_images)) ? $slider_images : [];
$response['sections'] = (!empty($featured_sections)) ? $featured_sections : [];
$response['offer_images'] = (!empty($offer_images)) ? $offer_images : [];

print_r(json_encode($response));
