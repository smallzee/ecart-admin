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
get-similar-products.php
    accesskey:90336
    get_similar_products:369
    product_id:211
    category_id:28
    limit:6          // {optional}
    user_id:369         // {optional}
*/

if (!verify_token()) {
    return false;
}

if (isset($_POST['accesskey']) && isset($_POST['get_similar_products']) && !empty($_POST['get_similar_products'])) {
    $access_key_received = isset($_POST['accesskey']) && !empty($_POST['accesskey']) ? $db->escapeString($fn->xss_clean($_POST['accesskey'])) : '';

    if (empty($_POST['product_id']) || empty($_POST['category_id'])) {
        $response['error'] = true;
        $response['message'] = "Missing arguments!";
        print_r(json_encode($response));
        return false;
        exit();
    }
    $product_id = $db->escapeString($fn->xss_clean($_POST['product_id']));
    $category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
    $user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";
    $row1 = array();

    if ($access_key_received == $access_key) {
        $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : 6;
        $offset = 0;
        $order =  "RAND()";

        // $result = $fn->get_data($columns = ['id'],'id=')

        $sql = "SELECT count(id) as total FROM products where id != $product_id and category_id = $category_id and `status`=1 ORDER BY $order LIMIT $offset,$limit";
        $db->sql($sql);
        $total1 = $db->getResult();

        $sql = "SELECT p.*,(SELECT MIN(pv.price) FROM product_variant pv WHERE pv.product_id=p.id) as price FROM products p where id != $product_id and `status`=1 and category_id = $category_id ORDER BY $order LIMIT $offset,$limit";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            foreach ($res as $row) {
                $tempRow['id'] = $row['id'];
                $tempRow['row_order'] = $row['row_order'];
                $tempRow['name'] = $row['name'];
                $tempRow['slug'] = $row['slug'];
                $tempRow['category_id'] = $row['category_id'];
                $tempRow['subcategory_id'] = $row['subcategory_id'];
                $tempRow['indicator'] = $row['indicator'];
                $tempRow['manufacturer'] = $row['manufacturer'];
                $tempRow['made_in'] = $row['made_in'];
                $tempRow['return_status'] = $row['return_status'];
                $tempRow['cancelable_status'] = $row['cancelable_status'];
                $tempRow['till_status'] = $row['till_status'];
                $tempRow['date_added'] = $row['date_added'];
                $tempRow['price'] = $row['price'];
                $tempRow['image'] = (!empty($row['image'])) ? DOMAIN_URL . '' . $row['image'] : '';
                if (!empty($row['other_images']) && $row['other_images'] != "") {
                    $row['other_images'] = json_decode($row['other_images'], 1);
                    for ($j = 0; $j < count($row['other_images']); $j++) {
                        $tempRow['other_images'][$j] = DOMAIN_URL . $row['other_images'][$j];
                    }
                } else {
                    $tempRow['other_images'] = array();
                }
                if ($row['tax_id'] == 0) {
                    $tempRow['tax_title'] = "";
                    $tempRow['tax_percentage'] = "0";
                } else {
                    $t_id = $row['tax_id'];
                    $sql_tax = "SELECT * from taxes where id= $t_id";
                    $db->sql($sql_tax);
                    $res_tax = $db->getResult();
                    foreach ($res_tax as $tax) {
                        $tempRow['tax_title'] = $tax['title'];
                        $tempRow['tax_percentage'] = $tax['percentage'];
                    }
                }

                if (!empty($user_id)) {
                    $sql = "SELECT id from favorites where product_id = " . $row['id'] . " AND user_id = " . $user_id;
                    $db->sql($sql);
                    $result = $db->getResult();
                    if (!empty($result)) {
                        $tempRow['is_favorite'] = true;
                    } else {
                        $tempRow['is_favorite'] = false;
                    }
                } else {
                    $tempRow['is_favorite'] = false;
                }
                $tempRow['description'] = $row['description'];
                $tempRow['status'] = $row['status'];

                $sql1 = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=" . $row['id'] . " ORDER BY serve_for ASC";
                $db->sql($sql1);
                $variants = $db->getResult();
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
                $tempRow['variants'] = $variants;
                $rows[] = $tempRow;
            }
            $response['error'] = false;
            $response['total'] = $total1[0]['total'];
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
