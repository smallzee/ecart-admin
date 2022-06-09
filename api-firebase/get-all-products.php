<?php
header('Access-Control-Allow-Origin: *');
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

// if (!verify_token()) {
//     return false;
// }

if (!isset($_POST['accesskey'])  || trim($_POST['accesskey']) != $access_key) {
    $response['error'] = true;
    $response['message'] = "No Accsess key found!";
    print_r(json_encode($response));
    return false;
}


/* 
get-all-products.php
    accesskey:90336
    get_all_products:1
    user_id:369 {optional}
*/
if (isset($_POST['get_all_products']) && $_POST['get_all_products'] == 1) {
    /* 
    1.get_all_products
        accesskey:90336
        get_all_products:1
        product_id:219      // {optional}
        user_id:1782        // {optional}
        category_id:29      // {optional}
        subcategory_id:132  // {optional}
        limit:5             // {optional}
        offset:1            // {optional}
        sort:id             // {optional}
        order:asc/desc      // {optional}
    */

    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : 10;
    $offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) ? $db->escapeString($fn->xss_clean($_POST['offset'])) : 0;

    $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $db->escapeString($fn->xss_clean($_POST['sort'])) : "row_order + 0 ";
    $order = (isset($_POST['order']) && !empty($_POST['order'])) ? $db->escapeString($fn->xss_clean($_POST['order'])) : "DESC";

    $product_id = (isset($_POST['product_id']) && !empty($_POST['product_id'])) ? $db->escapeString($fn->xss_clean($_POST['product_id'])) : "";
    $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";

    $category_id = (isset($_POST['category_id']) && !empty($_POST['category_id'])) ? $db->escapeString($fn->xss_clean($_POST['category_id'])) : "";
    $subcategory_id = (isset($_POST['subcategory_id']) && !empty($_POST['subcategory_id'])) ? $db->escapeString($fn->xss_clean($_POST['subcategory_id'])) : "";


    $where = "";
    if (isset($_POST['product_id']) && !empty($_POST['product_id']) && is_numeric($_POST['product_id'])) {
        $where .=  !empty($where) ? " AND `id` = " . $product_id :  " WHERE `id`=" . $product_id;
    }

    if (isset($_POST['category_id']) && !empty($_POST['category_id']) && is_numeric($_POST['category_id'])) {
        $where .=  !empty($where) ? " AND `category_id`=" . $category_id : " WHERE `category_id`=" . $category_id;
    }
    if (isset($_POST['subcategory_id']) && !empty($_POST['subcategory_id']) && is_numeric($_POST['subcategory_id'])) {
        $where .=  !empty($where) ? " AND `subcategory_id`=" . $subcategory_id : " WHERE `subcategory_id`=" . $subcategory_id;
    }


    $sql = "SELECT count(id) as total FROM products $where ";
    $db->sql($sql);
    $total = $db->getResult();

    $sql = "SELECT * FROM products $where ORDER BY $sort $order LIMIT $offset,$limit ";
    $db->sql($sql);
    $res = $db->getResult();
    $product = array();
    $i = 0;
    $sql = "SELECT id FROM cart limit 1";
    $db->sql($sql);
    $res_cart = $db->getResult();

    foreach ($res as $row) {
        $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=" . $row['id'] . " ";
        $db->sql($sql);
        $variants = $db->getResult();

        $row['is_included'] = (isset($row['is_included']) == null)  ? "" : $row['is_included'];
        $row['is_excluded'] = (isset($row['is_excluded']) == null)  ? "" : $row['is_excluded'];
        $row['is_approved'] = (isset($row['is_approved']) == null)  ? "" : $row['is_approved'];
        $row['seller_id'] = (isset($row['seller_id']) == null)  ? "" : $row['seller_id'];

        $row['other_images'] = json_decode($row['other_images'], 1);
        $row['other_images'] = (empty($row['other_images'])) ? array() : $row['other_images'];
        for ($j = 0; $j < count($row['other_images']); $j++) {
            $row['other_images'][$j] = DOMAIN_URL . $row['other_images'][$j];
        }

        $row['image'] = DOMAIN_URL . $row['image'];
        if ($row['tax_id'] == 0) {
            $row['tax_title'] = "";
            $row['tax_percentage'] = "0";
        } else {
            $t_id = $row['tax_id'];
            $sql_tax = "SELECT * from taxes where id= $t_id";
            $db->sql($sql_tax);
            $res_tax1 = $db->getResult();
            foreach ($res_tax1 as $tax1) {
                $row['tax_title'] = (!empty($tax1['title'])) ? $tax1['title'] : "";
                $row['tax_percentage'] =  (!empty($tax1['percentage'])) ? $tax1['percentage'] : "0";
            }
        }

        $product[$i] = $row;

        for ($k = 0; $k < count($variants); $k++) {
            if ($variants[$k]['stock'] <= 0 && $variants[$k]['serve_for'] = 'Sold Out') {
                $variants[$k]['serve_for'] = 'Sold Out';
            } else {
                $variants[$k]['serve_for'] = 'Available';
            }
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

    if (!empty($product)) {
        $response['error'] = false;
        $response['message'] = "Products retrieved successfully";
        $response['total'] = $total[0]['total'];
        $response['data'] = $product;
    } else {
        $response['error'] = true;
        $response['message'] = "No products available";
        $response['total'] = $total[0]['total'];
        $response['data'] = array();
    }
    print_r(json_encode($response));
    return false;
}

if (isset($_POST['get_all_products_name']) && $_POST['get_all_products_name'] == 1) {
    $sql = "SELECT name FROM `products`";
    $db->sql($sql);
    $res = $db->getResult();
    $rows = $tempRow = $blog_array = $blog_array1 = array();
    foreach ($res as $row) {
        $tempRow['name'] = $row['name'];
        $rows[] = $tempRow;
    }
    $names = array_column($rows, 'name');

    $pr_names = implode(",",$names);
    // print_r($pr_names);
    $response['error'] = false;
    $response['data'] = $pr_names;

    print_r(json_encode($response));
}
