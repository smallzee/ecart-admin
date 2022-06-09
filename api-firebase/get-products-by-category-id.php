<?php
header('Access-Control-Allow-Origin: *');
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
get-products-by-category-id.php
    accesskey:90336
  	category_id:32
  	user_id:369 {optional}
  	limit:10 // {optional}
  	offset:0 // {optional}
  	sort:new / old / high / low // {optional}
*/
if (!verify_token()) {
    return false;
}

if (isset($_POST['accesskey']) && isset($_POST['category_id'])) {
    $access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
    $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $db->escapeString($fn->xss_clean($_POST['sort'])) : 'id';
    $subcategory_id = (isset($_POST['category_id']) && is_numeric($_POST['category_id'])) ? $db->escapeString($fn->xss_clean($_POST['category_id'])) : "";
    $user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";
    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : '10';
    $offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) ? $db->escapeString($fn->xss_clean($_POST['offset'])) : '0';

    if ($access_key_received == $access_key) {

        if ($sort == 'new') {
            $sort = 'ORDER BY date_added DESC';
            $price = 'MIN(price)';
            $price_sort = 'ORDER BY pv.price ASC';
        } elseif ($sort == 'old') {
            $sort = 'ORDER BY date_added ASC';
            $price = 'MIN(price)';
            $price_sort = 'ORDER BY pv.price ASC';
        } elseif ($sort == 'high') {
            $sort = 'ORDER BY price DESC';
            $price = 'MAX(price)';
            $price_sort = 'ORDER BY pv.price DESC';
        } elseif ($sort == 'low') {
            $sort = 'ORDER BY price ASC';
            $price = 'MIN(price)';
            $price_sort = 'ORDER BY pv.price ASC';
        } else {
            $sort = 'ORDER BY p.row_order ASC';
            $price = 'MIN(price)';
            $price_sort = 'ORDER BY pv.price ASC';
        }

        $sql = "SELECT count(id) as total FROM products WHERE `status`=1 and category_id= $subcategory_id and subcategory_id=0";
        $db->sql($sql);
        $res = $db->getResult();
        foreach ($res as $row) {
            $total = $row['total'];
        }
        $sql = "SELECT p.*,(SELECT " . $price . " FROM product_variant pv WHERE pv.product_id=p.id) as price FROM products p WHERE `status`=1 and subcategory_id=0 and category_id='" . $subcategory_id . "' $sort LIMIT $offset, $limit";
        $db->sql($sql);
        $res = $db->getResult();
        $product = array();

        $i = 0;
        $sql = "SELECT id FROM cart limit 1";
        $db->sql($sql);
        $res_cart = $db->getResult();
        foreach ($res as $row) {


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
                    $row['tax_percentage'] = (!empty($tax['percentage'])) ? $tax['percentage'] : "0";
                }
            }
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

            $row['image'] = DOMAIN_URL . $row['image'];
            $product[$i] = $row;
            $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=" . $row['id'] . " " . $price_sort . " ";
            $db->sql($sql);
            $variants = $db->getResult();
            for ($k = 0; $k < count($variants); $k++) {
                if ($variants[$k]['stock'] <= 0 || $variants[$k]['serve_for'] == 'Sold Out') {
                    $variants[$k]['isAvailable'] = false;
                } else {
                    $variants[$k]['isAvailable'] = true;
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
            $output = json_encode(array(
                'error' => false, 'total' => $total,
                'data' => $product
            ));
        } else {
            $output = json_encode(array(
                'error' => true,
                'data' => 'No products available'
            ));
        }
    } else {
        $output = json_encode(array(
            'error' => true,
            'message' => 'accesskey is incorrect.'
        ));
    }
} else {
    $output = json_encode(array(
        'error' => true,
        'message' => 'accesskey and subcategory id are required.'
    ));
}
//Output the output.
echo $output;
$db->disconnect();
//to check if the string is json or not
function isJSON($string)
{
    return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
}
